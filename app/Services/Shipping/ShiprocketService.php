<?php

namespace App\Services\Shipping;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShiprocketService implements CourierServiceInterface
{
    protected string $baseUrl = 'https://apiv2.shiprocket.in/v1/external';

    protected string $email;

    protected string $password;

    protected string $pickupLocation;

    protected string $pickupPincode;

    protected ?string $webhookToken;

    public function __construct()
    {
        $this->email = (string) config('services.shiprocket.email', '');
        $this->password = (string) config('services.shiprocket.password', '');
        $this->pickupLocation = (string) config('services.shiprocket.pickup_location', 'Primary');
        $this->pickupPincode = (string) config('services.shiprocket.pickup_pincode', '641001');
        $this->webhookToken = config('services.shiprocket.webhook_token');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->email) && ! empty($this->password);
    }

    /**
     * Get or refresh Shiprocket Bearer authentication token.
     */
    public function getToken(): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        return Cache::remember('shiprocket_auth_token', 86400 * 7, function () {
            try {
                $response = Http::timeout(5)->post("{$this->baseUrl}/auth/login", [
                    'email' => $this->email,
                    'password' => $this->password,
                ]);

                if ($response->successful()) {
                    return $response->json('token');
                }

                Log::error('Shiprocket auth failed', [
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
            } catch (\Throwable $e) {
                Log::error('Shiprocket auth connection exception: '.$e->getMessage());
            }

            return null;
        });
    }

    /**
     * Helper to make authenticated HTTP requests to Shiprocket API.
     */
    protected function api(string $method, string $endpoint, array $data = [], array $queryParams = [])
    {
        $token = $this->getToken();
        if (! $token) {
            return [
                'success' => false,
                'message' => 'Shiprocket credentials not configured or authentication failed.',
                'status' => 401,
                'data' => [],
            ];
        }

        $url = "{$this->baseUrl}/{$endpoint}";
        $client = Http::withToken($token)->timeout(8);

        try {
            $response = match (strtoupper($method)) {
                'GET' => $client->get($url, $queryParams),
                'POST' => $client->post($url, $data),
                default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
            };

            return [
                'success' => $response->successful(),
                'status' => $response->status(),
                'data' => $response->json() ?? [],
                'message' => $response->successful() ? 'OK' : ($response->json('message') ?? 'Shiprocket API error'),
            ];
        } catch (\Throwable $e) {
            Log::error("Shiprocket API call failed [{$method} {$endpoint}]: ".$e->getMessage());

            return [
                'success' => false,
                'status' => 500,
                'message' => 'Courier service temporarily unavailable: '.$e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Create an ad-hoc shipment on Shiprocket.
     */
    public function createShipment(Order $order): array
    {
        if ($order->shipment_id) {
            return [
                'success' => true,
                'shipment_id' => $order->shipment_id,
                'order_id' => $order->order_code,
                'message' => 'Shipment already created for this order.',
                'data' => ['shipment_id' => $order->shipment_id],
            ];
        }

        $order->loadMissing('items');

        $orderItems = [];
        $totalWeightKg = 0.0;

        foreach ($order->items as $item) {
            $orderItems[] = [
                'name' => $item->product_name ?: 'Product #'.$item->product_id,
                'sku' => $item->sku ?: 'SKU-'.$item->product_id,
                'units' => (int) $item->quantity,
                'selling_price' => (float) $item->unit_price,
                'discount' => 0,
                'tax' => 0,
                'hsn' => 0,
            ];
            $totalWeightKg += (0.25 * (int) $item->quantity);
        }

        $totalWeightKg = max(0.5, round($totalWeightKg, 2));

        $payload = [
            'order_id' => $order->order_code,
            'order_date' => $order->created_at ? $order->created_at->format('Y-m-d H:i') : now()->format('Y-m-d H:i'),
            'pickup_location' => $this->pickupLocation,
            'billing_customer_name' => $order->addr_full_name ?: ($order->contact_name ?: 'Customer'),
            'billing_last_name' => '',
            'billing_address' => $order->addr_address_line1 ?: 'Address Line 1',
            'billing_address_2' => $order->addr_address_line2 ?: '',
            'billing_city' => $order->addr_city ?: 'City',
            'billing_pincode' => $order->addr_pincode ?: '600001',
            'billing_state' => $order->addr_state ?: 'Tamil Nadu',
            'billing_country' => 'India',
            'billing_email' => $order->contact_email ?: 'care@noolandcrop.com',
            'billing_phone' => $order->addr_mobile_primary ?: ($order->contact_mobile ?: '9999999999'),
            'shipping_is_billing' => true,
            'order_items' => $orderItems,
            'payment_method' => ($order->payment_method === 'cod') ? 'COD' : 'Prepaid',
            'sub_total' => (float) $order->total,
            'length' => 15,
            'breadth' => 10,
            'height' => 5,
            'weight' => $totalWeightKg,
        ];

        $res = $this->api('POST', 'orders/create/adhoc', $payload);

        if (! $res['success']) {
            return [
                'success' => false,
                'shipment_id' => null,
                'order_id' => null,
                'message' => $res['message'] ?? 'Failed to create shipment on Shiprocket.',
                'data' => $res['data'],
            ];
        }

        $data = $res['data'];
        $shipmentId = (string) ($data['shipment_id'] ?? ($data['order_id'] ?? ''));

        $order->shipment_id = $shipmentId;
        $order->pushHistory('shipment_created', "Shiprocket shipment #{$shipmentId} created successfully.", auth()->id(), auth()->user()?->name);
        $order->save();

        return [
            'success' => true,
            'shipment_id' => $shipmentId,
            'order_id' => (string) ($data['order_id'] ?? $order->order_code),
            'message' => 'Shipment created successfully on Shiprocket.',
            'data' => $data,
        ];
    }

    /**
     * Generate or assign an AWB to the shipment.
     */
    public function generateAwb(Order $order): array
    {
        if (! $order->shipment_id) {
            $createRes = $this->createShipment($order);
            if (! $createRes['success']) {
                return [
                    'success' => false,
                    'awb_code' => null,
                    'courier_name' => null,
                    'courier_company_id' => null,
                    'message' => 'Could not generate AWB: '.$createRes['message'],
                ];
            }
        }

        $payload = [
            'shipment_id' => (int) $order->shipment_id,
        ];

        $res = $this->api('POST', 'courier/assign/awb', $payload);

        if (! $res['success']) {
            return [
                'success' => false,
                'awb_code' => null,
                'courier_name' => null,
                'courier_company_id' => null,
                'message' => $res['message'] ?? 'Failed to assign AWB via Shiprocket.',
            ];
        }

        $responseData = $res['data']['response']['data'] ?? $res['data'];
        $awbCode = $responseData['awb_code'] ?? null;
        $courierName = $responseData['courier_name'] ?? null;
        $courierCompanyId = $responseData['courier_company_id'] ?? null;

        if ($awbCode) {
            $order->awb_code = $awbCode;
            $order->tracking_number = $awbCode;
            $order->courier_name = $courierName;
            $order->courier_company_id = $courierCompanyId;
            $order->dispatched_via = $courierName ?: 'Shiprocket';

            if (in_array($order->status, ['placed', 'confirmed', 'processing', 'packed'], true)) {
                $order->status = 'dispatched';
                $order->dispatched_at = now();
            }

            $order->pushHistory('awb_generated', "Assigned AWB {$awbCode} via {$courierName}", auth()->id(), auth()->user()?->name);
            $order->save();
        }

        return [
            'success' => ! empty($awbCode),
            'awb_code' => $awbCode,
            'courier_name' => $courierName,
            'courier_company_id' => $courierCompanyId,
            'message' => ! empty($awbCode) ? "AWB {$awbCode} assigned successfully." : 'AWB assignment in progress.',
        ];
    }

    /**
     * Download or retrieve the printable 4x6 / A4 shipping label URL.
     */
    public function getShippingLabel(Order $order): array
    {
        if (! $order->shipment_id) {
            return [
                'success' => false,
                'label_url' => null,
                'message' => 'Cannot get label: Order has no Shiprocket shipment ID.',
            ];
        }

        if ($order->shipping_label_url) {
            return [
                'success' => true,
                'label_url' => $order->shipping_label_url,
                'message' => 'Shipping label retrieved from cache.',
            ];
        }

        $payload = [
            'shipment_id' => [(int) $order->shipment_id],
        ];

        $res = $this->api('POST', 'courier/generate/label', $payload);

        if (! $res['success']) {
            return [
                'success' => false,
                'label_url' => null,
                'message' => $res['message'] ?? 'Failed to generate shipping label.',
            ];
        }

        $labelUrl = $res['data']['label_url'] ?? null;

        if ($labelUrl) {
            $order->shipping_label_url = $labelUrl;
            $order->save();
        }

        return [
            'success' => ! empty($labelUrl),
            'label_url' => $labelUrl,
            'message' => ! empty($labelUrl) ? 'Shipping label generated successfully.' : 'Label generation pending.',
        ];
    }

    /**
     * Real-time tracking for the order.
     */
    public function trackShipment(Order $order): array
    {
        $trackingCode = $order->awb_code ?: $order->tracking_number;

        if (! $trackingCode && ! $order->shipment_id) {
            return [
                'success' => false,
                'current_status' => $order->status,
                'activities' => [],
                'message' => 'No AWB or tracking number available for this order.',
            ];
        }

        $endpoint = $trackingCode
            ? "courier/track/awb/{$trackingCode}"
            : "courier/track/shipment/{$order->shipment_id}";

        $res = $this->api('GET', $endpoint);

        if (! $res['success']) {
            return [
                'success' => false,
                'current_status' => $order->status,
                'activities' => [],
                'message' => $res['message'] ?? 'Tracking service unavailable.',
            ];
        }

        $trackData = $res['data']['tracking_data'] ?? $res['data'];
        $currentStatus = $trackData['track_status'] ?? ($trackData['shipment_track'][0]['current_status'] ?? $order->status);
        $scans = $trackData['shipment_track_activities'] ?? ($trackData['shipment_track'][0]['scans'] ?? []);

        return [
            'success' => true,
            'current_status' => $currentStatus,
            'activities' => $scans,
            'message' => 'Tracking details retrieved successfully.',
        ];
    }

    /**
     * Check courier serviceability & ETA for a given destination pincode.
     */
    public function checkServiceability(string $pincode, float $weightKg = 0.5, bool $isCod = false): array
    {
        $cleanPincode = trim($pincode);
        if (! preg_match('/^[1-9][0-9]{5}$/', $cleanPincode)) {
            return [
                'serviceable' => false,
                'cod_available' => false,
                'estimated_days' => 0,
                'courier_name' => null,
                'city' => null,
                'state' => null,
                'message' => 'Invalid 6-digit Indian PIN code.',
            ];
        }

        // If credentials are not configured, return default delivery estimates gracefully
        if (! $this->isConfigured()) {
            return [
                'serviceable' => true,
                'cod_available' => true,
                'estimated_days' => 4,
                'courier_name' => 'Standard Express',
                'city' => null,
                'state' => null,
                'message' => 'Standard delivery available.',
            ];
        }

        $queryParams = [
            'pickup_postcode' => $this->pickupPincode,
            'delivery_postcode' => $cleanPincode,
            'weight' => max(0.5, $weightKg),
            'cod' => $isCod ? 1 : 0,
        ];

        $res = $this->api('GET', 'courier/serviceability/', [], $queryParams);

        if (! $res['success']) {
            // Graceful fallback on API error/timeout
            return [
                'serviceable' => true,
                'cod_available' => true,
                'estimated_days' => 4,
                'courier_name' => 'Standard Express',
                'city' => null,
                'state' => null,
                'message' => 'Standard delivery available (fallback).',
            ];
        }

        $availableCouriers = $res['data']['data']['available_courier_companies'] ?? [];

        if (empty($availableCouriers)) {
            return [
                'serviceable' => false,
                'cod_available' => false,
                'estimated_days' => 0,
                'courier_name' => null,
                'city' => null,
                'state' => null,
                'message' => 'Sorry, this pincode is currently unserviceable.',
            ];
        }

        // Pick the top courier with the fastest estimated delivery
        $bestCourier = $availableCouriers[0];
        $etd = (int) ($bestCourier['estimated_delivery_days'] ?? 4);
        if ($etd <= 0) {
            $etd = 4;
        }

        $codAvailable = (bool) ($bestCourier['cod'] ?? 1);

        return [
            'serviceable' => true,
            'cod_available' => $codAvailable,
            'estimated_days' => $etd,
            'courier_name' => $bestCourier['courier_name'] ?? 'Express Courier',
            'city' => $res['data']['data']['delivery_city'] ?? null,
            'state' => $res['data']['data']['delivery_state'] ?? null,
            'message' => "Delivery available in {$etd} days via ".($bestCourier['courier_name'] ?? 'Courier').'.',
        ];
    }

    /**
     * Handle incoming Shiprocket status webhook.
     */
    public function handleWebhook(array $payload, array $headers = []): array
    {
        if ($this->webhookToken) {
            $incomingToken = $headers['x-api-key'][0] ?? ($headers['token'][0] ?? null);
            if ($incomingToken !== $this->webhookToken) {
                return [
                    'success' => false,
                    'message' => 'Unauthorized Shiprocket webhook signature.',
                ];
            }
        }

        $awb = $payload['awb'] ?? ($payload['awb_code'] ?? null);
        $orderCode = $payload['order_id'] ?? null;
        $currentStatus = strtoupper(trim((string) ($payload['current_status'] ?? '')));

        if (! $awb && ! $orderCode) {
            return [
                'success' => false,
                'message' => 'Missing AWB and order_id in webhook payload.',
            ];
        }

        $order = Order::where(function ($q) use ($awb, $orderCode) {
            if ($orderCode) {
                $q->where('order_code', $orderCode);
            }
            if ($awb) {
                $q->orWhere('awb_code', $awb)->orWhere('tracking_number', $awb);
            }
        })->first();

        if (! $order) {
            return [
                'success' => false,
                'message' => "Order not found for code {$orderCode} / AWB {$awb}",
            ];
        }

        // Map Shiprocket status to application Order lifecycle states
        $newStatus = match ($currentStatus) {
            'PICKED UP', 'IN TRANSIT', 'REACHED AT DESTINATION' => 'shipped',
            'OUT FOR DELIVERY' => 'out_for_delivery',
            'DELIVERED' => 'delivered',
            'RTO INITIATED', 'RTO DELIVERED', 'RETURNED' => 'returned',
            'CANCELLED' => 'cancelled',
            default => null,
        };

        if ($newStatus && $newStatus !== $order->status) {
            $from = $order->status;
            $order->status = $newStatus;
            if ($newStatus === 'delivered') {
                if ($order->payment_method === 'cod' && $order->payment_status !== 'paid') {
                    $order->payment_status = 'paid';
                    $order->paid_at = now();
                }
            }
            $order->pushHistory('courier_webhook', "Shiprocket status: {$currentStatus} (updated {$from} → {$newStatus})", null, 'Shiprocket Webhook');
            $order->save();
        }

        return [
            'success' => true,
            'order_code' => $order->order_code,
            'status' => $order->status,
            'message' => 'Courier webhook processed successfully.',
        ];
    }
}

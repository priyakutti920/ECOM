<?php

namespace App\Services\Payment\Drivers;

use App\Models\PaymentGateway;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RazorpayGateway implements PaymentGatewayInterface
{
    private string $keyId;
    private string $keySecret;
    private ?string $webhookSecret;
    private ?PaymentGateway $model;

    public function __construct()
    {
        $this->model = PaymentGateway::where('slug', 'razorpay')->first();
        $creds = $this->model?->credentials ?? [];

        $this->keyId = $creds['key_id'] ?? (string) env('RAZORPAY_KEY_ID', '');
        $this->keySecret = $creds['key_secret'] ?? (string) env('RAZORPAY_KEY_SECRET', '');
        $this->webhookSecret = $creds['webhook_secret'] ?? env('RAZORPAY_WEBHOOK_SECRET', null);
    }

    public function getSlug(): string
    {
        return 'razorpay';
    }

    public function getName(): string
    {
        return $this->model?->name ?: 'Razorpay';
    }

    public function isConfigured(): bool
    {
        return !empty($this->keyId) && !empty($this->keySecret);
    }

    public function createOrder(array $orderData): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Razorpay credentials are not configured.'];
        }

        $amountInPaise = (int) round(((float) $orderData['amount']) * 100);
        $receipt = substr((string) ($orderData['order_id'] ?? time()), 0, 40);

        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->timeout(10)
                ->post('https://api.razorpay.com/v1/orders', [
                    'amount'   => $amountInPaise,
                    'currency' => 'INR',
                    'receipt'  => $receipt,
                    'notes'    => [
                        'order_id' => $orderData['order_id'],
                        'customer' => $orderData['customer_name'] ?? '',
                    ],
                ]);

            $body = $response->json();

            if ($response->successful() && !empty($body['id'])) {
                return [
                    'success'          => true,
                    'gateway_order_id' => $body['id'],
                    'payment_url'      => null, // Razorpay uses client checkout modal
                    'client_payload'   => [
                        'key'             => $this->keyId,
                        'order_id'        => $body['id'],
                        'amount'          => $amountInPaise,
                        'currency'        => 'INR',
                        'name'            => config('app.name', 'Nool & Crop'),
                        'description'     => 'Order #' . $orderData['order_id'],
                        'prefill'         => [
                            'name'    => $orderData['customer_name'] ?? '',
                            'contact' => $orderData['customer_mobile'] ?? '',
                            'email'   => $orderData['customer_email'] ?? '',
                        ],
                    ],
                    'message'          => 'Razorpay order created.',
                ];
            }

            Log::error('Razorpay createOrder failed', ['body' => $body, 'status' => $response->status()]);
            return [
                'success' => false,
                'message' => $body['error']['description'] ?? 'Failed to initiate Razorpay order.',
            ];
        } catch (\Throwable $e) {
            Log::error('Razorpay createOrder exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Razorpay connection error: ' . $e->getMessage()];
        }
    }

    public function checkStatus(string $orderId, ?string $gatewayPaymentId = null): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Razorpay is not configured.'];
        }

        try {
            if ($gatewayPaymentId) {
                $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                    ->timeout(10)
                    ->get("https://api.razorpay.com/v1/payments/{$gatewayPaymentId}");

                $body = $response->json();
                if ($response->successful()) {
                    $status = $body['status'] ?? '';
                    $isPaid = in_array($status, ['captured', 'authorized'], true);
                    $isFailed = in_array($status, ['failed'], true);
                    $amount = isset($body['amount']) ? round(((float)$body['amount']) / 100, 2) : null;

                    return [
                        'success'        => true,
                        'is_paid'        => $isPaid,
                        'is_pending'     => !$isPaid && !$isFailed,
                        'is_failed'      => $isFailed,
                        'amount'         => $amount,
                        'transaction_id' => $body['id'] ?? $gatewayPaymentId,
                        'raw'            => $body,
                    ];
                }
            }

            // Fallback: query payments by order ID
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->timeout(10)
                ->get("https://api.razorpay.com/v1/orders/{$orderId}/payments");

            $body = $response->json();
            if ($response->successful() && !empty($body['items'])) {
                foreach ($body['items'] as $item) {
                    if (($item['status'] ?? '') === 'captured') {
                        return [
                            'success'        => true,
                            'is_paid'        => true,
                            'is_pending'     => false,
                            'is_failed'      => false,
                            'amount'         => round(((float)$item['amount']) / 100, 2),
                            'transaction_id' => $item['id'],
                            'raw'            => $item,
                        ];
                    }
                }
            }

            return [
                'success'    => true,
                'is_paid'    => false,
                'is_pending' => true,
                'is_failed'  => false,
                'amount'     => null,
                'raw'        => $body ?? [],
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Razorpay status check error: ' . $e->getMessage()];
        }
    }

    public function verifySignature(array $data, string $signature): bool
    {
        $orderId = $data['razorpay_order_id'] ?? '';
        $paymentId = $data['razorpay_payment_id'] ?? '';

        if (empty($orderId) || empty($paymentId) || empty($signature)) {
            return false;
        }

        $expected = hash_hmac('sha256', $orderId . '|' . $paymentId, $this->keySecret);
        return hash_equals($expected, $signature);
    }

    public function verifyWebhook(array $payload, array $headers, string $rawContent = ''): array
    {
        $signature = $headers['x-razorpay-signature'][0] ?? ($headers['X-Razorpay-Signature'] ?? '');

        if ($this->webhookSecret && $signature) {
            $expected = hash_hmac('sha256', $rawContent, $this->webhookSecret);
            if (!hash_equals($expected, $signature)) {
                Log::warning('Razorpay webhook signature mismatch');
                return ['success' => false, 'message' => 'Invalid webhook signature'];
            }
        }

        $event = $payload['event'] ?? '';
        $paymentEntity = $payload['payload']['payment']['entity'] ?? [];
        $orderEntity = $payload['payload']['order']['entity'] ?? [];

        $isPaid = ($event === 'order.paid' || $event === 'payment.captured');
        $isFailed = ($event === 'payment.failed');

        $orderId = $paymentEntity['order_id'] ?? ($orderEntity['id'] ?? null);
        $paymentId = $paymentEntity['id'] ?? null;
        $amount = isset($paymentEntity['amount']) ? round(((float)$paymentEntity['amount']) / 100, 2) : null;

        return [
            'success'            => true,
            'is_paid'            => $isPaid,
            'is_failed'          => $isFailed,
            'order_id'           => $orderId,
            'gateway_payment_id' => $paymentId,
            'amount'             => $amount,
            'message'            => "Event: {$event}",
        ];
    }
}

<?php

namespace App\Services\Order;

use App\Http\Controllers\InvoiceController;
use App\Mail\AdminOrderNotificationMail;
use App\Mail\OrderInvoiceMail;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Services\BonusService;
use App\Services\Inventory\InventoryService;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Create or materialize an order from a pending checkout payload and payment details.
     */
    public function createFromPending(array $pending, array $paymentDetails = []): Order
    {
        return DB::transaction(function () use ($pending, $paymentDetails) {
            $paymentOrderId = $paymentDetails['payment_order_id'] ?? ($pending['payment_order_id'] ?? null);

            // Idempotency: if already exists, return existing order
            if ($paymentOrderId) {
                $existing = Order::where('payment_order_id', $paymentOrderId)
                    ->lockForUpdate()
                    ->first();
                if ($existing) {
                    return $existing;
                }
            }

            $order = Order::create([
                'order_code' => Order::nextOrderCode('NS'),
                'customer_id' => $pending['customer_id'],
                'address_id' => $pending['address']['id'] ?? null,

                'contact_name' => $pending['contact']['name'],
                'contact_mobile' => $pending['contact']['mobile'],
                'contact_email' => $pending['contact']['email'] ?? null,

                'addr_full_name' => $pending['address']['full_name'],
                'addr_line_1' => $pending['address']['line1'],
                'addr_line_2' => $pending['address']['line2'] ?? null,
                'addr_city' => $pending['address']['city'],
                'addr_state' => $pending['address']['state'],
                'addr_pincode' => $pending['address']['pincode'],
                'addr_mobile_primary' => $pending['address']['mobile_primary'],
                'addr_mobile_alternate' => $pending['address']['mobile_alternate'] ?? null,
                'addr_type' => $pending['address']['type'] ?? 'home',

                'subtotal' => $pending['subtotal'],
                'discount' => $pending['discount'] ?? 0,
                'tax_amount' => $pending['tax_amount'] ?? 0,
                'shipping' => $pending['shipping'] ?? 0,
                'total' => $pending['total'],

                'payment_method' => $paymentDetails['payment_method'] ?? ($pending['payment_method'] ?? 'upi'),
                'payment_status' => $paymentDetails['payment_status'] ?? 'paid',
                'payment_gateway' => $paymentDetails['payment_gateway'] ?? ($pending['payment_gateway'] ?? 'upi'),
                'payment_order_id' => $paymentOrderId,
                'gateway_payment_id' => $paymentDetails['gateway_payment_id'] ?? null,
                'gateway_signature' => $paymentDetails['gateway_signature'] ?? null,
                'payment_utr' => $paymentDetails['payment_utr'] ?? null,
                'paid_at' => $paymentDetails['paid_at'] ?? now(),

                'status' => Order::STATUS_PLACED,
            ]);

            // Batch load products with providers to avoid N+1 query issue
            $itemIds = collect($pending['items'] ?? [])->pluck('id')->filter()->all();
            $products = Product::with(['providers', 'variations', 'options'])
                ->whereIn('id', $itemIds)
                ->get()
                ->keyBy('id');

            foreach ($pending['items'] ?? [] as $item) {
                $p = $products->get($item['id']);
                $optIds = collect($item['options'] ?? [])->pluck('id')->filter()->all();
                $unit = $p ? $p->calculateUnitPrice($item['variation_id'] ?? null, $optIds)
                           : (float) ($item['price'] ?? 0);
                $qty = max(1, (int) ($item['qty'] ?? 1));

                $providerNames = [];
                $providerIds = [];
                if ($p && $p->relationLoaded('providers')) {
                    $providerNames = $p->providers->pluck('name')->all();
                    $providerIds = $p->providers->pluck('id')->all();
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $p?->id,
                    'product_name' => $p?->name ?? ($item['name'] ?? 'Product #'.$item['id']),
                    'product_slug' => $p?->slug,
                    'product_image' => $p?->image_url ?? ($item['image'] ?? null),
                    'sku' => $item['sku'] ?? ($p?->sku ?? ($p?->code ?? null)),
                    'quantity' => $qty,
                    'unit_price' => $unit,
                    'line_total' => $unit * $qty,
                    'variation_id' => $item['variation_id'] ?? null,
                    'variation_name' => $item['variation_name'] ?? null,
                    'color' => $item['color'] ?? null,
                    'options' => $item['options'] ?? null,
                    'provider_names' => json_encode(array_values($providerNames)),
                    'provider_ids' => json_encode(array_values($providerIds)),
                ]);
            }

            // Deduct inventory via central InventoryService
            $this->inventoryService->deductForOrder($order);

            // Coupon redemption
            if (! empty($pending['coupon_code'])) {
                $coupon = Coupon::where('code', $pending['coupon_code'])
                    ->where('is_active', true)
                    ->whereNull('used_at')
                    ->first();
                if ($coupon) {
                    $coupon->markUsed($order->id);
                }
            }

            // Auto-issue bonus coupon
            try {
                app(BonusService::class)->evaluateAndIssue($order);
            } catch (\Throwable $e) {
                Log::warning('BonusService evaluateAndIssue failed: '.$e->getMessage());
            }

            // Create authoritative invoice record
            try {
                InvoiceController::createFromOrder($order);
            } catch (\Throwable $e) {
                Log::warning('Auto-invoice creation failed: '.$e->getMessage());
            }

            // Email customer invoice
            $this->sendInvoiceEmail($order);

            // Email admin notification
            $adminEmail = StoreSetting::getValue('email', config('mail.from.address'));
            if ($adminEmail) {
                try {
                    Mail::to($adminEmail)->send(new AdminOrderNotificationMail($order));
                } catch (\Throwable $e) {
                    Log::warning('Admin notification email failed: '.$e->getMessage());
                }
            }

            // WhatsApp automated notification
            try {
                app(WhatsAppService::class)->sendAutomatedNotification($order);
            } catch (\Throwable $e) {
                Log::warning('WhatsApp notification failed: '.$e->getMessage());
            }

            return $order;
        });
    }

    /**
     * Create COD order directly.
     */
    public function createCodOrder(array $pending): Order
    {
        return $this->createFromPending($pending, [
            'payment_method' => 'cod',
            'payment_status' => 'pending',
            'payment_gateway' => 'cod',
            'payment_order_id' => 'COD'.strtoupper(Str::random(10)),
            'paid_at' => null,
        ]);
    }

    /**
     * Send order invoice email to customer.
     */
    public function sendInvoiceEmail(Order $order): void
    {
        try {
            $email = $order->contact_email ?: optional($order->customer)->email;
            if (empty($email)) {
                return;
            }
            Mail::to($email)->send(new OrderInvoiceMail($order));
        } catch (\Throwable $e) {
            Log::warning('Order invoice email failed: '.$e->getMessage());
        }
    }
}

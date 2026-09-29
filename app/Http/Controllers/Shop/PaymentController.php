<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Mail\AdminOrderNotificationMail;
use App\Mail\OrderInvoiceMail;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\StoreSetting;
use App\Services\BonusService;
use App\Services\UpiPaymentService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /** How long a pending order may sit waiting for the customer to pay. */
    private const PENDING_TTL_MINUTES = 30;

    public function __construct(private UpiPaymentService $upi)
    {
    }

    /**
     * Show the "Pay Now" page for a previously-stashed pending order.
     */
    public function show(string $token)
    {
        $pending = $this->loadPending($token);

        if (!$pending) {
            return redirect()->route('shop.home')
                ->with('info', 'This payment session has expired. Please place your order again.');
        }

        // If somehow already paid (e.g. user refreshes the page after success),
        // surface the existing order.
        if ($pending['order_id'] ?? null) {
            $order = Order::find($pending['order_id']);
            if ($order) {
                return redirect()->route('shop.order.success', ['order' => $order->order_code]);
            }
        }

        $storeName = \App\Models\StoreSetting::getStoreName();
        $gatewayConfigured = $this->upi->isConfigured();

        return view('shop.payment', [
            'token'             => $token,
            'pending'           => $pending,
            'storeName'         => $storeName,
            'gatewayConfigured' => $gatewayConfigured,
        ]);
    }

    /**
     * Customer clicked "Pay Now" — call the UPI gateway and return the payment URL.
     * JSON endpoint so the page can open the URL in a new tab and start polling.
     */
    public function initiate(Request $request, string $token)
    {
        $pending = $this->loadPending($token);

        if (!$pending) {
            return response()->json([
                'success' => false,
                'message' => 'Payment session expired. Please start checkout again.',
            ], 410);
        }

        if (!$this->upi->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'Payment gateway is not configured. Please contact support.',
            ], 503);
        }

        $result = $this->upi->createOrder([
            'order_id'        => $token,                       // we use our temp token AS the gateway order_id
            'amount'          => $pending['total'],
            'customer_mobile' => $pending['contact']['mobile'],
            'redirect_url'    => route('shop.payment.return', ['token' => $token]),
            'remark1'         => 'Order ' . $token,
            'remark2'         => $pending['contact']['name'] ?? '',
        ]);

        if (!$result['success']) {
            Log::warning('UPI createOrder failed', ['token' => $token, 'result' => $result]);
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Could not initiate payment.',
            ], 422);
        }

        // Stash the upstream order_id back into the pending payload
        $pending['gateway_order_id'] = $result['order_id'];
        $pending['payment_url']      = $result['payment_url'];
        $this->savePending($token, $pending);

        return response()->json([
            'success'     => true,
            'payment_url' => $result['payment_url'],
            'order_id'    => $result['order_id'],
        ]);
    }

    /**
     * Polled by the payment page to know when to redirect to the success page.
     * Calls the gateway's status API and, on success, materialises the order in the DB.
     */
    public function status(string $token)
    {
        $pending = $this->loadPending($token);

        if (!$pending) {
            return response()->json(['state' => 'expired'], 410);
        }

        // Already materialised?
        if (!empty($pending['order_id'])) {
            $order = Order::find($pending['order_id']);
            if ($order) {
                return response()->json([
                    'state'    => 'paid',
                    'redirect' => route('shop.order.success', ['order' => $order->order_code]),
                ]);
            }
        }

        $status = $this->upi->checkStatus($token);

        if (!$status['success']) {
            return response()->json(['state' => 'pending']);
        }

        if (!empty($status['is_paid'])) {
            $order = $this->materialiseOrder($token, $pending, $status);
            return response()->json([
                'state'    => 'paid',
                'redirect' => route('shop.order.success', ['order' => $order->order_code]),
            ]);
        }

        if (!empty($status['is_failed'])) {
            return response()->json(['state' => 'failed', 'message' => 'Payment failed or was cancelled.']);
        }

        return response()->json(['state' => 'pending']);
    }

    /**
     * The gateway redirects the user here after they finish (or cancel) payment.
     * We check status once on arrival and either show success or send them back to the pay page.
     */
    public function return(Request $request, string $token)
    {
        $pending = $this->loadPending($token);

        // If already materialised, jump straight to success
        if ($pending && !empty($pending['order_id'])) {
            $order = Order::find($pending['order_id']);
            if ($order) {
                return redirect()->route('shop.order.success', ['order' => $order->order_code]);
            }
        }

        if (!$pending) {
            return redirect()->route('shop.home')
                ->with('info', 'Payment session expired.');
        }

        // Synchronous status check on return
        $status = $this->upi->checkStatus($token);

        if (!empty($status['success']) && !empty($status['is_paid'])) {
            $order = $this->materialiseOrder($token, $pending, $status);
            return redirect()->route('shop.order.success', ['order' => $order->order_code]);
        }

        // Otherwise back to the pay page — the poller can keep trying / customer can retry
        return redirect()->route('shop.payment.show', ['token' => $token])
            ->with('info', 'Payment not confirmed yet. If you completed the payment, please wait a few seconds.');
    }

    /**
     * Webhook posted by upicheckout. CSRF-exempted via VerifyCsrfToken::$except.
     * Trusts the gateway's status, then re-verifies via checkStatus before materialising.
     */
    public function webhook(Request $request)
    {
        $orderId = $request->input('order_id');
        $status  = $request->input('status');

        Log::info('UPI webhook received', $request->all());

        if (!$orderId) {
            return response()->json(['success' => false, 'message' => 'missing order_id'], 400);
        }

        // The webhook's order_id IS our temp token (we sent it on createOrder)
        $pending = $this->loadPending($orderId);

        if (!$pending) {
            // Already materialised or expired — webhook arrives once, this is fine
            return response()->json(['success' => true, 'message' => 'no pending order, ignored']);
        }

        // Verify with status API directly from the payment provider to prevent spoofing
        $check = $this->upi->checkStatus($orderId);

        if (!empty($check['success']) && !empty($check['is_paid'])) {
            $this->materialiseOrder($orderId, $pending, $check);
            return response()->json(['success' => true, 'message' => 'order materialised']);
        }

        if (!empty($check['is_failed'])) {
            Log::info("UPI webhook: payment failed or cancelled for token {$orderId}");
            return response()->json(['success' => false, 'message' => 'payment failed']);
        }

        Log::warning("UPI webhook: checkStatus did not verify payment for {$orderId}", ['webhook_data' => $request->all(), 'check_result' => $check]);
        return response()->json(['success' => false, 'message' => 'payment could not be verified with gateway'], 422);
    }

    /**
     * Order success page — shown after a confirmed payment.
     * Looked up by the friendly order_code (NS0001, ...). Ownership-guarded.
     */
    public function success(string $order)
    {
        $orderModel = Order::where('order_code', $order)
            ->with('items')
            ->firstOrFail();

        abort_if($orderModel->customer_id !== Auth::guard('customer')->id(), 403);

        $storeName = \App\Models\StoreSetting::getStoreName();

        return view('shop.order-success', [
            'storeName' => $storeName,
            'order'     => $orderModel,
        ]);
    }

    // ────────────────────────────────────────────────────
    // Helpers
    // ────────────────────────────────────────────────────

    private function cacheKey(string $token): string
    {
        return 'pending_order:' . $token;
    }

    private function loadPending(string $token): ?array
    {
        return Cache::get($this->cacheKey($token));
    }

    private function savePending(string $token, array $payload): void
    {
        Cache::put($this->cacheKey($token), $payload, now()->addMinutes(self::PENDING_TTL_MINUTES));
    }

    /**
     * Stash a brand-new pending order and return its token.
     * Called from CartController::placeOrder.
     */
    public static function stashPending(array $payload): string
    {
        $token = 'TMP' . strtoupper(Str::random(10));
        Cache::put('pending_order:' . $token, $payload, now()->addMinutes(self::PENDING_TTL_MINUTES));
        return $token;
    }

    /**
     * Idempotently create the orders + order_items rows for a confirmed payment.
     */
    private function materialiseOrder(string $token, array $pending, array $status): Order
    {
        return DB::transaction(function () use ($token, $pending, $status) {
            // Re-check with a lock — webhook + status poll can both arrive
            $existing = Order::where('payment_order_id', $token)->lockForUpdate()->first();
            if ($existing) {
                return $existing;
            }

            $order = Order::create([
                'order_code'             => Order::nextOrderCode('NS'),
                'customer_id'            => $pending['customer_id'],
                'address_id'             => $pending['address']['id'] ?? null,

                'contact_name'           => $pending['contact']['name'],
                'contact_mobile'         => $pending['contact']['mobile'],
                'contact_email'          => $pending['contact']['email'] ?? null,

                'addr_full_name'         => $pending['address']['full_name'],
                'addr_line_1'            => $pending['address']['line1'],
                'addr_line_2'            => $pending['address']['line2'] ?? null,
                'addr_city'              => $pending['address']['city'],
                'addr_state'             => $pending['address']['state'],
                'addr_pincode'           => $pending['address']['pincode'],
                'addr_mobile_primary'    => $pending['address']['mobile_primary'],
                'addr_mobile_alternate'  => $pending['address']['mobile_alternate'] ?? null,
                'addr_type'              => $pending['address']['type'],

                'subtotal'               => $pending['subtotal'],
                'discount'               => $pending['discount'],
                'shipping'               => $pending['shipping'] ?? 0,
                'total'                  => $pending['total'],

                'payment_method'         => 'upi',
                'payment_status'         => 'paid',
                'payment_gateway'        => 'upicheckout',
                'payment_order_id'       => $token,
                'payment_utr'            => $status['utr'] ?? null,
                'paid_at'                => now(),

                'status'                 => 'placed',
            ]);

            // Snapshot line items — re-fetch product details server-side, never trust cart prices
            $itemIds   = collect($pending['items'])->pluck('id')->all();
            $products  = Product::whereIn('id', $itemIds)->get()->keyBy('id');

            foreach ($pending['items'] as $item) {
                $p = $products->get($item['id']);
                $optIds = collect($item['options'] ?? [])->pluck('id')->filter()->all();
                $unit = $p ? $p->calculateUnitPrice($item['variation_id'] ?? null, $optIds)
                           : (float) ($item['price'] ?? 0);
                $qty  = max(1, (int) ($item['qty'] ?? 1));

                $row = OrderItem::create([
                    'order_id'       => $order->id,
                    'product_id'     => $p?->id,
                    'product_name'   => $p?->name ?? ($item['name'] ?? 'Product #' . $item['id']),
                    'product_slug'   => $p?->slug,
                    'product_image'  => $p?->image_url ?? ($item['image'] ?? null),
                    'quantity'       => $qty,
                    'unit_price'     => $unit,
                    'line_total'     => $unit * $qty,
                    'variation_id'   => $item['variation_id'] ?? null,
                    'variation_name' => $item['variation_name'] ?? null,
                    'color'          => $item['color'] ?? null,
                    'options'        => $item['options'] ?? null,
                ]);

                // Decrement stock
                if ($p) {
                    $p->decrement('qty', $qty);
                    if ($p->qty <= 0) {
                        $p->update(['stock_status' => 'out_of_stock']);
                    }
                }
                if (!empty($item['variation_id'])) {
                    $v = ProductVariation::find($item['variation_id']);
                    if ($v && $v->manage_inventory) {
                        $v->decrement('qty', $qty);
                        if ($v->qty <= 0) {
                            $v->update(['stock_status' => 'out_of_stock']);
                        }
                    }
                }

                // Snapshot the providers so the order view is stable even if
                // the product_provider pivot later changes.
                if ($p) {
                    $providers = $p->load('providers')->providers;
                    $names = $providers->pluck('name')->all();
                    $ids   = $providers->pluck('id')->all();
                    \Illuminate\Support\Facades\DB::table('order_items')
                        ->where('id', $row->id)
                        ->update([
                            'provider_names' => json_encode(array_values($names)),
                            'provider_ids'   => json_encode(array_values($ids)),
                        ]);
                }
            }

            // Coupon redemption (if a coupon was applied on this order) — only
            // mark the coupon used when the order was paid.
            if (!empty($pending['coupon_code'])) {
                $coupon = Coupon::where('code', $pending['coupon_code'])
                    ->where('is_active', true)
                    ->whereNull('used_at')
                    ->first();
                if ($coupon) {
                    $coupon->markUsed($order->id);
                }
            }

            // Auto-issue a coupon for this order (BonusService) — the new
            // % - off the order total gets credited to the customer's account.
            try {
                app(BonusService::class)->evaluateAndIssue($order);
            } catch (\Throwable $e) {
                Log::warning('BonusService evaluateAndIssue failed', [
                    'order_id' => $order->id,
                    'error'    => $e->getMessage(),
                ]);
            }

            // Mark the pending payload as materialised so subsequent polls/webhook short-circuit
            $pending['order_id'] = $order->id;
            $this->savePending($token, $pending);

            Log::info("Order materialised: {$order->order_code} (token {$token})");

            // Email the invoice PDF to the customer
            $this->sendInvoiceEmail($order);

            // Notify store admin
            $adminEmail = StoreSetting::getValue('email', config('mail.from.address'));
            if ($adminEmail) {
                try {
                    Mail::to($adminEmail)->send(new AdminOrderNotificationMail($order));
                } catch (\Throwable $e) {
                    Log::warning('Admin order notification email failed: ' . $e->getMessage());
                }
            }

            // Automatically generate invoice record in invoices table
            try {
                \App\Http\Controllers\InvoiceController::createFromOrder($order);
            } catch (\Throwable $e) {
                Log::warning('Auto-invoice creation failed: ' . $e->getMessage());
            }

            return $order;
        });
    }

    /**
     * Create an order directly with COD (Cash on Delivery)
     */
    public static function createCodOrder(array $pending): Order
    {
        return DB::transaction(function () use ($pending) {
            $order = Order::create([
                'order_code'             => Order::nextOrderCode('NS'),
                'customer_id'            => $pending['customer_id'],
                'address_id'             => $pending['address']['id'] ?? null,

                'contact_name'           => $pending['contact']['name'],
                'contact_mobile'         => $pending['contact']['mobile'],
                'contact_email'          => $pending['contact']['email'] ?? null,

                'addr_full_name'         => $pending['address']['full_name'],
                'addr_line_1'            => $pending['address']['line1'],
                'addr_line_2'            => $pending['address']['line2'] ?? null,
                'addr_city'              => $pending['address']['city'],
                'addr_state'             => $pending['address']['state'],
                'addr_pincode'           => $pending['address']['pincode'],
                'addr_mobile_primary'    => $pending['address']['mobile_primary'],
                'addr_mobile_alternate'  => $pending['address']['mobile_alternate'] ?? null,
                'addr_type'              => $pending['address']['type'],

                'subtotal'               => $pending['subtotal'],
                'discount'               => $pending['discount'],
                'shipping'               => $pending['shipping'] ?? 0,
                'total'                  => $pending['total'],

                'payment_method'         => 'cod',
                'payment_status'         => 'pending',
                'payment_gateway'        => 'cod',
                'payment_order_id'       => 'COD' . strtoupper(Str::random(10)),
                'paid_at'                => null,

                'status'                 => Order::STATUS_PLACED,
            ]);

            $itemIds  = collect($pending['items'])->pluck('id')->all();
            $products = Product::whereIn('id', $itemIds)->with(['variations', 'options'])->get()->keyBy('id');

            foreach ($pending['items'] as $item) {
                $p = $products->get($item['id']);
                $optIds = collect($item['options'] ?? [])->pluck('id')->filter()->all();
                $unit = $p ? $p->calculateUnitPrice($item['variation_id'] ?? null, $optIds)
                           : (float) ($item['price'] ?? 0);
                $qty  = max(1, (int) ($item['qty'] ?? 1));

                $row = OrderItem::create([
                    'order_id'       => $order->id,
                    'product_id'     => $p?->id,
                    'product_name'   => $p?->name ?? ($item['name'] ?? 'Product #' . $item['id']),
                    'product_slug'   => $p?->slug,
                    'product_image'  => $p?->image_url ?? ($item['image'] ?? null),
                    'quantity'       => $qty,
                    'unit_price'     => $unit,
                    'line_total'     => $unit * $qty,
                    'variation_id'   => $item['variation_id'] ?? null,
                    'variation_name' => $item['variation_name'] ?? null,
                    'color'          => $item['color'] ?? null,
                    'options'        => $item['options'] ?? null,
                ]);

                // Decrement stock
                if ($p) {
                    $p->decrement('qty', $qty);
                    if ($p->qty <= 0) {
                        $p->update(['stock_status' => 'out_of_stock']);
                    }
                }
                if (!empty($item['variation_id'])) {
                    $v = ProductVariation::find($item['variation_id']);
                    if ($v && $v->manage_inventory) {
                        $v->decrement('qty', $qty);
                        if ($v->qty <= 0) {
                            $v->update(['stock_status' => 'out_of_stock']);
                        }
                    }
                }

                if ($p) {
                    $providers = $p->load('providers')->providers;
                    $names = $providers->pluck('name')->all();
                    $ids   = $providers->pluck('id')->all();
                    DB::table('order_items')
                        ->where('id', $row->id)
                        ->update([
                            'provider_names' => json_encode(array_values($names)),
                            'provider_ids'   => json_encode(array_values($ids)),
                        ]);
                }
            }

            if (!empty($pending['coupon_code'])) {
                $coupon = Coupon::where('code', $pending['coupon_code'])
                    ->where('is_active', true)
                    ->whereNull('used_at')
                    ->first();
                if ($coupon) {
                    $coupon->markUsed($order->id);
                }
            }

            // Notifications
            try {
                $email = $order->contact_email ?: optional($order->customer)->email;
                if ($email) {
                    Mail::to($email)->send(new OrderInvoiceMail($order));
                }
            } catch (\Throwable $e) {
                Log::warning('COD order invoice email failed: ' . $e->getMessage());
            }

            $adminEmail = StoreSetting::getValue('email', config('mail.from.address'));
            if ($adminEmail) {
                try {
                    Mail::to($adminEmail)->send(new AdminOrderNotificationMail($order));
                } catch (\Throwable $e) {
                    Log::warning('Admin order notification email failed: ' . $e->getMessage());
                }
            }

            try {
                app(WhatsAppService::class)->sendAutomatedNotification($order);
            } catch (\Throwable $e) {
                Log::warning('WhatsApp notification failed: ' . $e->getMessage());
            }

            // Automatically generate invoice record in invoices table
            try {
                \App\Http\Controllers\InvoiceController::createFromOrder($order);
            } catch (\Throwable $e) {
                Log::warning('Auto-invoice creation for COD failed: ' . $e->getMessage());
            }

            Log::info("COD Order created: {$order->order_code}");
            return $order;
        });
    }

    /**
     * Email the order invoice (with PDF attachment) to the customer.
     * Priority: order contact_email → user account email.
     * Silently logs and continues on failure — never breaks checkout.
     */
    protected function sendInvoiceEmail(Order $order): void
    {
        try {
            $email = $order->contact_email ?: optional($order->customer)->email;
            if (empty($email)) {
                Log::info("Order invoice email skipped: no email for order {$order->order_code}");
                return;
            }
            \Illuminate\Support\Facades\Mail::to($email)
                ->send(new \App\Mail\OrderInvoiceMail($order));
            Log::info("Order invoice emailed to {$email} for {$order->order_code}");
        } catch (\Throwable $e) {
            Log::warning('Order invoice email failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);
        }
    }
}

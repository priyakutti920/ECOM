<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\StoreSetting;
use App\Services\Inventory\InventoryService;
use App\Services\Order\OrderService;
use App\Services\Payment\PaymentManager;
use App\Services\UpiPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    /** How long a pending order may sit waiting for the customer to pay. */
    private const PENDING_TTL_MINUTES = 30;

    public function __construct(
        private UpiPaymentService $upi,
        private PaymentManager $paymentManager,
        private InventoryService $inventoryService,
        private OrderService $orderService
    ) {}

    /**
     * Show the "Pay Now" page for a previously-stashed pending order.
     */
    public function show(Request $request, string $token)
    {
        $pending = $this->loadPending($token);

        if (! $pending) {
            return redirect()->route('shop.home')
                ->with('info', 'This payment session has expired. Please place your order again.');
        }

        // If somehow already paid (e.g. user refreshes the page after success), surface the existing order.
        if (! empty($pending['order_id'])) {
            $order = Order::find($pending['order_id']);
            if ($order) {
                return redirect()->route('shop.order.success', ['order' => $order->order_code]);
            }
        }

        $storeName = StoreSetting::getStoreName();
        $gateways = $this->paymentManager->getActiveGateways();

        // Default or chosen gateway
        $requestedMethod = $request->query('method', $pending['payment_method'] ?? 'upi');
        $activeMethod = $this->paymentManager->isSupported($requestedMethod) ? $requestedMethod : 'upi';

        // Check if chosen gateway driver is configured
        $driver = $this->paymentManager->driver($activeMethod);
        $gatewayConfigured = $driver->isConfigured();

        // Update pending payment method if changed on pay page
        if ($activeMethod !== ($pending['payment_method'] ?? null)) {
            $pending['payment_method'] = $activeMethod;
            $this->savePending($token, $pending);
        }

        return view('shop.payment', [
            'token' => $token,
            'pending' => $pending,
            'storeName' => $storeName,
            'gateways' => $gateways,
            'activeMethod' => $activeMethod,
            'gatewayConfigured' => $gatewayConfigured,
        ]);
    }

    /**
     * Customer clicked "Pay Now" — call the configured gateway and return payment URL or client payload.
     */
    public function initiate(Request $request, string $token)
    {
        $pending = $this->loadPending($token);

        if (! $pending) {
            return response()->json([
                'success' => false,
                'message' => 'Payment session expired. Please start checkout again.',
            ], 410);
        }

        $method = $request->input('payment_method', $pending['payment_method'] ?? 'upi');
        $driver = $this->paymentManager->driver($method);

        if (! $driver->isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => "Payment gateway [{$driver->getName()}] is not configured. Please choose another payment method or contact support.",
            ], 503);
        }

        $result = $driver->createOrder([
            'order_id' => $token,
            'amount' => $pending['total'],
            'customer_name' => $pending['contact']['name'] ?? '',
            'customer_mobile' => $pending['contact']['mobile'] ?? '',
            'customer_email' => $pending['contact']['email'] ?? '',
            'redirect_url' => route('shop.payment.return', ['token' => $token, 'gateway' => $method]),
        ]);

        if (! $result['success']) {
            Log::warning("Gateway [{$method}] createOrder failed", ['token' => $token, 'result' => $result]);

            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Could not initiate payment.',
            ], 422);
        }

        // Stash the upstream order_id and gateway method back into the pending payload
        $pending['payment_method'] = $method;
        $pending['gateway_order_id'] = $result['gateway_order_id'];
        $pending['payment_url'] = $result['payment_url'] ?? null;
        $pending['client_payload'] = $result['client_payload'] ?? [];
        $this->savePending($token, $pending);

        return response()->json([
            'success' => true,
            'method' => $method,
            'payment_url' => $result['payment_url'] ?? null,
            'client_payload' => $result['client_payload'] ?? [],
            'order_id' => $result['gateway_order_id'] ?? $token,
        ]);
    }

    /**
     * Polled by the payment page to know when to redirect to the success page.
     */
    public function status(Request $request, string $token)
    {
        $pending = $this->loadPending($token);

        if (! $pending) {
            return response()->json(['state' => 'expired'], 410);
        }

        // Already materialised?
        if (! empty($pending['order_id'])) {
            $order = Order::find($pending['order_id']);
            if ($order) {
                return response()->json([
                    'state' => 'paid',
                    'redirect' => route('shop.order.success', ['order' => $order->order_code]),
                ]);
            }
        }

        $method = $pending['payment_method'] ?? 'upi';
        $driver = $this->paymentManager->driver($method);

        $status = $driver->checkStatus(
            $pending['gateway_order_id'] ?? $token,
            $pending['gateway_payment_id'] ?? null
        );

        if (! $status['success']) {
            return response()->json(['state' => 'pending']);
        }

        if (! empty($status['is_paid'])) {
            try {
                $status['gateway'] = $method;
                $order = $this->materialiseOrder($token, $pending, $status);

                return response()->json([
                    'state' => 'paid',
                    'redirect' => route('shop.order.success', ['order' => $order->order_code]),
                ]);
            } catch (\UnexpectedValueException $e) {
                return response()->json([
                    'state' => 'failed',
                    'message' => $e->getMessage(),
                ], 400);
            }
        }

        if (! empty($status['is_failed'])) {
            return response()->json(['state' => 'failed', 'message' => 'Payment failed or was cancelled.']);
        }

        return response()->json(['state' => 'pending']);
    }

    /**
     * Verify payment completed on client-side SDK (e.g. Razorpay modal callback).
     */
    public function verifyClientPayment(Request $request, string $token)
    {
        $pending = $this->loadPending($token);

        if (! $pending) {
            return response()->json(['success' => false, 'message' => 'Session expired.'], 410);
        }

        $method = $request->input('payment_method', $pending['payment_method'] ?? 'razorpay');
        $driver = $this->paymentManager->driver($method);

        // 1. Signature verification
        $signatureValid = $driver->verifySignature($request->all(), (string) $request->input('razorpay_signature', ''));
        if (! $signatureValid) {
            Log::warning('Client payment verification failed signature check', ['token' => $token, 'data' => $request->all()]);

            return response()->json(['success' => false, 'message' => 'Payment signature verification failed.'], 400);
        }

        // 2. Query upstream status to verify payment state and amount
        $paymentId = $request->input('razorpay_payment_id');
        $status = $driver->checkStatus($request->input('razorpay_order_id', $token), $paymentId);

        if (empty($status['is_paid'])) {
            return response()->json(['success' => false, 'message' => 'Payment has not been captured yet.'], 422);
        }

        $status['gateway'] = $method;
        $status['transaction_id'] = $paymentId;
        $status['signature'] = $request->input('razorpay_signature');

        try {
            $order = $this->materialiseOrder($token, $pending, $status);

            return response()->json([
                'success' => true,
                'redirect' => route('shop.order.success', ['order' => $order->order_code]),
            ]);
        } catch (\UnexpectedValueException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Return URL after gateway redirect.
     */
    public function return(Request $request, string $token)
    {
        $pending = $this->loadPending($token);

        if ($pending && ! empty($pending['order_id'])) {
            $order = Order::find($pending['order_id']);
            if ($order) {
                return redirect()->route('shop.order.success', ['order' => $order->order_code]);
            }
        }

        if (! $pending) {
            return redirect()->route('shop.home')->with('info', 'Payment session expired.');
        }

        $method = $request->query('gateway', $pending['payment_method'] ?? 'upi');
        $driver = $this->paymentManager->driver($method);

        $status = $driver->checkStatus(
            $pending['gateway_order_id'] ?? $token,
            $pending['gateway_payment_id'] ?? null
        );

        if (! empty($status['success']) && ! empty($status['is_paid'])) {
            try {
                $status['gateway'] = $method;
                $order = $this->materialiseOrder($token, $pending, $status);

                return redirect()->route('shop.order.success', ['order' => $order->order_code]);
            } catch (\UnexpectedValueException $e) {
                return redirect()->route('shop.payment.show', ['token' => $token])->with('info', $e->getMessage());
            }
        }

        return redirect()->route('shop.payment.show', ['token' => $token])
            ->with('info', 'Payment not confirmed yet. If you completed the payment, please wait a few seconds.');
    }

    /**
     * Webhook handler for all payment gateways.
     */
    public function webhook(Request $request, ?string $gateway = null)
    {
        $rawOrderId = $request->input('order_id')
            ?: ($request->input('data.order.order_id')
            ?: ($request->input('payload.payment.entity.order_id')
            ?: $request->input('orderId')));

        if ($rawOrderId) {
            $alreadyProcessed = Order::where('payment_order_id', $rawOrderId)
                ->orWhere('order_code', $rawOrderId)
                ->first();
            if ($alreadyProcessed) {
                return response()->json(['success' => true, 'message' => 'Order already processed']);
            }
        }

        $selectedGateway = $gateway ?: ($request->input('gateway') ?: 'upi');
        $driver = $this->paymentManager->driver($selectedGateway);

        Log::info("Payment webhook received for gateway [{$selectedGateway}]");

        $rawBody = (string) $request->getContent();
        $check = $driver->verifyWebhook($request->all(), $request->headers->all(), $rawBody);

        if (empty($check['success'])) {
            Log::warning("Payment webhook verification failed for gateway [{$selectedGateway}]", ['check' => $check]);

            return response()->json(['success' => false, 'message' => $check['message'] ?? 'Webhook verification failed'], 400);
        }

        $orderId = $check['order_id'] ?? null;
        if (! $orderId) {
            return response()->json(['success' => false, 'message' => 'Missing order reference in webhook'], 400);
        }

        // Idempotency: order already materialized?
        $existingOrder = Order::where('payment_order_id', $orderId)
            ->orWhere('gateway_payment_id', $check['gateway_payment_id'] ?? 'none')
            ->first();

        if ($existingOrder) {
            return response()->json(['success' => true, 'message' => 'Order already processed']);
        }

        $pending = $this->loadPending($orderId);
        if (! $pending) {
            return response()->json(['success' => false, 'message' => 'No pending order found for token'], 404);
        }

        if (! empty($check['is_paid'])) {
            try {
                $check['gateway'] = $selectedGateway;
                $check['transaction_id'] = $check['gateway_payment_id'] ?? null;
                $this->materialiseOrder($orderId, $pending, $check);

                return response()->json(['success' => true, 'message' => 'Order materialized']);
            } catch (\UnexpectedValueException $e) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
            }
        }

        return response()->json(['success' => true, 'message' => 'Webhook received, state: '.($check['is_failed'] ? 'failed' : 'pending')]);
    }

    /**
     * Order success page.
     */
    public function success(string $order)
    {
        $orderModel = Order::where('order_code', $order)
            ->with(['items', 'customer', 'address'])
            ->firstOrFail();

        abort_if($orderModel->customer_id !== Auth::guard('customer')->id(), 403);

        $storeName = StoreSetting::getStoreName();

        return view('shop.order-success', [
            'storeName' => $storeName,
            'order' => $orderModel,
        ]);
    }

    // ────────────────────────────────────────────────────
    // Internal Helpers & Materialization
    // ────────────────────────────────────────────────────

    private function cacheKey(string $token): string
    {
        return 'pending_order:'.$token;
    }

    private function loadPending(string $token): ?array
    {
        return Cache::get($this->cacheKey($token));
    }

    private function savePending(string $token, array $payload): void
    {
        Cache::put($this->cacheKey($token), $payload, now()->addMinutes(self::PENDING_TTL_MINUTES));
    }

    public static function stashPending(array $payload): string
    {
        $token = 'TMP'.strtoupper(Str::random(10));
        Cache::put('pending_order:'.$token, $payload, now()->addMinutes(self::PENDING_TTL_MINUTES));

        return $token;
    }

    /**
     * Idempotently create the orders + order_items rows for a confirmed payment.
     */
    private function materialiseOrder(string $token, array $pending, array $status): Order
    {
        $paidAmount = isset($status['amount']) ? (float) $status['amount'] : (float) $pending['total'];
        $expectedAmount = (float) $pending['total'];

        // Verify amount with 0.05 margin for currency conversion rounding
        if (abs($paidAmount - $expectedAmount) > 0.05) {
            Log::error('Payment amount mismatch', [
                'expected_amount' => $expectedAmount,
                'paid_amount' => $paidAmount,
                'token' => $token,
            ]);

            throw new \UnexpectedValueException('Payment amount verification failed.');
        }

        $order = $this->orderService->createFromPending($pending, [
            'payment_method' => $pending['payment_method'] ?? 'upi',
            'payment_status' => 'paid',
            'payment_gateway' => $status['gateway'] ?? ($pending['payment_method'] ?? 'upi'),
            'payment_order_id' => $token,
            'gateway_payment_id' => $status['transaction_id'] ?? ($status['utr'] ?? null),
            'gateway_signature' => $status['signature'] ?? null,
            'payment_utr' => $status['utr'] ?? null,
            'paid_at' => now(),
        ]);

        // Mark pending payload as materialized
        $pending['order_id'] = $order->id;
        $this->savePending($token, $pending);

        Log::info("Order materialized: {$order->order_code} (token {$token})");

        return $order;
    }

    /**
     * Create an order directly with COD (Cash on Delivery)
     */
    public static function createCodOrder(array $pending): Order
    {
        return app(OrderService::class)->createCodOrder($pending);
    }

    protected function sendInvoiceEmail(Order $order): void
    {
        $this->orderService->sendInvoiceEmail($order);
    }
}

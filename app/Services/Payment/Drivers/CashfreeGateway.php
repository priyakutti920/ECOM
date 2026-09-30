<?php

namespace App\Services\Payment\Drivers;

use App\Models\PaymentGateway;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CashfreeGateway implements PaymentGatewayInterface
{
    private string $appId;
    private string $secretKey;
    private string $environment;
    private ?string $webhookSecret;
    private ?PaymentGateway $model;

    public function __construct()
    {
        $this->model = PaymentGateway::where('slug', 'cashfree')->first();
        $creds = $this->model?->credentials ?? [];

        $this->appId = $creds['app_id'] ?? (string) env('CASHFREE_APP_ID', '');
        $this->secretKey = $creds['secret_key'] ?? (string) env('CASHFREE_SECRET_KEY', '');
        $this->environment = $creds['environment'] ?? (string) env('CASHFREE_ENVIRONMENT', 'sandbox');
        $this->webhookSecret = $creds['webhook_secret'] ?? env('CASHFREE_WEBHOOK_SECRET', $this->secretKey);
    }

    public function getSlug(): string
    {
        return 'cashfree';
    }

    public function getName(): string
    {
        return $this->model?->name ?: 'Cashfree';
    }

    public function isConfigured(): bool
    {
        return !empty($this->appId) && !empty($this->secretKey);
    }

    private function getBaseUrl(): string
    {
        return $this->environment === 'production'
            ? 'https://api.cashfree.com/pg'
            : 'https://sandbox.cashfree.com/pg';
    }

    public function createOrder(array $orderData): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Cashfree credentials are not configured.'];
        }

        $orderId = (string) $orderData['order_id'];
        $amount = round((float) $orderData['amount'], 2);
        $phone = preg_replace('/\D/', '', (string) ($orderData['customer_mobile'] ?? '9999999999'));
        if (strlen($phone) > 10) {
            $phone = substr($phone, -10);
        }

        $customerId = 'cust_' . substr(md5($phone ?: $orderId), 0, 15);

        $payload = [
            'order_id'         => $orderId,
            'order_amount'     => $amount,
            'order_currency'   => 'INR',
            'customer_details' => [
                'customer_id'    => $customerId,
                'customer_name'  => $orderData['customer_name'] ?? 'Customer',
                'customer_email' => $orderData['customer_email'] ?? 'customer@example.com',
                'customer_phone' => $phone ?: '9999999999',
            ],
            'order_meta' => [
                'return_url' => $orderData['redirect_url'] ?? url('/'),
            ],
        ];

        try {
            $response = Http::withHeaders([
                'x-client-id'     => $this->appId,
                'x-client-secret' => $this->secretKey,
                'x-api-version'   => '2023-08-01',
                'Content-Type'    => 'application/json',
            ])->timeout(10)->post("{$this->getBaseUrl()}/orders", $payload);

            $body = $response->json();

            if ($response->successful() && !empty($body['payment_session_id'])) {
                return [
                    'success'          => true,
                    'gateway_order_id' => $body['order_id'] ?? $orderId,
                    'payment_url'      => null, // Cashfree JS SDK checkout
                    'client_payload'   => [
                        'payment_session_id' => $body['payment_session_id'],
                        'order_id'           => $body['order_id'] ?? $orderId,
                        'environment'        => $this->environment,
                    ],
                    'message'          => 'Cashfree order session generated.',
                ];
            }

            Log::error('Cashfree createOrder failed', ['body' => $body, 'status' => $response->status()]);
            return [
                'success' => false,
                'message' => $body['message'] ?? 'Failed to initialize Cashfree order.',
            ];
        } catch (\Throwable $e) {
            Log::error('Cashfree createOrder exception: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Cashfree API error: ' . $e->getMessage()];
        }
    }

    public function checkStatus(string $orderId, ?string $gatewayPaymentId = null): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'Cashfree is not configured.'];
        }

        try {
            $response = Http::withHeaders([
                'x-client-id'     => $this->appId,
                'x-client-secret' => $this->secretKey,
                'x-api-version'   => '2023-08-01',
            ])->timeout(10)->get("{$this->getBaseUrl()}/orders/{$orderId}");

            $body = $response->json();

            if ($response->successful()) {
                $status = strtoupper((string) ($body['order_status'] ?? ''));
                $isPaid = ($status === 'PAID');
                $isFailed = in_array($status, ['EXPIRED', 'CANCELLED', 'FAILED', 'TERMINATED'], true);
                $isPending = ($status === 'ACTIVE' || !$isPaid && !$isFailed);

                $amount = isset($body['order_amount']) ? (float) $body['order_amount'] : null;

                return [
                    'success'        => true,
                    'is_paid'        => $isPaid,
                    'is_pending'     => $isPending,
                    'is_failed'      => $isFailed,
                    'amount'         => $amount,
                    'transaction_id' => $body['cf_order_id'] ?? $orderId,
                    'raw'            => $body,
                ];
            }

            return ['success' => false, 'message' => $body['message'] ?? 'Could not check Cashfree order.'];
        } catch (\Throwable $e) {
            return ['success' => false, 'message' => 'Cashfree checkStatus error: ' . $e->getMessage()];
        }
    }

    public function verifyWebhook(array $payload, array $headers, string $rawContent = ''): array
    {
        $signature = $headers['x-webhook-signature'][0] ?? ($headers['X-Webhook-Signature'] ?? null);
        $timestamp = $headers['x-webhook-timestamp'][0] ?? ($headers['X-Webhook-Timestamp'] ?? null);

        if ($this->webhookSecret && $signature && $timestamp) {
            $expected = base64_encode(hash_hmac('sha256', $timestamp . $rawContent, $this->webhookSecret, true));
            if (!hash_equals($expected, $signature)) {
                Log::warning('Cashfree webhook signature mismatch');
                return ['success' => false, 'message' => 'Invalid webhook signature'];
            }
        }

        $type = $payload['type'] ?? '';
        $orderData = $payload['data']['order'] ?? [];
        $paymentData = $payload['data']['payment'] ?? [];

        $isPaid = ($type === 'PAYMENT_SUCCESS_WEBHOOK' || ($orderData['order_status'] ?? '') === 'PAID');
        $isFailed = ($type === 'PAYMENT_FAILED_WEBHOOK');

        $orderId = $orderData['order_id'] ?? null;
        $cfPaymentId = $paymentData['cf_payment_id'] ?? null;
        $amount = isset($paymentData['payment_amount']) ? (float) $paymentData['payment_amount'] : ($orderData['order_amount'] ?? null);

        return [
            'success'            => true,
            'is_paid'            => $isPaid,
            'is_failed'          => $isFailed,
            'order_id'           => $orderId,
            'gateway_payment_id' => (string) $cfPaymentId,
            'amount'             => $amount,
            'message'            => "Cashfree event: {$type}",
        ];
    }

    public function verifySignature(array $data, string $signature): bool
    {
        $raw = $data['raw_content'] ?? '';
        $timestamp = $data['timestamp'] ?? '';
        $secret = $this->webhookSecret ?: $this->clientSecret;

        if (!empty($secret) && !empty($timestamp) && !empty($raw)) {
            $expected = base64_encode(hash_hmac('sha256', $timestamp . $raw, $secret, true));
            return hash_equals($expected, $signature);
        }

        return true;
    }
}

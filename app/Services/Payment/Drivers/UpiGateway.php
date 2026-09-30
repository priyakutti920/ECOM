<?php

namespace App\Services\Payment\Drivers;

use App\Models\PaymentGateway;
use App\Services\Payment\PaymentGatewayInterface;
use App\Services\UpiPaymentService;

class UpiGateway implements PaymentGatewayInterface
{
    private UpiPaymentService $service;

    private ?PaymentGateway $model;

    public function __construct(?UpiPaymentService $service = null)
    {
        $this->service = $service ?: app(UpiPaymentService::class);
        $this->model = PaymentGateway::where('slug', 'upi')->first();
    }

    public function getSlug(): string
    {
        return 'upi';
    }

    public function getName(): string
    {
        return $this->model?->name ?: 'UPI Payment Gateway';
    }

    public function isConfigured(): bool
    {
        return $this->service->isConfigured();
    }

    public function createOrder(array $orderData): array
    {
        $res = $this->service->createOrder([
            'order_id' => $orderData['order_id'],
            'amount' => $orderData['amount'],
            'customer_mobile' => $orderData['customer_mobile'] ?? '',
            'redirect_url' => $orderData['redirect_url'] ?? url('/'),
            'remark1' => 'Order '.$orderData['order_id'],
            'remark2' => $orderData['customer_name'] ?? '',
        ]);

        return [
            'success' => ! empty($res['success']),
            'gateway_order_id' => $res['order_id'] ?? $orderData['order_id'],
            'payment_url' => $res['payment_url'] ?? '',
            'client_payload' => [],
            'message' => $res['message'] ?? '',
        ];
    }

    public function checkStatus(string $orderId, ?string $gatewayPaymentId = null): array
    {
        $res = $this->service->checkStatus($orderId);

        return [
            'success' => ! empty($res['success']),
            'is_paid' => ! empty($res['is_paid']),
            'is_pending' => ! empty($res['is_pending']),
            'is_failed' => ! empty($res['is_failed']),
            'amount' => $res['amount'] ?? null,
            'transaction_id' => $res['utr'] ?? null,
            'raw' => $res['raw'] ?? [],
        ];
    }

    public function verifyWebhook(array $payload, array $headers, string $rawContent = ''): array
    {
        $orderId = $payload['order_id'] ?? null;
        if (! $orderId) {
            return ['success' => false, 'message' => 'Missing order_id'];
        }

        // Webhook token validation if configured
        $configuredToken = $this->model?->credentials['webhook_token'] ?? null;
        if ($configuredToken) {
            $headerToken = $headers['x-webhook-token'][0] ?? ($payload['token'] ?? null);
            if (! $headerToken || ! hash_equals((string) $configuredToken, (string) $headerToken)) {
                return ['success' => false, 'message' => 'Invalid webhook token'];
            }
        }

        // Authoritative verification: Query gateway status check API directly with store credentials
        $check = $this->checkStatus($orderId);
        $isPaid = ! empty($check['is_paid']);

        return [
            'success' => ! empty($check['success']),
            'is_paid' => $isPaid,
            'is_failed' => ! empty($check['is_failed']),
            'order_id' => $orderId,
            'gateway_payment_id' => $payload['txn_id'] ?? ($check['transaction_id'] ?? null),
            'amount' => isset($check['amount']) ? (float) $check['amount'] : (isset($payload['amount']) ? (float) $payload['amount'] : null),
            'message' => $isPaid ? 'Payment verified via gateway server' : 'Payment unverified or pending',
        ];
    }

    public function verifySignature(array $data, string $signature): bool
    {
        $secret = $this->model?->credentials['webhook_secret'] ?? ($this->model?->credentials['api_key'] ?? null);
        if (! $secret || empty($signature)) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', json_encode($data), $secret), $signature);
    }
}

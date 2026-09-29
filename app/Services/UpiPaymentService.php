<?php

namespace App\Services;

use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class UpiPaymentService
{
    private ?PaymentGateway $gateway = null;

    public function __construct(?string $gatewaySlug = 'upi')
    {
        $this->gateway = PaymentGateway::where('slug', $gatewaySlug)
            ->where('is_active', 1)
            ->where('is_available', 1)
            ->first();
    }

    public function isConfigured(): bool
    {
        if (!$this->gateway) return false;
        $creds = $this->gateway->credentials ?? [];
        return !empty($creds['create_order_url'])
            && !empty($creds['status_check_url'])
            && !empty($creds['api_key']);
    }

    public function createOrder(array $data): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'UPI gateway is not configured.'];
        }

        $creds = $this->gateway->credentials;
        $orderId = $data['order_id'] ?? (string) time() . Str::random(6);
        $amount = $data['amount'] ?? 0;
        $customerMobile = $data['customer_mobile'] ?? '';
        $redirectUrl = $creds['redirect_url'] ?? ($data['redirect_url'] ?? url('/'));
        $remark1 = $data['remark1'] ?? 'order';
        $remark2 = $data['remark2'] ?? '';

        $payload = [
            'user_token'       => $creds['api_key'],
            'order_id'         => $orderId,
            'amount'           => (string) $amount,
            'customer_mobile'  => $customerMobile,
            'redirect_url'     => $redirectUrl,
            'remark1'          => $remark1,
            'remark2'          => $remark2,
        ];

        try {
            $response = Http::timeout(15)
                ->asForm()
                ->post($creds['create_order_url'], $payload);

            $body = $response->json();

            if ($response->successful() && ($body['status'] === true || $body['status'] === 'true')) {
                return [
                    'success'      => true,
                    'order_id'     => $body['result']['orderId'] ?? $orderId,
                    'payment_url'  => $body['result']['payment_url'] ?? '',
                    'message'      => $body['message'] ?? 'Order created.',
                ];
            }

            return [
                'success' => false,
                'message' => $body['message'] ?? 'Failed to create UPI order.',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'UPI API error: ' . $e->getMessage(),
            ];
        }
    }

    public function checkStatus(string $orderId): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'UPI gateway is not configured.'];
        }

        $creds = $this->gateway->credentials;

        try {
            $response = Http::timeout(15)
                ->asForm()
                ->post($creds['status_check_url'], [
                    'user_token' => $creds['api_key'],
                    'order_id'   => $orderId,
                ]);

            $body = $response->json();

            if ($response->successful() && isset($body['status'])) {
                $txnStatus = $body['result']['txnStatus'] ?? $body['status'] ?? '';
                $isSuccess = strtoupper($txnStatus) === 'COMPLETED'
                    || strtoupper($body['status']) === 'COMPLETED'
                    || strtoupper($txnStatus) === 'SUCCESS'
                    || strtoupper($body['status']) === 'SUCCESS';

                return [
                    'success'    => true,
                    'status'     => $txnStatus,
                    'is_paid'    => $isSuccess,
                    'is_pending' => strtoupper($txnStatus) === 'PENDING',
                    'is_failed'  => strtoupper($txnStatus) === 'FAILED' || !$isSuccess,
                    'amount'     => $body['result']['amount'] ?? null,
                    'utr'        => $body['result']['utr'] ?? null,
                    'date'       => $body['result']['date'] ?? null,
                    'raw'        => $body,
                ];
            }

            return [
                'success' => false,
                'message' => $body['message'] ?? 'Failed to check order status.',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'UPI API error: ' . $e->getMessage(),
            ];
        }
    }
}

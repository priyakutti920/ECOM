<?php

namespace App\Services;

use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class UpiPaymentService
{
    private ?PaymentGateway $gateway = null;
    private ?string $customApiKey = null;
    private ?string $customCreateOrderUrl = null;
    private ?string $customStatusCheckUrl = null;

    public function __construct(?string $gatewaySlug = 'upi')
    {
        $this->gateway = PaymentGateway::where('slug', $gatewaySlug)
            ->where('is_active', 1)
            ->where('is_available', 1)
            ->first();
    }

    public static function forWhatsApp(): self
    {
        $instance = new self('upi');

        $waKey = config('services.whatsapp.upi_key') ?: env('WA_UPI_KEY', env('WA_UPI_API_KEY', ''));
        $createUrl = config('services.whatsapp.upi_create_order_url') ?: env('WA_UPI_CREATE_ORDER_URL', 'https://upicheckout.online/api/create-order');
        $statusUrl = config('services.whatsapp.upi_status_check_url') ?: env('WA_UPI_STATUS_CHECK_URL', 'https://upicheckout.online/api/check-order-status');

        if (!empty($waKey)) {
            $instance->customApiKey = $waKey;
            $instance->customCreateOrderUrl = $createUrl;
            $instance->customStatusCheckUrl = $statusUrl;
        }

        return $instance;
    }

    public function getApiKey(): ?string
    {
        return $this->customApiKey ?: ($this->gateway->credentials['api_key'] ?? null);
    }

    public function getCreateOrderUrl(): ?string
    {
        return $this->customCreateOrderUrl ?: ($this->gateway->credentials['create_order_url'] ?? null);
    }

    public function getStatusCheckUrl(): ?string
    {
        return $this->customStatusCheckUrl ?: ($this->gateway->credentials['status_check_url'] ?? null);
    }

    public function isConfigured(): bool
    {
        return !empty($this->getApiKey())
            && !empty($this->getCreateOrderUrl())
            && !empty($this->getStatusCheckUrl());
    }

    public function createOrder(array $data): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'message' => 'UPI gateway is not configured.'];
        }

        $orderId = $data['order_id'] ?? (string) time() . Str::random(6);
        $amount = $data['amount'] ?? 0;
        $customerMobile = $data['customer_mobile'] ?? '';
        $redirectUrl = $data['redirect_url'] ?? ($this->gateway->credentials['redirect_url'] ?? url('/'));
        $remark1 = $data['remark1'] ?? 'order';
        $remark2 = $data['remark2'] ?? '';

        $payload = [
            'user_token'       => $this->getApiKey(),
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
                ->post($this->getCreateOrderUrl(), $payload);

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

        try {
            $response = Http::timeout(15)
                ->asForm()
                ->post($this->getStatusCheckUrl(), [
                    'user_token' => $this->getApiKey(),
                    'order_id'   => $orderId,
                ]);

            $body = $response->json();

            if ($response->successful()) {
                $txnStatus = $body['result']['txnStatus'] ?? $body['status'] ?? '';
                $normalizedStatus = strtoupper((string) $txnStatus);
                $bodyStatus = strtoupper((string) ($body['status'] ?? ''));

                $isSuccess = $normalizedStatus === 'COMPLETED'
                    || $bodyStatus === 'COMPLETED'
                    || $normalizedStatus === 'SUCCESS'
                    || $bodyStatus === 'SUCCESS';

                $isPending = in_array($normalizedStatus, ['PENDING', 'INITIATED', 'OPEN'], true)
                    || (empty($normalizedStatus) && !$isSuccess);

                $isFailed = in_array(
                    $normalizedStatus,
                    ['FAILED', 'FAILURE', 'CANCELLED', 'EXPIRED', 'REJECTED'],
                    true
                );

                return [
                    'success'    => true,
                    'status'     => $txnStatus,
                    'is_paid'    => $isSuccess,
                    'is_pending' => $isPending,
                    'is_failed'  => $isFailed,
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

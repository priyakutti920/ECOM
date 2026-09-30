<?php

namespace App\Services\Payment\Drivers;

use App\Models\PaymentGateway;
use App\Services\Payment\PaymentGatewayInterface;
use Illuminate\Support\Str;

class CodGateway implements PaymentGatewayInterface
{
    private ?PaymentGateway $model;

    public function __construct()
    {
        $this->model = PaymentGateway::whereIn('slug', ['cod', 'manual'])->first();
    }

    public function getSlug(): string
    {
        return 'cod';
    }

    public function getName(): string
    {
        return $this->model?->name ?: 'Cash on Delivery (COD)';
    }

    public function isConfigured(): bool
    {
        // COD requires no external API keys
        return true;
    }

    public function createOrder(array $orderData): array
    {
        $codRef = 'COD' . strtoupper(Str::random(10));

        return [
            'success'          => true,
            'gateway_order_id' => $codRef,
            'payment_url'      => null,
            'client_payload'   => [],
            'message'          => 'Cash on Delivery order initiated.',
        ];
    }

    public function checkStatus(string $orderId, ?string $gatewayPaymentId = null): array
    {
        return [
            'success'        => true,
            'is_paid'        => false,
            'is_pending'     => true,
            'is_failed'      => false,
            'amount'         => null,
            'transaction_id' => null,
            'raw'            => [],
        ];
    }

    public function verifyWebhook(array $payload, array $headers, string $rawContent = ''): array
    {
        return [
            'success' => false,
            'message' => 'COD does not support webhooks',
        ];
    }

    public function verifySignature(array $data, string $signature): bool
    {
        return true;
    }
}

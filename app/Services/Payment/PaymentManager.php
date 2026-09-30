<?php

namespace App\Services\Payment;

use App\Models\PaymentGateway;
use App\Services\Payment\Drivers\CashfreeGateway;
use App\Services\Payment\Drivers\CodGateway;
use App\Services\Payment\Drivers\RazorpayGateway;
use App\Services\Payment\Drivers\UpiGateway;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class PaymentManager
{
    /** @var array<string, PaymentGatewayInterface> */
    protected array $drivers = [];

    /**
     * Get a payment gateway driver instance by slug.
     */
    public function driver(?string $slug = null): PaymentGatewayInterface
    {
        $slug = strtolower(trim((string) ($slug ?: 'upi')));
        if ($slug === 'manual') {
            $slug = 'cod';
        }

        if (isset($this->drivers[$slug])) {
            return $this->drivers[$slug];
        }

        return $this->drivers[$slug] = $this->resolve($slug);
    }

    /**
     * Resolve the given driver.
     */
    protected function resolve(string $slug): PaymentGatewayInterface
    {
        return match ($slug) {
            'razorpay' => app(RazorpayGateway::class),
            'cashfree' => app(CashfreeGateway::class),
            'upi'      => app(UpiGateway::class),
            'cod'      => app(CodGateway::class),
            default    => throw new InvalidArgumentException("Unsupported payment driver [{$slug}]."),
        };
    }

    /**
     * Get all active and available payment gateways configured in the store.
     */
    public function getActiveGateways(): Collection
    {
        $records = PaymentGateway::where('is_available', 1)
            ->where('is_active', 1)
            ->orderBy('id')
            ->get();

        if ($records->isEmpty()) {
            // Default fallback if admin has not toggled any gateway on yet
            return collect([
                [
                    'slug'        => 'upi',
                    'name'        => 'UPI / Online Payment',
                    'description' => 'Pay securely via Google Pay, PhonePe, Paytm, or QR code',
                    'icon'        => 'fa-qrcode',
                ],
                [
                    'slug'        => 'cod',
                    'name'        => 'Cash on Delivery (COD)',
                    'description' => 'Pay upon delivery at your doorstep',
                    'icon'        => 'fa-hand-holding-usd',
                ],
            ]);
        }

        return $records->map(function ($g) {
            $slug = $g->slug === 'manual' ? 'cod' : $g->slug;
            return [
                'id'          => $g->id,
                'slug'        => $slug,
                'name'        => $g->method_name ?: $g->name,
                'description' => $g->description,
                'icon'        => $g->icon ?: 'fa-credit-card',
            ];
        });
    }

    /**
     * Check if a payment method slug is supported and active.
     */
    public function isSupported(string $slug): bool
    {
        $normalized = $slug === 'manual' ? 'cod' : $slug;
        return in_array($normalized, ['upi', 'cod', 'razorpay', 'cashfree'], true);
    }
}

<?php

namespace App\Services\Order;

use App\Models\Order;
use App\Models\StoreSetting;

class OrderFinancialCalculator
{
    public const STORE_HOME_STATE = 'Tamil Nadu';

    /**
     * Compute authoritative order totals from raw subtotal, discount, and address.
     * Formula: total = subtotal - discount + tax + shipping.
     */
    public static function calculate(float $subtotal, float $discount = 0.0, float $shipping = 0.0, ?string $customerState = null): array
    {
        $subtotal = round(max(0, $subtotal), 2);
        $discount = round(min($subtotal, max(0, $discount)), 2);
        $taxableAmount = round($subtotal - $discount, 2);

        // Fetch configured GST percentage (default 0 or configured % in store settings)
        $gstPct = (float) StoreSetting::getValue('gst_percentage', 0);
        $taxAmount = 0.0;
        if ($gstPct > 0) {
            $taxAmount = round($taxableAmount * ($gstPct / 100), 2);
        }

        $shipping = round(max(0, $shipping), 2);
        $total = round($taxableAmount + $taxAmount + $shipping, 2);

        // Determine interstate tax breakdown
        $state = strtolower(trim((string) $customerState));
        $isInterstate = !empty($state) && !str_contains($state, 'tamil') && !str_contains($state, 'tn');

        $cgst = 0.0;
        $sgst = 0.0;
        $igst = 0.0;

        if ($taxAmount > 0) {
            if ($isInterstate) {
                $igst = $taxAmount;
            } else {
                $cgst = round($taxAmount / 2, 2);
                $sgst = round($taxAmount - $cgst, 2);
            }
        }

        return [
            'subtotal'       => $subtotal,
            'discount'       => $discount,
            'taxable_amount' => $taxableAmount,
            'gst_percentage' => $gstPct,
            'tax_amount'     => $taxAmount,
            'cgst'           => $cgst,
            'sgst'           => $sgst,
            'igst'           => $igst,
            'is_interstate'  => $isInterstate,
            'shipping'       => $shipping,
            'total'          => $total,
        ];
    }

    /**
     * Calculate financial totals directly for an Order instance.
     */
    public static function calculateForOrder(Order $order): array
    {
        return self::calculate(
            (float) $order->subtotal,
            (float) $order->discount,
            (float) $order->shipping,
            $order->addr_state
        );
    }
}

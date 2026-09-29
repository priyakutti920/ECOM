<?php

namespace App\Services;

use App\Models\Bonus;
use App\Models\Coupon;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

/**
 * Bonus → auto-generated coupon.
 *
 * When a customer places an order whose subtotal meets a bonus rule
 * (Bonus.min_amount), we automatically issue them a Coupon worth
 * bonus_percent of that subtotal. The coupon is added to their
 * account, has no expiry, and can be redeemed on a future order
 * (subject to the coupon's own min_amount, which is at least ₹100).
 */
class BonusService
{
    /**
     * Evaluate the order's subtotal against every active bonus rule
     * and issue the best-matching coupon to the customer. Returns
     * the issued coupon or null when no rule matches.
     */
    public function evaluateAndIssue(Order $order): ?Coupon
    {
        $subtotal = (float) $order->subtotal;
        if ($subtotal <= 0 || empty($order->customer_id)) return null;

        $rules = Bonus::active()
            ->where('min_amount', '<=', $subtotal)
            ->where('bonus_percent', '>', 0)
            ->orderByDesc('bonus_percent')
            ->get();

        if ($rules->isEmpty()) return null;

        // Pick the best matching rule (highest %).
        $rule = $rules->first();

        // The discount value is the % of the order subtotal.
        $value = round($subtotal * ((float) $rule->bonus_percent / 100), 2);

        // Guardrails: never issue a zero or negative coupon.
        if ($value <= 0) return null;

        // Minimum coupon min_amount is ₹100.
        $couponMin = max(100, (float) $rule->min_amount);

        return DB::transaction(function () use ($order, $rule, $value, $couponMin) {
            return Coupon::issue([
                'type'        => 'flat',
                'value'       => $value,
                'min_amount'  => $couponMin,
                'customer_id' => $order->customer_id,
                'order_id'    => $order->id,
                'bonus_id'    => $rule->id,
                'label'       => trim(($rule->name ?: 'Bonus') . " — {$rule->bonus_percent}% off"),
                'issued_at'   => now(),
                // No expiry — per requirement.
            ]);
        });
    }
}

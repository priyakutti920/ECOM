<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    /**
     * GET /shop/coupons — return the customer's redeemable coupons as JSON.
     * Used by the cart / buy-now page to show "Available coupons" with
     * their code, value, and minimum amount.
     */
    public function myCoupons(Request $request)
    {
        $customerId = Auth::guard('customer')->id();
        if (!$customerId) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $coupons = Coupon::redeemableFor($customerId)
            ->orderByDesc('issued_at')
            ->limit(50)
            ->get(['id', 'code', 'type', 'value', 'min_amount', 'max_discount', 'label', 'issued_at']);

        return response()->json([
            'success' => true,
            'data'    => $coupons->map(function (Coupon $c) {
                return [
                    'code'        => $c->code,
                    'type'        => $c->type,
                    'value'       => (float) $c->value,
                    'min_amount'  => (float) $c->min_amount,
                    'max_discount'=> $c->max_discount !== null ? (float) $c->max_discount : null,
                    'label'       => $c->label,
                    'display'     => $c->type === 'percent'
                        ? (rtrim(rtrim($c->value, '0'), '.') . '% off up to ₹' . number_format($c->max_discount ?? 0, 0))
                        : ('₹' . number_format($c->value, 0) . ' off'),
                ];
            }),
        ]);
    }
}

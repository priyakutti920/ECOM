<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    /** Admin > Coupons: list of every issued coupon. */
    public function index(Request $request)
    {
        $query = Coupon::with(['customer:id,name,email', 'order:id,order_code', 'bonus:id,name'])
            ->orderByDesc('id');

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($w) use ($q) {
                $w->where('code', 'like', "%{$q}%")
                  ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$q}%")
                                                       ->orWhere('email', 'like', "%{$q}%"));
            });
        }

        $coupons = $query->paginate(20)->withQueryString();

        return view('admin.coupons', [
            'coupons' => $coupons,
            'filters' => ['q' => $request->q],
        ]);
    }
}

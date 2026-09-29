<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * GET /admin/users
     * Read-only list of customers + their order stats.
     */
    public function index(Request $request)
    {
        $users = User::query()
            ->where(function ($q) {
                $q->where('is_admin', 0)->orWhereNull('is_admin');
            })
            ->withCount([
                'orders',
                'orders as paid_orders_count' => function ($q) {
                    $q->where('payment_status', 'paid');
                },
            ])
            ->withSum(['orders as total_spent' => function ($q) {
                $q->where('payment_status', 'paid');
            }], 'total')
            ->when($request->q, function ($q) use ($request) {
                $term = trim($request->q);
                $q->where(function ($w) use ($term) {
                    $w->where('name', 'like', "%{$term}%")
                      ->orWhere('email', 'like', "%{$term}%")
                      ->orWhere('mobile', 'like', "%{$term}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.users', [
            'users'   => $users,
            'filters' => $request->only(['q']),
        ]);
    }
}

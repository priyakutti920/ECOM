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
     * List of users & admins + their order stats.
     */
    public function index(Request $request)
    {
        $role = $request->input('role', 'all');

        $query = User::query()
            ->withCount([
                'orders',
                'orders as paid_orders_count' => function ($q) {
                    $q->where('payment_status', 'paid');
                },
            ])
            ->withSum(['orders as total_spent' => function ($q) {
                $q->where('payment_status', 'paid');
            }], 'total');

        if ($role === 'customers') {
            $query->where(function ($q) {
                $q->where('is_admin', 0)->orWhereNull('is_admin');
            });
        } elseif ($role === 'admins') {
            $query->where('is_admin', 1);
        }

        $query->when($request->filled('q'), function ($q) use ($request) {
            $term = trim($request->q);
            $q->where(function ($w) use ($term) {
                $w->where('name', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%")
                  ->orWhere('mobile', 'like', "%{$term}%");
            });
        });

        $totalUsersCount = User::count();
        $customersCount = User::where(function ($q) {
            $q->where('is_admin', 0)->orWhereNull('is_admin');
        })->count();
        $adminsCount = User::where('is_admin', 1)->count();

        $users = $query->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.users', [
            'users'             => $users,
            'filters'           => $request->only(['q', 'role']),
            'currentRole'       => $role,
            'totalUsersCount'   => $totalUsersCount,
            'customersCount'    => $customersCount,
            'adminsCount'       => $adminsCount,
        ]);
    }
}

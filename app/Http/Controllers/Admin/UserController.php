<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

    /**
     * Update user details and access role (is_admin).
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:120',
            'email'    => 'required|email|max:120|unique:users,email,' . $user->id,
            'mobile'   => 'nullable|string|max:20|unique:users,mobile,' . $user->id,
            'is_admin' => 'nullable|boolean',
            'password' => 'nullable|string|min:6',
        ]);

        $newIsAdmin = $request->boolean('is_admin');

        // Prevent current logged-in administrator from revoking their own admin access to avoid lock-out
        if ($user->id === auth()->id() && !$newIsAdmin) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot revoke your own administrator access.',
                ], 422);
            }
            return back()->with('error', 'You cannot revoke your own administrator access.');
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->mobile = !empty($validated['mobile']) ? trim($validated['mobile']) : null;
        $user->is_admin = $newIsAdmin;

        if ($request->filled('password')) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        $user->save();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'User access and profile updated successfully.',
                'user'    => [
                    'id'       => $user->id,
                    'name'     => $user->name,
                    'email'    => $user->email,
                    'mobile'   => $user->mobile,
                    'is_admin' => (bool) $user->is_admin,
                ],
            ]);
        }

        return back()->with('success', 'User access and profile updated successfully.');
    }
}


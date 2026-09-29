@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">

    <div style="margin-bottom:24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <div>
            <h2 style="margin:0; font-weight:700;">Dashboard</h2>
            <p class="text-muted" style="margin:4px 0 0;">Welcome back, <strong>{{ auth()->user()->name }}</strong>!</p>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('admin.orders.index') }}" class="btn btn-primary" style="border-radius:6px; font-weight:600;">
                <i class="fas fa-list"></i> View All Orders
            </a>
            <a href="{{ route('admin.products.create') }}" class="btn btn-success" style="border-radius:6px; font-weight:600;">
                <i class="fas fa-plus"></i> Add Product
            </a>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="row">
        <div class="col-sm-6 col-lg-3">
            <div class="panel panel-default" style="border-radius:10px; overflow:hidden; border:1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <div class="panel-body" style="display:flex; align-items:center; gap:16px; padding:20px;">
                    <div style="width:48px;height:48px;background:#ede9fe;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-users" style="color:#4f46e5;font-size:20px;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:13px; font-weight:500;">Total Users</div>
                        <div style="font-size:24px;font-weight:800;line-height:1.2; color:#1e293b;">{{ number_format($totalUsers) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="panel panel-default" style="border-radius:10px; overflow:hidden; border:1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <div class="panel-body" style="display:flex; align-items:center; gap:16px; padding:20px;">
                    <div style="width:48px;height:48px;background:#dcfce7;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-shopping-cart" style="color:#16a34a;font-size:20px;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:13px; font-weight:500;">Total Orders</div>
                        <div style="font-size:24px;font-weight:800;line-height:1.2; color:#1e293b;">{{ number_format($totalOrders) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="panel panel-default" style="border-radius:10px; overflow:hidden; border:1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <div class="panel-body" style="display:flex; align-items:center; gap:16px; padding:20px;">
                    <div style="width:48px;height:48px;background:#fef9c3;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-box" style="color:#ca8a04;font-size:20px;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:13px; font-weight:500;">Active Products</div>
                        <div style="font-size:24px;font-weight:800;line-height:1.2; color:#1e293b;">{{ number_format($totalProducts) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="panel panel-default" style="border-radius:10px; overflow:hidden; border:1px solid #e2e8f0; box-shadow:0 1px 3px rgba(0,0,0,0.05);">
                <div class="panel-body" style="display:flex; align-items:center; gap:16px; padding:20px;">
                    <div style="width:48px;height:48px;background:#fee2e2;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fas fa-coins" style="color:#dc2626;font-size:20px;"></i>
                    </div>
                    <div>
                        <div class="text-muted" style="font-size:13px; font-weight:500;">Total Revenue</div>
                        <div style="font-size:24px;font-weight:800;line-height:1.2; color:#1e293b;">{{ $currency }}{{ number_format($totalRevenue, 0) }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>{{-- /.row --}}

    {{-- Secondary Status Counters --}}
    <div class="row" style="margin-top:8px;">
        <div class="col-sm-6 col-md-3">
            <div class="panel panel-default" style="border-radius:8px; border-left:4px solid #3b82f6;">
                <div class="panel-body" style="padding:14px 16px;">
                    <div style="font-size:12px; color:#64748b; font-weight:600; text-transform:uppercase;">Pending / Processing</div>
                    <div style="font-size:20px; font-weight:700; color:#1e293b; margin-top:4px;">{{ $pendingOrdersCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="panel panel-default" style="border-radius:8px; border-left:4px solid #10b981;">
                <div class="panel-body" style="padding:14px 16px;">
                    <div style="font-size:12px; color:#64748b; font-weight:600; text-transform:uppercase;">Delivered Orders</div>
                    <div style="font-size:20px; font-weight:700; color:#1e293b; margin-top:4px;">{{ $deliveredOrdersCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="panel panel-default" style="border-radius:8px; border-left:4px solid #ef4444;">
                <div class="panel-body" style="padding:14px 16px;">
                    <div style="font-size:12px; color:#64748b; font-weight:600; text-transform:uppercase;">Cancelled Orders</div>
                    <div style="font-size:20px; font-weight:700; color:#1e293b; margin-top:4px;">{{ $cancelledOrdersCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="panel panel-default" style="border-radius:8px; border-left:4px solid #f59e0b;">
                <div class="panel-body" style="padding:14px 16px; display:flex; justify-content:space-between; align-items:center;">
                    <div>
                        <div style="font-size:12px; color:#64748b; font-weight:600; text-transform:uppercase;">Pending Reviews</div>
                        <div style="font-size:20px; font-weight:700; color:#1e293b; margin-top:4px;">{{ $pendingReviewsCount }}</div>
                    </div>
                    @if($pendingReviewsCount > 0)
                        <a href="{{ route('admin.reviews.index') }}" class="btn btn-xs btn-warning" style="font-weight:600;">Moderate</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Orders Table --}}
    <div class="panel panel-default" style="border-radius:10px; border:1px solid #e2e8f0; margin-top:16px;">
        <div class="panel-heading" style="background:#fff; border-bottom:1px solid #f1f5f9; padding:16px 20px; display:flex; justify-content:space-between; align-items:center;">
            <h4 style="margin:0; font-weight:700; color:#0f172a; font-size:16px;">Recent Orders</h4>
            <a href="{{ route('admin.orders.index') }}" style="font-size:13px; font-weight:600; text-decoration:none;">View All Orders &rarr;</a>
        </div>
        <div class="panel-body" style="padding:0;">
            <div class="table-responsive">
                <table class="table table-hover" style="margin-bottom:0;">
                    <thead style="background:#f8fafc; font-size:12px; color:#64748b; text-transform:uppercase;">
                        <tr>
                            <th style="padding:12px 20px;">Order Code</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Amount</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th style="text-align:right; padding-right:20px;">Action</th>
                        </tr>
                    </thead>
                    <tbody style="font-size:13.5px; color:#334155;">
                        @forelse($recentOrders as $order)
                            <tr>
                                <td style="padding:14px 20px; font-weight:700; color:#0f172a;">
                                    <a href="{{ route('admin.orders.show', $order->order_code) }}" style="color:#2563eb; text-decoration:none;">
                                        #{{ $order->order_code }}
                                    </a>
                                </td>
                                <td>
                                    <strong>{{ $order->contact_name ?: ($order->customer?->name ?? 'Guest') }}</strong>
                                    <div style="font-size:11.5px; color:#64748b;">{{ $order->contact_mobile }}</div>
                                </td>
                                <td>{{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}</td>
                                <td style="font-weight:700; color:#0f172a;">{{ $currency }}{{ number_format($order->total, 0) }}</td>
                                <td>
                                    <span class="label {{ $order->payment_status === 'paid' ? 'label-success' : 'label-warning' }}" style="border-radius:4px; font-size:11px; padding:3px 6px;">
                                        {{ strtoupper($order->payment_method ?? 'COD') }} : {{ ucfirst($order->payment_status) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $badgeClass = match($order->status) {
                                            'delivered' => 'label-success',
                                            'shipped', 'dispatched' => 'label-info',
                                            'cancelled', 'returned' => 'label-danger',
                                            'processing', 'packed', 'confirmed' => 'label-primary',
                                            default => 'label-default',
                                        };
                                    @endphp
                                    <span class="label {{ $badgeClass }}" style="border-radius:4px; font-size:11px; padding:3px 8px;">
                                        {{ strtoupper(str_replace('_', ' ', $order->status)) }}
                                    </span>
                                </td>
                                <td style="font-size:12px; color:#64748b;">{{ $order->created_at ? $order->created_at->format('d M, h:i A') : '—' }}</td>
                                <td style="text-align:right; padding-right:20px;">
                                    <a href="{{ route('admin.orders.show', $order->order_code) }}" class="btn btn-xs btn-default" style="border-radius:4px; font-weight:600;">
                                        Manage
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" style="text-align:center; padding:32px; color:#94a3b8;">
                                    <i class="fas fa-shopping-bag" style="font-size:32px; margin-bottom:8px; color:#cbd5e1;"></i>
                                    <div>No orders received yet.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

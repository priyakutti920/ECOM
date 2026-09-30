@extends('layouts.shop')

@section('title', 'My Orders — ' . $storeName)

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<div class="orders-wrap">
    {{-- ── Breadcrumbs ── --}}
    <nav class="orders-breadcrumbs" aria-label="Breadcrumb">
        <a href="{{ url('/') }}" class="orders-bc-link"><i class="las la-home"></i> Home</a>
        <span class="orders-bc-sep"><i class="las la-angle-right"></i></span>
        <a href="{{ url('/account') }}" class="orders-bc-link">My Account</a>
        <span class="orders-bc-sep"><i class="las la-angle-right"></i></span>
        <span class="orders-bc-current">My Orders</span>
    </nav>

    <div class="orders-header">
        <div>
            <h1><i class="las la-box" style="color: var(--color-primary);"></i> My Orders</h1>
            <p class="orders-sub">Review your purchase history and live order tracking</p>
        </div>
        <span class="orders-count-badge">{{ $orders->total() }} {{ Str::plural('order', $orders->total()) }}</span>
    </div>

    @if($orders->isEmpty())
        <div class="orders-empty">
            <div class="orders-empty-icon"><i class="las la-shopping-bag"></i></div>
            <h2>No orders yet</h2>
            <p>You haven't placed any orders with us yet. Start exploring our collections!</p>
            <a href="{{ url('/shop') }}" class="orders-btn-primary"><i class="las la-arrow-right"></i> Start Shopping</a>
        </div>
    @else
        <div class="orders-list">
            @foreach($orders as $o)
                <div class="order-card">
                    <div class="order-main">
                        <div class="order-head">
                            <span class="order-code">#{{ $o->order_code }}</span>
                            <span class="status-badge status-{{ $o->status }}">{{ ucfirst(str_replace('_', ' ', $o->status)) }}</span>
                            <span class="status-badge status-{{ $o->payment_status }}">Payment: {{ ucfirst($o->payment_status) }}</span>
                            <span class="meta-item"><i class="las la-calendar"></i> {{ $o->created_at->format('d M Y, h:i A') }}</span>
                            <span class="meta-item"><i class="las la-box-open"></i> <strong>{{ $o->items->count() }}</strong> {{ Str::plural('item', $o->items->count()) }}</span>
                        </div>
                        <div class="order-items-preview">
                            @foreach($o->items->take(5) as $it)
                                <div class="thumb" title="{{ $it->product_name }}">
                                    @if($it->product_image)
                                        <img src="{{ $it->product_image }}" alt="{{ $it->product_name }}" loading="lazy">
                                    @else
                                        <i class="las la-image"></i>
                                    @endif
                                </div>
                            @endforeach
                            @if($o->items->count() > 5)
                                <div class="more">+{{ $o->items->count() - 5 }}</div>
                            @endif
                        </div>
                    </div>
                    <div class="order-right">
                        <div class="total-label">Total Amount</div>
                        <div class="total-val">{{ \App\Models\StoreSetting::getCurrencySymbol() }}{{ number_format($o->grand_total, 2) }}</div>
                        <a href="{{ route('shop.orders.show', $o->id) }}" class="view-btn">
                            View Order <i class="las la-arrow-right"></i>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pagination-wrap">
            {{ $orders->withQueryString()->links('pagination::simple-default') }}
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.orders-wrap {
    max-width: 1200px;
    margin: 0 auto;
    padding: 24px 20px 60px;
}

/* Breadcrumbs */
.orders-breadcrumbs {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--color-muted, #64748b);
    margin-bottom: 20px;
}
.orders-bc-link {
    color: var(--color-muted, #64748b);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.orders-bc-link:hover { color: var(--color-primary, #0068e1); }
.orders-bc-sep { font-size: 11px; color: #cbd5e1; }
.orders-bc-current { color: var(--color-heading, #0f172a); font-weight: 600; }

.orders-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 24px;
    flex-wrap: wrap;
    gap: 12px;
}
.orders-header h1 {
    margin: 0 0 4px;
    font-size: 24px;
    font-weight: 700;
    color: var(--color-heading, #0f172a);
    display: flex;
    align-items: center;
    gap: 8px;
}
.orders-sub {
    font-size: 13.5px;
    color: var(--color-muted, #64748b);
    margin: 0;
}
.orders-count-badge {
    padding: 4px 12px;
    background: #ffffff;
    border: 1px solid var(--color-border, #e2e8f0);
    border-radius: 100px;
    font-size: 12.5px;
    font-weight: 600;
    color: var(--color-primary, #0068e1);
}

.orders-list { display: flex; flex-direction: column; gap: 16px; }
.order-card {
    background: #ffffff;
    border: 1px solid var(--color-border, #e2e8f0);
    border-radius: var(--radius-lg, 12px);
    padding: 20px 24px;
    transition: all 0.2s ease;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 20px;
    align-items: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.order-card:hover {
    box-shadow: 0 6px 16px rgba(0, 104, 225, 0.08);
    border-color: #cbd5e1;
    transform: translateY(-1px);
}

.order-head {
    display: flex;
    flex-wrap: wrap;
    gap: 12px 18px;
    align-items: center;
    margin-bottom: 12px;
}
.order-code {
    font-size: 16px;
    font-weight: 800;
    color: var(--color-heading, #0f172a);
    letter-spacing: 0.5px;
}
.meta-item {
    font-size: 12.5px;
    color: var(--color-muted, #64748b);
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.meta-item strong { color: var(--color-heading, #0f172a); }

.order-items-preview { display: flex; gap: 8px; flex-wrap: wrap; }
.order-items-preview .thumb {
    width: 54px;
    height: 54px;
    border-radius: var(--radius-md, 8px);
    background: #f8fafc;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    border: 1px solid var(--color-border, #e2e8f0);
}
.order-items-preview .thumb img { width: 100%; height: 100%; object-fit: cover; }
.order-items-preview .more {
    width: 54px;
    height: 54px;
    border-radius: var(--radius-md, 8px);
    background: #f1f5f9;
    border: 1px solid var(--color-border, #e2e8f0);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    color: var(--color-muted, #64748b);
    font-weight: 700;
}

.order-right { text-align: right; }
.total-label { font-size: 11.5px; color: var(--color-muted, #64748b); text-transform: uppercase; font-weight: 600; margin-bottom: 2px; }
.total-val { font-size: 20px; font-weight: 800; color: var(--color-heading, #0f172a); }
.view-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 8px;
    padding: 8px 18px;
    font-size: 13px;
    font-weight: 600;
    background: var(--color-primary, #0068e1);
    border: none;
    border-radius: var(--radius-md, 8px);
    color: #ffffff;
    text-decoration: none;
    box-shadow: 0 2px 6px rgba(0, 104, 225, 0.25);
    transition: all 0.15s ease;
}
.view-btn:hover {
    background: var(--color-primary-hover, #0051b3);
    color: #ffffff;
    transform: translateY(-1px);
}

.status-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 100px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.status-placed, .status-pending   { background: #fef9c3; color: #854d0e; }
.status-confirmed, .status-packed { background: #e0f2fe; color: #0369a1; }
.status-shipped                   { background: #ede9fe; color: #5b21b6; }
.status-delivered, .status-paid   { background: #dcfce7; color: #15803d; }
.status-cancelled, .status-failed { background: #fee2e2; color: #991b1b; }
.status-refunded                  { background: #f1f5f9; color: #475569; }

.orders-empty {
    text-align: center;
    padding: 60px 20px;
    background: #ffffff;
    border: 1px solid var(--color-border, #e2e8f0);
    border-radius: var(--radius-lg, 12px);
}
.orders-empty-icon {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: #f1f5f9;
    color: #94a3b8;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 34px;
    margin-bottom: 16px;
}
.orders-empty h2 { font-size: 20px; color: var(--color-heading, #0f172a); margin: 0 0 6px; font-weight: 700; }
.orders-empty p { color: var(--color-muted, #64748b); font-size: 14px; margin: 0 0 20px; }
.orders-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: var(--color-primary, #0068e1);
    color: #ffffff;
    padding: 10px 24px;
    border-radius: var(--radius-md, 8px);
    font-weight: 600;
    font-size: 13.5px;
    text-decoration: none;
    box-shadow: 0 2px 6px rgba(0, 104, 225, 0.25);
    transition: all 0.15s ease;
}
.orders-btn-primary:hover {
    background: var(--color-primary-hover, #0051b3);
    color: #ffffff;
    transform: translateY(-1px);
}

.pagination-wrap { margin-top: 24px; display: flex; justify-content: center; }

@media (max-width: 768px) {
    .order-card { grid-template-columns: 1fr; }
    .order-right { text-align: left; }
}
</style>
@endpush

@extends('layouts.admin')

@section('title', 'Coupons')

@push('styles')
<style>
.coupon-table { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #e7e7e7; border-radius: 8px; overflow: hidden; }
.coupon-table th { background: #f6f8fa; text-align: left; font-size: 12px; color: #555; padding: 10px 12px; border-bottom: 1px solid #e7e7e7; }
.coupon-table td { padding: 10px 12px; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
.coupon-table tr:hover td { background: #fafbfc; }
.code-pill { background: #f0f7ff; color: #0a4b87; border: 1px solid #cce0ff; padding: 2px 8px; border-radius: 100px; font-size: 11.5px; font-weight: 700; letter-spacing: 0.5px; }
.tag { display: inline-block; padding: 2px 8px; border-radius: 100px; font-size: 11px; font-weight: 600; }
.tag.percent { background: #e8f5e9; color: #1a7a42; }
.tag.flat   { background: #fff7e6; color: #946a00; }
.tag.used   { background: #f0f0f0; color: #777; }
.tag.active { background: #e3f2fd; color: #0a4b87; }
.tag.expired{ background: #fdecea; color: #a93226; }
.toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; gap: 10px; flex-wrap: wrap; }
.toolbar form { display: flex; gap: 6px; }
.toolbar input[type=text] { padding: 7px 10px; border: 1px solid #d5d9d9; border-radius: 6px; font-size: 13px; min-width: 240px; }
.toolbar button { padding: 7px 14px; background: #3a7bd5; color: #fff; border: none; border-radius: 6px; font-size: 13px; cursor: pointer; }
.empty { padding: 30px; text-align: center; color: #999; }
</style>
@endpush

@section('content')
<div style="max-width: 1100px; margin: 0 auto;">

    <div class="toolbar">
        <h2 style="margin:0; font-size: 20px; color:#1a1a2e;">
            <i class="fas fa-ticket-alt" style="color:#3a7bd5;"></i> Coupons
        </h2>
        <form method="GET" action="{{ route('admin.coupons.index') }}">
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search by code, customer, email…">
            <button><i class="fas fa-search"></i> Search</button>
        </form>
    </div>

    <table class="coupon-table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Customer</th>
                <th>Type</th>
                <th>Value</th>
                <th>Min ₹</th>
                <th>Status</th>
                <th>Issued</th>
                <th>Used on</th>
            </tr>
        </thead>
        <tbody>
        @forelse($coupons as $c)
            <tr>
                <td><span class="code-pill">{{ $c->code }}</span></td>
                <td>
                    @if($c->customer)
                        {{ $c->customer->name ?? '—' }}<br>
                        <small style="color:#888;">{{ $c->customer->email }}</small>
                    @else
                        <span style="color:#888;">—</span>
                    @endif
                </td>
                <td>
                    <span class="tag {{ $c->type }}">{{ strtoupper($c->type) }}</span>
                </td>
                <td>
                    @if($c->type === 'percent')
                        {{ rtrim(rtrim($c->value, '0'), '.') }}%
                    @else
                        ₹{{ number_format($c->value, 0) }}
                    @endif
                    @if($c->bonus)
                        <br><small style="color:#888;">from {{ $c->bonus->name }}</small>
                    @endif
                </td>
                <td>{{ $c->min_amount > 0 ? '₹' . number_format($c->min_amount, 0) : '—' }}</td>
                <td>
                    @if($c->used_at)
                        <span class="tag used">Used</span>
                    @elseif($c->expires_at && $c->expires_at->isPast())
                        <span class="tag expired">Expired</span>
                    @elseif(!$c->is_active)
                        <span class="tag used">Inactive</span>
                    @else
                        <span class="tag active">Active</span>
                    @endif
                </td>
                <td>
                    @if($c->issued_at)
                        <small>{{ $c->issued_at->format('d M Y') }}</small>
                    @else
                        <small style="color:#888;">—</small>
                    @endif
                </td>
                <td>
                    @if($c->order)
                        <a href="#" style="color:#3a7bd5; text-decoration: none;">{{ $c->order->order_code }}</a>
                    @else
                        <span style="color:#888;">—</span>
                    @endif
                </td>
            </tr>
        @empty
            <tr><td colspan="8" class="empty">No coupons issued yet.</td></tr>
        @endforelse
        </tbody>
    </table>

    <div style="margin-top:14px;">{{ $coupons->links() }}</div>

</div>
@endsection

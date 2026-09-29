@extends('layouts.admin')

@section('title', 'Users')

@push('styles')
<style>
.kpi-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 18px; }
.kpi { background: #fff; border: 1px solid #e7e7e7; border-radius: 8px; padding: 14px 18px; display: flex; align-items: center; gap: 12px; }
.kpi .icon { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; }
.kpi.c1 .icon { background: #e3f2fd; color: #0a4b6e; }
.kpi.c2 .icon { background: #fff7e6; color: #946a00; }
.kpi.c3 .icon { background: #e8f5e9; color: #1a7a42; }
.kpi .num { font-size: 20px; font-weight: 700; color: #1a1a2e; }
.kpi .lbl { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 0.4px; }

.toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; gap: 10px; flex-wrap: wrap; }
.toolbar form { display: flex; gap: 6px; }
.toolbar input[type=text] { padding: 7px 10px; border: 1px solid #d5d9d9; border-radius: 6px; font-size: 13px; min-width: 280px; }
.toolbar button { padding: 7px 14px; background: #3a7bd5; color: #fff; border: none; border-radius: 6px; font-size: 13px; cursor: pointer; }

table.data { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #e7e7e7; border-radius: 8px; overflow: hidden; }
table.data th { background: #f6f8fa; text-align: left; font-size: 12px; color: #555; padding: 10px 12px; border-bottom: 1px solid #e7e7e7; text-transform: uppercase; letter-spacing: 0.4px; }
table.data td { padding: 10px 12px; border-bottom: 1px solid #f0f0f0; font-size: 13px; vertical-align: top; }
table.data tr:hover td { background: #fafbfc; }
.u-avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #c1d8f0 0%, #3a7bd5 100%); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:14px; flex-shrink:0; }
.u-name { font-weight: 600; color: #222; }
.u-meta { color: #888; font-size: 11.5px; margin-top: 1px; }
.empty { padding: 30px; text-align: center; color: #999; }
</style>
@endpush

@section('content')
<div style="max-width: 1280px; margin: 0 auto;">

    <h2 style="margin: 0 0 14px; font-size: 22px; color: #1a1a2e;">
        <i class="fas fa-users" style="color:#3a7bd5;"></i> Customers
        <span style="font-size:13px; color:#888; font-weight:500;">({{ $users->total() }})</span>
    </h2>

    <div class="kpi-row">
        <div class="kpi c1">
            <div class="icon"><i class="fas fa-user-friends"></i></div>
            <div><div class="num">{{ $users->total() }}</div><div class="lbl">Total customers</div></div>
        </div>
        <div class="kpi c2">
            <div class="icon"><i class="fas fa-shopping-bag"></i></div>
            <div>{{-- aggregate from collection --}}
                @php $sumOrders = $users->sum('orders_count'); @endphp
                <div><div class="num">{{ number_format($sumOrders) }}</div><div class="lbl">Total orders (page)</div></div>
            </div>
        </div>
        <div class="kpi c3">
            <div class="icon"><i class="fas fa-rupee-sign"></i></div>
            <div>@php $sumSpent = $users->sum('total_spent'); @endphp
                <div><div class="num">₹{{ number_format($sumSpent, 0) }}</div><div class="lbl">Total revenue (page)</div></div>
            </div>
        </div>
    </div>

    <div class="toolbar">
        <form method="GET" action="{{ route('admin.users.index') }}">
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search by name, email, mobile…">
            <button><i class="fas fa-search"></i> Search</button>
        </form>
    </div>

    <table class="data">
        <thead>
            <tr>
                <th style="width:50px;"></th>
                <th>Name</th>
                <th>Contact</th>
                <th style="width:100px;">Orders</th>
                <th style="width:120px;">Paid orders</th>
                <th style="width:120px;">Total spent</th>
                <th>Joined</th>
            </tr>
        </thead>
        <tbody>
        @forelse($users as $u)
            @php
                $initials = strtoupper(mb_substr($u->name ?? ($u->email ?? 'U'), 0, 1));
            @endphp
            <tr>
                <td>
                    <div class="u-avatar">{{ $initials }}</div>
                </td>
                <td>
                    <div class="u-name">{{ $u->name ?? '—' }}</div>
                    <div class="u-meta">#{{ $u->id }}</div>
                </td>
                <td>
                    <div>{{ $u->email ?? '—' }}</div>
                    <div class="u-meta">{{ $u->mobile ?? '—' }}</div>
                </td>
                <td>{{ $u->orders_count ?? 0 }}</td>
                <td>{{ $u->paid_orders_count ?? 0 }}</td>
                <td>₹{{ number_format($u->total_spent ?? 0, 0) }}</td>
                <td><small>{{ $u->created_at?->format('d M Y') ?? '—' }}</small></td>
            </tr>
        @empty
            <tr><td colspan="7" class="empty">No users found.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div style="margin-top:10px;">{{ $users->appends(request()->query())->links() }}</div>

</div>
@endsection

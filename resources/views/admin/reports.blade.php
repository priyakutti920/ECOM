@extends('layouts.admin')

@section('title', 'Sales & Reports')

@push('styles')
<style>
    .rep-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .rep-filter-btns a {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        color: #555;
        border: 1px solid #ddd;
        background: #fff;
        margin-left: 4px;
    }
    .rep-filter-btns a.active {
        background: #3a7bd5;
        color: #fff;
        border-color: #3a7bd5;
    }
    .rep-metrics {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .rep-card {
        background: #fff;
        border: 1px solid #e7e7e7;
        border-radius: 8px;
        padding: 18px 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .rep-label { font-size: 11px; text-transform: uppercase; font-weight: 700; color: #888; }
    .rep-val { font-size: 24px; font-weight: 700; color: #222; margin-top: 4px; }
    .table-card {
        background: #fff;
        border: 1px solid #e7e7e7;
        border-radius: 8px;
        overflow: hidden;
        margin-bottom: 24px;
    }
    .table-card-head {
        padding: 14px 18px;
        border-bottom: 1px solid #eee;
        font-size: 14px;
        font-weight: 700;
        color: #333;
    }
    .rep-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .rep-table th, .rep-table td { padding: 12px 16px; text-align: left; }
    .rep-table thead th { background: #fafafa; border-bottom: 1px solid #eee; font-size: 11px; font-weight: 600; text-transform: uppercase; color: #666; }
    .rep-table tbody tr { border-top: 1px solid #f2f2f2; }
</style>
@endpush

@section('content')
<div class="container-fluid" style="padding: 24px 20px;">

    <div class="rep-header">
        <div>
            <h2 style="margin:0; font-weight:700; font-size:22px; color:#222;">
                <i class="fas fa-chart-bar" style="color:#3a7bd5; margin-right:8px;"></i> Sales &amp; Performance Reports
            </h2>
            <div style="font-size:13px; color:#777; margin-top:3px;">
                Comprehensive revenue, fulfillment, and product sales statistics.
            </div>
        </div>
        <div class="rep-filter-btns">
            <a href="?period=today" class="{{ $period === 'today' ? 'active' : '' }}">Today</a>
            <a href="?period=week"  class="{{ $period === 'week' ? 'active' : '' }}">This Week</a>
            <a href="?period=month" class="{{ $period === 'month' ? 'active' : '' }}">This Month</a>
            <a href="?period=year"  class="{{ $period === 'year' ? 'active' : '' }}">This Year</a>
            <a href="?period=all"   class="{{ $period === 'all' ? 'active' : '' }}">All Time</a>
        </div>
    </div>

    <div class="rep-metrics">
        <div class="rep-card">
            <div class="rep-label">Gross Revenue</div>
            <div class="rep-val" style="color:#2e7d32;">₹{{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="rep-card">
            <div class="rep-label">Total Orders</div>
            <div class="rep-val" style="color:#3a7bd5;">{{ number_format($totalOrders) }}</div>
        </div>
        <div class="rep-card">
            <div class="rep-label">Avg Order Value</div>
            <div class="rep-val" style="color:#7b1fa2;">₹{{ number_format($avgOrderValue, 2) }}</div>
        </div>
        <div class="rep-card">
            <div class="rep-label">Delivered Orders</div>
            <div class="rep-val" style="color:#f57f17;">{{ number_format($deliveredOrders) }}</div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="table-card">
                <div class="table-card-head">
                    <i class="fas fa-medal" style="color:#f57f17; margin-right:6px;"></i> Top Selling Products
                </div>
                @if($topItems->isEmpty())
                    <div style="padding: 30px; text-align: center; color: #999;">No product sales recorded in this period.</div>
                @else
                    <table class="rep-table">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th style="width:100px;">Units Sold</th>
                                <th style="width:120px;">Total Revenue</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topItems as $item)
                                <tr>
                                    <td style="font-weight:600; color:#333;">{{ $item->product_name }}</td>
                                    <td>{{ number_format($item->total_qty) }}</td>
                                    <td style="font-weight:700; color:#2e7d32;">₹{{ number_format($item->total_amount, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
        </div>

        <div class="col-md-5">
            <div class="table-card">
                <div class="table-card-head">
                    <i class="fas fa-pie-chart" style="color:#3a7bd5; margin-right:6px;"></i> Orders by Status
                </div>
                <table class="rep-table">
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th style="width:100px; text-align:right;">Count</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(['placed','accepted','dispatched','delivered','cancelled','refunded'] as $st)
                            <tr>
                                <td>
                                    <span style="display:inline-block; width:8px; height:8px; border-radius:50%; background:#888; margin-right:6px;"></span>
                                    {{ ucfirst($st) }}
                                </td>
                                <td style="text-align:right; font-weight:600;">
                                    {{ $ordersByStatus[$st] ?? 0 }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection

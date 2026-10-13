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
        width: 100%;
        box-sizing: border-box;
    }
    .rep-header h2 {
        margin: 0;
        font-weight: 700;
        font-size: 22px;
        color: #222;
        word-break: break-word;
    }
    .rep-filter-btns {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
        align-items: center;
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
        margin-left: 0;
        white-space: nowrap;
        flex-shrink: 0;
        transition: background 0.15s, color 0.15s;
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
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
    .rep-card {
        background: #fff;
        border: 1px solid #e7e7e7;
        border-radius: 8px;
        padding: 18px 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        min-width: 0;
        overflow: hidden;
        box-sizing: border-box;
    }
    .rep-label {
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 700;
        color: #888;
        white-space: normal;
        word-break: break-word;
        letter-spacing: 0.5px;
    }
    .rep-val {
        font-size: 24px;
        font-weight: 700;
        color: #222;
        margin-top: 4px;
        word-break: break-word;
        overflow-wrap: anywhere;
        line-height: 1.25;
    }
    .table-card {
        background: #fff;
        border: 1px solid #e7e7e7;
        border-radius: 8px;
        overflow: visible;
        margin-bottom: 24px;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
    .table-card-head {
        padding: 14px 18px;
        border-bottom: 1px solid #eee;
        font-size: 14px;
        font-weight: 700;
        color: #333;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .rep-table { width: 100%; min-width: 440px; border-collapse: collapse; font-size: 13px; margin: 0; }
    .rep-table th, .rep-table td { padding: 12px 16px; text-align: left; }
    .rep-table thead th { background: #fafafa; border-bottom: 1px solid #eee; font-size: 11px; font-weight: 600; text-transform: uppercase; color: #666; }
    .rep-table tbody tr { border-top: 1px solid #f2f2f2; }

    .col-prod-name { min-width: 180px; word-break: break-word; overflow-wrap: anywhere; font-weight: 600; color: #333; }
    .col-prod-qty  { min-width: 90px; white-space: nowrap; }
    .col-prod-rev  { min-width: 120px; white-space: nowrap; font-weight: 700; color: #2e7d32; }

    .table-responsive {
        width: 100% !important;
        max-width: 100% !important;
        overflow-x: auto !important;
        overflow-y: visible !important;
        -webkit-overflow-scrolling: touch !important;
        touch-action: pan-x pan-y !important;
        overscroll-behavior-x: contain;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e1 #f8fafc;
    }
    .table-responsive::-webkit-scrollbar {
        height: 6px;
    }
    .table-responsive::-webkit-scrollbar-track {
        background: #f8fafc;
        border-radius: 4px;
    }
    .table-responsive::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .mobile-scroll-hint {
        display: none;
        font-size: 11.5px;
        color: #64748b;
        padding: 8px 12px;
        background: #f8fafc;
        border-bottom: 1px solid #eee;
        align-items: center;
        gap: 6px;
    }
    @media (max-width: 768px) {
        .mobile-scroll-hint { display: flex; }
    }
    .rep-table th, .rep-table td {
        touch-action: pan-x pan-y !important;
    }

    @media (max-width: 768px) {
        .rep-header {
            flex-direction: column;
            align-items: stretch;
            gap: 12px;
        }
        .container-fluid > .row {
            margin-left: 0;
            margin-right: 0;
        }
        .container-fluid > .row > [class*="col-"] {
            padding-left: 0;
            padding-right: 0;
        }
    }
    @media (max-width: 600px) {
        .rep-filter-btns {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            touch-action: pan-x pan-y;
            overscroll-behavior-x: contain;
            flex-wrap: nowrap;
            padding-bottom: 6px;
            scrollbar-width: thin;
        }
    }
    @media (max-width: 480px) {
        .rep-metrics { grid-template-columns: 1fr; gap: 10px; }
        .rep-card { padding: 14px 16px; }
        .rep-val { font-size: 20px; }
    }
    @media (max-width: 360px) {
        .rep-val { font-size: 18px; }
        .rep-table { font-size: 12px; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid" style="padding: 0;">

    <div class="rep-header">
        <div>
            <h2>
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
                    <span><i class="fas fa-medal" style="color:#f57f17; margin-right:6px;"></i> Top Selling Products</span>
                </div>
                @if($topItems->isEmpty())
                    <div style="padding: 30px; text-align: center; color: #999;">No product sales recorded in this period.</div>
                @else
                    <div class="table-responsive" style="margin-bottom:0; border:none; border-radius:0;">
                        <div class="mobile-scroll-hint">
                            <i class="fas fa-arrows-alt-h"></i> Scroll right to view units and revenue &rarr;
                        </div>
                        <table class="rep-table">
                            <thead>
                                <tr>
                                    <th class="col-prod-name">Product</th>
                                    <th class="col-prod-qty" style="width:100px;">Units Sold</th>
                                    <th class="col-prod-rev" style="width:120px;">Total Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topItems as $item)
                                    <tr>
                                        <td class="col-prod-name">{{ $item->product_name }}</td>
                                        <td class="col-prod-qty">{{ number_format($item->total_qty) }}</td>
                                        <td class="col-prod-rev">₹{{ number_format($item->total_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="col-md-5">
            <div class="table-card">
                <div class="table-card-head">
                    <i class="fas fa-pie-chart" style="color:#3a7bd5; margin-right:6px;"></i> Orders by Status
                </div>
                <div class="table-responsive" style="margin-bottom:0; border:none; border-radius:0;">
                    <table class="rep-table" style="min-width:280px;">
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

</div>
@endsection

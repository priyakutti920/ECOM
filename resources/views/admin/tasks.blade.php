@extends('layouts.admin')

@section('title', 'Operational Tasks')

@push('styles')
<style>
    .task-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .task-card {
        background: #fff;
        border: 1px solid #e7e7e7;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .task-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }
    .task-icon {
        width: 44px;
        height: 44px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }
    .task-icon.amber { background: #fff8e1; color: #f57f17; }
    .task-icon.red   { background: #ffebee; color: #c62828; }
    .task-icon.blue  { background: #e3f2fd; color: #1565c0; }
    .task-icon.green { background: #e8f5e9; color: #2e7d32; }
    .task-count {
        font-size: 26px;
        font-weight: 700;
        color: #222;
    }
    .task-title {
        font-size: 14px;
        font-weight: 600;
        color: #333;
        margin-bottom: 6px;
    }
    .task-desc {
        font-size: 12px;
        color: #777;
        margin-bottom: 14px;
    }
    .task-btn {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 4px;
        background: #f0f4fa;
        color: #3a7bd5;
        font-weight: 600;
        font-size: 12px;
        text-decoration: none;
        transition: background .15s;
    }
    .task-btn:hover { background: #e3ecf8; color: #2f6bc4; }
</style>
@endpush

@section('content')
<div class="container-fluid" style="padding: 24px 20px;">

    <div style="margin-bottom: 24px;">
        <h2 style="margin:0; font-weight:700; font-size:22px; color:#222;">
            <i class="fas fa-tasks" style="color:#3a7bd5; margin-right:8px;"></i> Actionable Tasks
        </h2>
        <p style="margin:4px 0 0; color:#666; font-size:13px;">
            Overview of pending items requiring staff or admin attention.
        </p>
    </div>

    <div class="task-grid">
        <div class="task-card">
            <div class="task-card-head">
                <div class="task-icon amber">
                    <i class="fas fa-box"></i>
                </div>
                <div class="task-count">{{ $pendingOrders->count() }}</div>
            </div>
            <div class="task-title">Orders Awaiting Dispatch</div>
            <div class="task-desc">Placed or accepted orders ready to be packed and fulfilled.</div>
            <a href="{{ route('admin.orders.index') }}?status=placed" class="task-btn">
                View Orders &rarr;
            </a>
        </div>

        <div class="task-card">
            <div class="task-card-head">
                <div class="task-icon red">
                    <i class="fas fa-undo"></i>
                </div>
                <div class="task-count">{{ $openReturns->count() }}</div>
            </div>
            <div class="task-title">Return Requests</div>
            <div class="task-desc">Customer return and exchange requests awaiting review.</div>
            <a href="{{ route('admin.cancellations-returns.index') }}?tab=returns" class="task-btn">
                Review Returns &rarr;
            </a>
        </div>

        <div class="task-card">
            <div class="task-card-head">
                <div class="task-icon blue">
                    <i class="fas fa-headset"></i>
                </div>
                <div class="task-count">{{ $openTickets->count() }}</div>
            </div>
            <div class="task-title">Open Support Tickets</div>
            <div class="task-desc">Customer inquiries and help tickets awaiting response.</div>
            <a href="{{ route('admin.support.index') }}?status=open" class="task-btn">
                Respond to Tickets &rarr;
            </a>
        </div>

        <div class="task-card">
            <div class="task-card-head">
                <div class="task-icon green">
                    <i class="fas fa-credit-card"></i>
                </div>
                <div class="task-count">{{ $unpaidOrders->count() }}</div>
            </div>
            <div class="task-title">Pending Payment Orders</div>
            <div class="task-desc">Unpaid orders requiring verification or payment confirmation.</div>
            <a href="{{ route('admin.payments.index') }}?payment_status=pending" class="task-btn">
                Check Payments &rarr;
            </a>
        </div>
    </div>

</div>
@endsection

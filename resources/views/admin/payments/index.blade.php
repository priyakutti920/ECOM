@extends('layouts.admin')

@section('title', 'Payments & Transactions')

@php
    $startNo = ($payments->currentPage() - 1) * $payments->perPage();

    $payBadge = [
        'paid'     => 'background:#e8f5e9;color:#2e7d32;border:1px solid #c8e6c9;',
        'failed'   => 'background:#ffebee;color:#c62828;border:1px solid #ffcdd2;',
        'pending'  => 'background:#fff8e1;color:#f57f17;border:1px solid #ffe082;',
        'refunded' => 'background:#ede7f6;color:#512da8;border:1px solid #d1c4e9;',
    ];
@endphp

@push('styles')
<style>
    .pay-page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
        gap: 12px;
    }
    .pay-page-header h2 {
        margin: 0;
        font-weight: 700;
        font-size: 22px;
        color: #222;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .pay-page-header .subtitle {
        font-size: 13px;
        color: #666;
        font-weight: 400;
        margin-top: 4px;
    }
    .pay-actions-top {
        display: flex;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
    }

    /* ── Metric Cards ── */
    .metric-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .metric-card {
        background: #fff;
        border: 1px solid #e7e7e7;
        border-radius: 8px;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        gap: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        transition: transform .15s, box-shadow .15s;
    }
    .metric-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }
    .metric-icon {
        width: 48px;
        height: 48px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        flex-shrink: 0;
    }
    .metric-icon.green  { background: #e8f5e9; color: #2e7d32; }
    .metric-icon.blue   { background: #e3f2fd; color: #1565c0; }
    .metric-icon.amber  { background: #fff8e1; color: #f57f17; }
    .metric-icon.purple { background: #ede7f6; color: #6a1b9a; }
    .metric-info { flex: 1; min-width: 0; }
    .metric-label { font-size: 12px; color: #777; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; }
    .metric-value { font-size: 22px; font-weight: 700; color: #222; margin-top: 2px; }
    .metric-sub { font-size: 11px; color: #888; margin-top: 3px; }

    /* ── Filter Card ── */
    .filter-card {
        background: #fff;
        border: 1px solid #e7e7e7;
        border-radius: 8px;
        padding: 16px 20px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .pay-filters {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
    }
    .pay-filters input, .pay-filters select {
        padding: 8px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 13px;
        font-family: inherit;
        background: #fff;
    }
    .pay-filters input:focus, .pay-filters select:focus {
        outline: none;
        border-color: #3a7bd5;
        box-shadow: 0 0 0 2px rgba(58,123,213,0.15);
    }
    .pay-filters .search-input { min-width: 260px; flex: 1; }
    .pay-filters .btn {
        padding: 8px 16px;
        font-size: 13px;
        font-weight: 500;
        border-radius: 6px;
    }
    .pay-filters .btn-reset {
        color: #666;
        text-decoration: none;
        font-size: 13px;
        padding: 8px 12px;
    }
    .pay-filters .btn-reset:hover { color: #222; }

    /* ── Table Design ── */
    .table-container {
        background: #fff;
        border: 1px solid #e7e7e7;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .pay-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .pay-table th, .pay-table td {
        padding: 14px 16px;
        text-align: left;
        vertical-align: middle;
    }
    .pay-table thead th {
        background: #f8f9fa;
        border-bottom: 1px solid #e7e7e7;
        font-weight: 600;
        color: #555;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
    }
    .pay-table tbody tr {
        border-top: 1px solid #f0f0f0;
        transition: background .12s;
    }
    .pay-table tbody tr:hover { background: #fafbfc; }

    .ord-code {
        font-weight: 700;
        font-size: 13px;
        color: #3a7bd5;
        text-decoration: none;
    }
    .ord-code:hover { text-decoration: underline; }

    .cust-info .name { font-weight: 600; color: #222; }
    .cust-info .mobile { font-size: 12px; color: #666; }
    .cust-info .email { font-size: 11px; color: #888; }

    .amount-tag {
        font-size: 14px;
        font-weight: 700;
        color: #1a7a42;
    }
    .amount-refunded {
        font-size: 11px;
        color: #721c24;
        margin-top: 2px;
    }

    .status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    .gateway-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: #f4f5f7;
        border: 1px solid #e1e4e8;
        border-radius: 4px;
        padding: 2px 8px;
        font-size: 11px;
        font-weight: 600;
        color: #444;
        text-transform: uppercase;
    }

    .utr-box {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #f8f9fa;
        border: 1px dashed #ced4da;
        border-radius: 4px;
        padding: 3px 8px;
        font-family: monospace;
        font-size: 12px;
        color: #333;
    }
    .copy-btn {
        background: none;
        border: none;
        color: #777;
        cursor: pointer;
        padding: 0;
        font-size: 11px;
    }
    .copy-btn:hover { color: #3a7bd5; }

    .action-btns {
        display: flex;
        gap: 6px;
        align-items: center;
    }
    .btn-action-icon {
        width: 32px;
        height: 32px;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #ddd;
        background: #fff;
        color: #555;
        font-size: 12px;
        cursor: pointer;
        text-decoration: none;
        transition: all .15s;
    }
    .btn-action-icon:hover {
        background: #f0f4fa;
        color: #3a7bd5;
        border-color: #3a7bd5;
    }

    /* ── Empty State ── */
    .empty-state {
        padding: 50px 20px;
        text-align: center;
        color: #888;
    }
    .empty-state i {
        font-size: 44px;
        color: #d0d5dd;
        margin-bottom: 12px;
        display: block;
    }
    .empty-state h4 { font-size: 16px; font-weight: 600; color: #444; margin-bottom: 4px; }
    .empty-state p { font-size: 13px; color: #888; margin: 0; }

    /* ── Toast ── */
    #toast-wrap {
        position: fixed;
        bottom: 24px;
        right: 24px;
        z-index: 99999;
    }
    .toast-msg {
        background: #222;
        color: #fff;
        padding: 10px 16px;
        border-radius: 6px;
        font-size: 13px;
        box-shadow: 0 4px 14px rgba(0,0,0,0.2);
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 8px;
    }

    /* Modal Backdrop and Box */
    .pay-modal-backdrop {
        position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0,0,0,0.5); z-index: 1050; display: none;
    }
    .pay-modal-backdrop.show { display: block; }
    .pay-modal {
        position: fixed; top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        background: #fff; border-radius: 8px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        width: 440px; max-width: 92vw;
        z-index: 1051; display: none;
    }
    .pay-modal.show { display: block; }
    .pay-modal-head {
        padding: 14px 18px; border-bottom: 1px solid #eee;
        display: flex; justify-content: space-between; align-items: center;
    }
    .pay-modal-head h3 { margin: 0; font-size: 15px; font-weight: 700; color: #222; }
    .pay-modal-head .close-btn {
        background: none; border: none; font-size: 20px; color: #888; cursor: pointer;
    }
    .pay-modal-body { padding: 18px; }
    .pay-modal-foot {
        padding: 12px 18px; border-top: 1px solid #eee;
        display: flex; justify-content: flex-end; gap: 8px;
    }
    .pay-modal-body label {
        display: block; font-weight: 600; font-size: 12px; color: #444; margin-bottom: 5px;
    }
    .pay-modal-body input, .pay-modal-body select {
        width: 100%; padding: 8px 12px; border: 1px solid #ddd;
        border-radius: 5px; font-size: 13px; box-sizing: border-box; margin-bottom: 12px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid" style="padding: 20px 24px;">

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert" style="border-radius:6px;">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <i class="fas fa-check-circle" style="margin-right:6px;"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible" role="alert" style="border-radius:6px;">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i> {{ session('error') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="pay-page-header">
        <div>
            <h2>
                <i class="fas fa-credit-card" style="color:#3a7bd5;"></i>
                Payments &amp; Transactions
                <span style="font-size:14px; font-weight:500; color:#888;">({{ $payments->total() }})</span>
            </h2>
            <div class="subtitle">Monitor incoming payments, transaction statuses, settlements, and gateway settings.</div>
        </div>

        <div class="pay-actions-top">
            <a href="{{ route('admin.settings.payment') }}" class="btn btn-default" style="border-color:#ccc; font-weight:600;">
                <i class="fas fa-cog" style="margin-right:6px; color:#7b1fa2;"></i> Gateway Settings
            </a>
            <a href="{{ route('admin.payments.export', request()->query()) }}" class="btn btn-primary" style="background:#3a7bd5; border-color:#3a7bd5; font-weight:600;">
                <i class="fas fa-file-export" style="margin-right:6px;"></i> Export CSV
            </a>
        </div>
    </div>

    {{-- Metric Cards --}}
    <div class="metric-grid">
        <div class="metric-card">
            <div class="metric-icon green">
                <i class="fas fa-wallet"></i>
            </div>
            <div class="metric-info">
                <div class="metric-label">Total Collected</div>
                <div class="metric-value">₹{{ number_format($stats['total_collected'], 2) }}</div>
                <div class="metric-sub">Today: ₹{{ number_format($stats['today_collected'], 2) }}</div>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon blue">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="metric-info">
                <div class="metric-label">Successful Paid</div>
                <div class="metric-value">{{ number_format($stats['paid_count']) }}</div>
                <div class="metric-sub">Orders completed</div>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon amber">
                <i class="fas fa-clock"></i>
            </div>
            <div class="metric-info">
                <div class="metric-label">Pending / Unpaid</div>
                <div class="metric-value">{{ number_format($stats['pending_count']) }}</div>
                <div class="metric-sub">Awaiting confirmation</div>
            </div>
        </div>

        <div class="metric-card">
            <div class="metric-icon purple">
                <i class="fas fa-undo"></i>
            </div>
            <div class="metric-info">
                <div class="metric-label">Refunded</div>
                <div class="metric-value">₹{{ number_format($stats['refunded_amount'], 2) }}</div>
                <div class="metric-sub">{{ $stats['refunded_count'] }} transactions refunded</div>
            </div>
        </div>
    </div>

    {{-- Search & Filter Card --}}
    <div class="filter-card">
        <form class="pay-filters" method="get" action="{{ route('admin.payments.index') }}">
            <input type="text" name="q" class="search-input" value="{{ $filters['q'] ?? '' }}" placeholder="Search by Order ID, Customer, Mobile, UTR...">

            <select name="payment_status">
                <option value="">All Payment Statuses</option>
                <option value="paid"     @selected(($filters['payment_status'] ?? '') === 'paid')>Paid</option>
                <option value="pending"  @selected(($filters['payment_status'] ?? '') === 'pending')>Pending</option>
                <option value="failed"   @selected(($filters['payment_status'] ?? '') === 'failed')>Failed</option>
                <option value="refunded" @selected(($filters['payment_status'] ?? '') === 'refunded')>Refunded</option>
            </select>

            <select name="payment_method">
                <option value="">All Methods / Gateways</option>
                <option value="upi"    @selected(($filters['payment_method'] ?? '') === 'upi')>UPI</option>
                <option value="manual" @selected(($filters['payment_method'] ?? '') === 'manual')>Manual / COD</option>
                <option value="cash"   @selected(($filters['payment_method'] ?? '') === 'cash')>Cash</option>
                <option value="bank"   @selected(($filters['payment_method'] ?? '') === 'bank')>Bank Transfer</option>
            </select>

            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" title="From Date">
            <input type="date" name="date_to"   value="{{ $filters['date_to'] ?? '' }}"   title="To Date">

            <button type="submit" class="btn btn-primary" style="background:#3a7bd5; border-color:#3a7bd5;">
                <i class="fas fa-filter" style="margin-right:4px;"></i> Filter
            </button>
            <a href="{{ route('admin.payments.index') }}" class="btn-reset">
                <i class="fas fa-times" style="margin-right:3px;"></i> Reset
            </a>
        </form>
    </div>

    {{-- Transactions Table --}}
    <div class="table-container">
        @if($payments->isEmpty())
            <div class="empty-state">
                <i class="fas fa-receipt"></i>
                <h4>No payment transactions found</h4>
                <p>There are no transactions matching your current filter criteria.</p>
            </div>
        @else
            <div style="overflow-x:auto;">
                <table class="pay-table">
                    <thead>
                        <tr>
                            <th style="width:40px;">#</th>
                            <th style="width:130px;">Order Code</th>
                            <th>Customer</th>
                            <th style="width:120px;">Amount</th>
                            <th style="width:130px;">Method</th>
                            <th style="width:180px;">UTR / Reference</th>
                            <th style="width:110px;">Payment Status</th>
                            <th style="width:140px;">Date &amp; Time</th>
                            <th style="width:100px; text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $index => $payment)
                            <tr>
                                <td style="color:#888;">{{ $startNo + $index + 1 }}</td>
                                <td>
                                    <a href="{{ route('admin.orders.show', $payment->order_code) }}" class="ord-code">
                                        {{ $payment->order_code }}
                                    </a>
                                    @if($payment->payment_order_id)
                                        <div style="font-size:10px; color:#999; font-family:monospace;" title="Gateway Order ID">
                                            ID: {{ Str::limit($payment->payment_order_id, 16) }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="cust-info">
                                        <div class="name">{{ $payment->contact_name ?: ($payment->customer->name ?? 'Guest') }}</div>
                                        <div class="mobile"><i class="fas fa-phone-alt" style="font-size:10px; color:#aaa; margin-right:4px;"></i>{{ $payment->contact_mobile }}</div>
                                        @if($payment->contact_email)
                                            <div class="email">{{ $payment->contact_email }}</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="amount-tag">₹{{ number_format($payment->total, 2) }}</div>
                                    @if($payment->payment_status === 'refunded' && $payment->refunded_amount > 0)
                                        <div class="amount-refunded">Ref: ₹{{ number_format($payment->refunded_amount, 2) }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="gateway-pill">
                                        @if(in_array(strtolower($payment->payment_method ?? ''), ['upi', 'upicheckout']))
                                            <i class="fas fa-mobile-alt" style="color:#e65100;"></i> UPI
                                        @elseif(strtolower($payment->payment_method ?? '') === 'manual')
                                            <i class="fas fa-money-bill-wave" style="color:#2e7d32;"></i> Manual
                                        @elseif(strtolower($payment->payment_method ?? '') === 'bank')
                                            <i class="fas fa-university" style="color:#1565c0;"></i> Bank
                                        @else
                                            <i class="fas fa-credit-card" style="color:#666;"></i> {{ strtoupper($payment->payment_method ?? 'COD') }}
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    @if($payment->payment_utr)
                                        <div class="utr-box">
                                            <span>{{ $payment->payment_utr }}</span>
                                            <button type="button" class="copy-btn" onclick="copyUtr('{{ $payment->payment_utr }}')" title="Copy UTR">
                                                <i class="far fa-copy"></i>
                                            </button>
                                        </div>
                                    @elseif($payment->refund_reference)
                                        <div class="utr-box" style="border-color:#b39ddb; color:#512da8;" title="Refund Reference">
                                            <span>REF: {{ $payment->refund_reference }}</span>
                                        </div>
                                    @else
                                        <span style="color:#bbb;">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="status-badge" style="{{ $payBadge[$payment->payment_status] ?? $payBadge['pending'] }}">
                                        {{ $payment->payment_status ?? 'pending' }}
                                    </span>
                                </td>
                                <td>
                                    <div style="font-weight:500; color:#333;">
                                        {{ ($payment->paid_at ?? $payment->created_at)->format('d M Y') }}
                                    </div>
                                    <div style="font-size:11px; color:#888;">
                                        {{ ($payment->paid_at ?? $payment->created_at)->format('h:i A') }}
                                    </div>
                                </td>
                                <td>
                                    <div class="action-btns" style="justify-content: flex-end;">
                                        {{-- View order --}}
                                        <a href="{{ route('admin.orders.show', $payment->order_code) }}" class="btn-action-icon" title="View Order Details">
                                            <i class="fas fa-eye"></i>
                                        </a>

                                        {{-- Mark Paid if not paid --}}
                                        @if($payment->payment_status !== 'paid' && $payment->payment_status !== 'refunded')
                                            <button type="button" class="btn-action-icon" style="color:#2e7d32;" onclick="openMarkPaidModal('{{ $payment->order_code }}', '{{ $payment->total }}')" title="Mark as Paid">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif

                                        {{-- Refund if paid --}}
                                        @if($payment->payment_status === 'paid')
                                            <button type="button" class="btn-action-icon" style="color:#c62828;" onclick="openRefundModal('{{ $payment->order_code }}', '{{ $payment->total }}')" title="Issue Refund">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div style="padding: 16px 20px; border-top: 1px solid #eee;">
                    {{ $payments->links() }}
                </div>
            @endif
        @endif
    </div>

</div>

{{-- ── Mark Paid Modal ── --}}
<div class="pay-modal-backdrop" id="markPaidBackdrop"></div>
<div class="pay-modal" id="markPaidModal">
    <div class="pay-modal-head">
        <h3><i class="fas fa-check-circle" style="color:#2e7d32; margin-right:6px;"></i> Mark Order as Paid</h3>
        <button type="button" class="close-btn" onclick="closeModals()">&times;</button>
    </div>
    <form id="markPaidForm" method="post" action="">
        @csrf
        <div class="pay-modal-body">
            <p style="margin-top:0; font-size:13px; color:#555;">
                Confirm payment received for order <strong id="markPaidOrderCode"></strong> (Amount: <strong id="markPaidAmount"></strong>).
            </p>
            <label>Payment Method</label>
            <select name="method">
                <option value="upi">UPI</option>
                <option value="cash">Cash on Delivery</option>
                <option value="bank">Bank Transfer</option>
                <option value="other">Other</option>
            </select>

            <label>Transaction / UTR Number (Optional)</label>
            <input type="text" name="utr" placeholder="e.g. 312345678901">
        </div>
        <div class="pay-modal-foot">
            <button type="button" class="btn btn-default" onclick="closeModals()">Cancel</button>
            <button type="submit" class="btn btn-success" style="background:#2e7d32; border-color:#2e7d32;">Confirm Payment</button>
        </div>
    </form>
</div>

{{-- ── Refund Modal ── --}}
<div class="pay-modal-backdrop" id="refundBackdrop"></div>
<div class="pay-modal" id="refundModal">
    <div class="pay-modal-head">
        <h3><i class="fas fa-undo" style="color:#c62828; margin-right:6px;"></i> Record Refund</h3>
        <button type="button" class="close-btn" onclick="closeModals()">&times;</button>
    </div>
    <form id="refundForm" method="post" action="">
        @csrf
        <div class="pay-modal-body">
            <p style="margin-top:0; font-size:13px; color:#555;">
                Record refund for order <strong id="refundOrderCode"></strong>.
            </p>
            <label>Refund Amount (₹)</label>
            <input type="number" step="0.01" name="amount" id="refundAmountInput" required min="0">

            <label>Refund Reference / Note</label>
            <input type="text" name="reference" placeholder="e.g. Bank refund UTR #45678">
        </div>
        <div class="pay-modal-foot">
            <button type="button" class="btn btn-default" onclick="closeModals()">Cancel</button>
            <button type="submit" class="btn btn-danger" style="background:#c62828; border-color:#c62828;">Record Refund</button>
        </div>
    </form>
</div>

<div id="toast-wrap"></div>
@endsection

@push('scripts')
<script>
    function copyUtr(text) {
        if (!navigator.clipboard) {
            var temp = document.createElement('input');
            temp.value = text;
            document.body.appendChild(temp);
            temp.select();
            document.execCommand('copy');
            document.body.removeChild(temp);
        } else {
            navigator.clipboard.writeText(text);
        }
        showToast('UTR copied to clipboard: ' + text);
    }

    function showToast(msg) {
        var wrap = document.getElementById('toast-wrap');
        var toast = document.createElement('div');
        toast.className = 'toast-msg';
        toast.innerHTML = '<i class="fas fa-check-circle" style="color:#4caf50;"></i> ' + msg;
        wrap.appendChild(toast);
        setTimeout(function() {
            toast.style.transition = 'opacity .3s';
            toast.style.opacity = '0';
            setTimeout(function() { toast.remove(); }, 300);
        }, 2500);
    }

    function openMarkPaidModal(orderCode, amount) {
        document.getElementById('markPaidOrderCode').innerText = orderCode;
        document.getElementById('markPaidAmount').innerText = '₹' + amount;
        document.getElementById('markPaidForm').action = "{{ url('admin/orders') }}/" + orderCode + "/mark-paid";
        document.getElementById('markPaidBackdrop').classList.add('show');
        document.getElementById('markPaidModal').classList.add('show');
    }

    function openRefundModal(orderCode, amount) {
        document.getElementById('refundOrderCode').innerText = orderCode;
        document.getElementById('refundAmountInput').value = amount;
        document.getElementById('refundForm').action = "{{ url('admin/orders') }}/" + orderCode + "/refund";
        document.getElementById('refundBackdrop').classList.add('show');
        document.getElementById('refundModal').classList.add('show');
    }

    function closeModals() {
        document.querySelectorAll('.pay-modal-backdrop').forEach(function(b) { b.classList.remove('show'); });
        document.querySelectorAll('.pay-modal').forEach(function(m) { m.classList.remove('show'); });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModals();
    });
</script>
@endpush

@extends('layouts.admin')

@section('title', 'Orders')

@php
    // The serial number must reflect pagination offset, not just 1..N.
    $startNo = ($orders->currentPage() - 1) * $orders->perPage();

    $statusBadge = [
        'placed'    => 'background:#fff8e1;color:#946a00;',
        'accepted'  => 'background:#e3f2fd;color:#0a4b6e;',
        'dispatched'=> 'background:#e0f2f1;color:#00695c;',
        'delivered' => 'background:#d4edda;color:#155724;',
        'cancelled' => 'background:#f8d7da;color:#721c24;',
        'refunded'  => 'background:#e2e3e5;color:#383d41;',
    ];
    $payBadge = [
        'paid'     => 'background:#d4edda;color:#155724;',
        'failed'   => 'background:#f8d7da;color:#721c24;',
        'pending'  => 'background:#fff8e1;color:#946a00;',
        'refunded' => 'background:#e2e3e5;color:#383d41;',
    ];
@endphp

@push('styles')
<style>
    .ord-page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; flex-wrap:wrap; gap:8px; }
    .ord-page-header h2 { margin:0; font-weight:700; }
    .ord-filters { display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
    .ord-filters input, .ord-filters select {
        padding:7px 10px; border:1px solid #ddd; border-radius:5px; font-size:13px; font-family:inherit; background:#fff;
    }
    .ord-filters input:focus, .ord-filters select:focus { outline:none; border-color:#3a7bd5; box-shadow:0 0 0 2px rgba(58,123,213,0.15); }
    .ord-filters .btn { padding:7px 14px; font-size:13px; }
    .ord-filters .btn-reset { color:#666; text-decoration:none; }

    .ord-table { width:100%; border-collapse:separate; border-spacing:0; background:#fff; border:1px solid #e7e7e7; border-radius:6px; overflow:visible; }
    .ord-table th, .ord-table td { padding:12px 14px; font-size:13px; text-align:left; vertical-align:top; }
    .ord-table thead th { background:#fafafa; border-bottom:1px solid #e7e7e7; font-weight:600; color:#333; text-transform:uppercase; font-size:11px; letter-spacing:0.4px; }
    .ord-table tbody tr { border-top:1px solid #f0f0f0; }
    .ord-table tbody tr:hover { background:#fafbfc; }
    .ord-table td .ord-id { font-weight:700; color:#222; font-size:14px; }
    .ord-table td .ord-id a { color:#3a7bd5; text-decoration:none; }
    .ord-table td .ord-id a:hover { text-decoration:underline; }
    .ord-table td .ord-when { font-size:11px; color:#888; margin-top:2px; }

    .ord-customer { line-height:1.4; }
    .ord-customer .name { font-weight:600; color:#222; }
    .ord-customer .mobile { font-size:12px; color:#555; }
    .ord-customer .addr { font-size:11px; color:#888; max-width:220px; line-height:1.35; margin-top:2px; }

    .ord-items-list { display:flex; flex-direction:column; gap:6px; max-width:380px; }
    .ord-item { display:flex; align-items:flex-start; gap:8px; }
    .ord-item .img { width:36px; height:36px; border-radius:4px; background:#f3f3f3; overflow:hidden; flex-shrink:0; display:flex; align-items:center; justify-content:center; color:#ccc; border:1px solid #eee; }
    .ord-item .img img { width:100%; height:100%; object-fit:cover; }
    .ord-item .info { font-size:12px; line-height:1.4; min-width:0; flex:1; }
    .ord-item .info .pname { color:#222; font-weight:500; }
    .ord-item .info .pmeta { color:#888; font-size:11px; }
    .ord-item .info .prov { color:#2980b9; font-size:11px; margin-top:2px; }
    .ord-item .info .prov i { color:#999; margin-right:3px; }

    .ord-amount { font-weight:700; color:#c7511f; font-size:14px; }
    .ord-amount .method { font-size:10px; color:#888; font-weight:500; text-transform:uppercase; letter-spacing:0.3px; margin-top:2px; }

    .ord-paid-pill { display:inline-block; padding:3px 10px; border-radius:100px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; }

    .ord-status-pill { display:inline-block; padding:3px 10px; border-radius:100px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; }

    /* Actions dropdown — z-index escape */
    .ord-actions { position:relative; }
    .ord-actions .dropdown-menu { z-index: 10000 !important; right:0; left:auto; min-width:230px; }
    .ord-actions .btn-actions {
        background:#3a7bd5; color:#fff; border:none; border-radius:4px; padding:6px 14px; font-size:12px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:6px;
    }
    .ord-actions .btn-actions:hover { background:#2f6bc4; }
    .ord-actions .btn-actions .caret { margin-left:4px; }
    .ord-actions .dropdown-menu > li > a { padding:7px 14px; font-size:13px; cursor:pointer; }
    .ord-actions .dropdown-menu > li > a i { width:18px; color:#888; }
    .ord-actions .dropdown-menu .divider { margin:4px 0; }
    .ord-actions .dropdown-menu .action-section { padding:4px 14px; font-size:10px; color:#999; text-transform:uppercase; letter-spacing:0.4px; font-weight:700; }

    .ord-empty { background:#fff; border:1px dashed #d5d9d9; border-radius:6px; padding:40px 20px; text-align:center; color:#888; }
    .ord-empty i { font-size:36px; color:#d5d9d9; margin-bottom:8px; }

    /* Modal */
    .ord-modal-backdrop { position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.45); z-index:1050; display:none; }
    .ord-modal-backdrop.show { display:block; }
    .ord-modal {
        position:fixed; top:50%; left:50%; transform:translate(-50%,-50%);
        background:#fff; border-radius:8px; box-shadow:0 10px 40px rgba(0,0,0,0.18);
        width:480px; max-width:95vw; max-height:90vh; overflow-y:auto; z-index:1051; display:none;
    }
    .ord-modal.show { display:block; }
    .ord-modal .m-head { padding:14px 18px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center; }
    .ord-modal .m-head h3 { margin:0; font-size:15px; font-weight:700; color:#222; }
    .ord-modal .m-head .x { background:none; border:none; font-size:20px; color:#888; cursor:pointer; padding:0; line-height:1; }
    .ord-modal .m-body { padding:18px; }
    .ord-modal .m-foot { padding:12px 18px; border-top:1px solid #eee; display:flex; justify-content:flex-end; gap:8px; }
    .ord-modal label { display:block; font-weight:600; font-size:12px; color:#444; margin-bottom:4px; }
    .ord-modal input, .ord-modal select, .ord-modal textarea {
        width:100%; padding:8px 10px; border:1px solid #ddd; border-radius:5px; font-size:13px; font-family:inherit; box-sizing:border-box;
    }
    .ord-modal textarea { resize:vertical; min-height:70px; }
    .ord-modal .btn { padding:8px 16px; font-size:13px; }
    .ord-modal .btn-primary { background:#3a7bd5; color:#fff; border:1px solid #3a7bd5; }
    .ord-modal .btn-secondary { background:#fff; color:#333; border:1px solid #ddd; }

    .ord-pagination { margin-top:16px; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    <div class="ord-page-header">
        <h2><i class="fas fa-shopping-cart" style="color:#3a7bd5;"></i> Orders
            <span style="font-size:13px; color:#888; font-weight:500;">({{ $orders->total() }})</span>
        </h2>
    </div>

    <form class="ord-filters" method="get">
        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Order ID, name, mobile, UTR, product…">
        <select name="status">
            <option value="">All statuses</option>
            @foreach(['placed','accepted','dispatched','delivered','cancelled','refunded'] as $s)
                <option value="{{ $s }}" @selected(($filters['status'] ?? '') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <select name="payment_status">
            <option value="">All payments</option>
            @foreach(['paid','pending','failed','refunded'] as $p)
                <option value="{{ $p }}" @selected(($filters['payment_status'] ?? '') === $p)>{{ ucfirst($p) }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="{{ route('admin.orders.index') }}" class="btn-reset">Reset</a>
    </form>

    @if($orders->isEmpty())
        <div class="ord-empty">
            <i class="fas fa-inbox"></i>
            <div>No orders found.</div>
        </div>
    @else
        <div style="overflow:visible;">
            <table class="ord-table">
                <thead>
                    <tr>
                        <th style="width:36px; text-align:center;"><input type="checkbox" id="bulkMasterCheck"></th>
                        <th style="width:50px;">S.No</th>
                        <th style="width:120px;">Order ID</th>
                        <th style="width:240px;">Customer</th>
                        <th>Items purchased</th>
                        <th style="width:130px;">Amount</th>
                        <th style="width:110px;">Paid</th>
                        <th style="width:120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($orders as $i => $o)
                    @php
                        $row = $startNo + $i + 1;
                        $rowStyle = $statusBadge[$o->status] ?? 'background:#e2e3e5;color:#383d41;';
                        $payStyle = $payBadge[$o->payment_status] ?? 'background:#fff8e1;color:#946a00;';
                    @endphp
                    <tr>
                        <td style="vertical-align:middle; text-align:center;">
                            <input type="checkbox" class="bulk-item-check" value="{{ $o->id }}">
                        </td>
                        <td>{{ $row }}</td>
                        <td>
                            <div class="ord-id">
                                <a href="{{ route('admin.orders.show', ['order' => $o->order_code]) }}">{{ $o->order_code }}</a>
                            </div>
                            <div class="ord-when">{{ $o->created_at->format('d M Y, h:i A') }}</div>
                            <div style="margin-top:4px;">
                                <span class="ord-status-pill" style="{{ $rowStyle }}">{{ $o->status }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="ord-customer">
                                <div class="name">{{ $o->contact_name ?: $o->addr_full_name ?: '—' }}</div>
                                <div class="mobile"><i class="fas fa-phone" style="color:#aaa;"></i> {{ $o->contact_mobile ?: $o->addr_mobile_primary ?: '—' }}</div>
                                <div class="addr">{{ $o->addr_line_1 }}{{ $o->addr_line_2 ? ', '.$o->addr_line_2 : '' }}, {{ $o->addr_city }}, {{ $o->addr_state }} — {{ $o->addr_pincode }}</div>
                            </div>
                        </td>
                        <td>
                            <div class="ord-items-list">
                                @foreach($o->items as $it)
                                    <div class="ord-item">
                                        <div class="img">
                                            @if($it->product_image)
                                                <img src="{{ $it->product_image }}" alt="">
                                            @else
                                                <i class="fas fa-image"></i>
                                            @endif
                                        </div>
                                        <div class="info">
                                            <div class="pname">{{ $it->product_name }}</div>
                                            <div class="pmeta">Qty: {{ $it->quantity }} × ₹{{ number_format($it->unit_price, 2) }}</div>
                                            <div class="prov">
                                                <i class="fas fa-long-arrow-alt-right"></i>
                                                <strong>{{ $it->product_name }}</strong> &rarr; {{ $it->provider_names ? implode(', ', $it->provider_names) : '—' }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </td>
                        <td>
                            <div class="ord-amount">₹{{ number_format($o->total, 2) }}</div>
                            <div class="method">{{ strtoupper($o->payment_method ?? '—') }}{{ $o->payment_gateway ? ' · '.ucfirst($o->payment_gateway) : '' }}</div>
                        </td>
                        <td>
                            <span class="ord-paid-pill" style="{{ $payStyle }}">{{ $o->payment_status }}</span>
                            @if($o->payment_status === 'paid' && $o->paid_at)
                                <div class="ord-when">{{ $o->paid_at->format('d M, h:i A') }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="ord-actions dropdown">
                                <button type="button" class="btn-actions dropdown-toggle" data-toggle="dropdown">
                                    Actions <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu" role="menu">
                                    <li><a href="{{ route('admin.orders.show', ['order' => $o->order_code]) }}"><i class="fas fa-eye"></i> View order</a></li>
                                    <li class="divider"></li>
                                    <li class="action-section">Set status</li>
                                    @if($o->status === 'placed')
                                        <li>
                                            <a onclick="openStatusModal('{{ $o->order_code }}', 'accepted')"><i class="fas fa-check"></i> Order accepted</a>
                                        </li>
                                    @elseif($o->status === 'accepted')
                                        <li>
                                            <a onclick="openStatusModal('{{ $o->order_code }}', 'dispatched')"><i class="fas fa-truck"></i> Dispatched</a>
                                        </li>
                                    @elseif($o->status === 'dispatched')
                                        <li>
                                            <a onclick="openStatusModal('{{ $o->order_code }}', 'delivered')"><i class="fas fa-box-open"></i> Delivered</a>
                                        </li>
                                    @endif
                                    <li>
                                        <a onclick="openCustomStatusModal('{{ $o->order_code }}')"><i class="fas fa-comment-dots"></i> Add custom status…</a>
                                    </li>
                                    <li class="divider"></li>
                                    <li class="action-section">Money</li>
                                    <li>
                                        <a onclick="openMarkPaidModal('{{ $o->order_code }}')"><i class="fas fa-money-bill-wave"></i> Mark as paid</a>
                                    </li>
                                    <li>
                                        <a onclick="openRefundModal('{{ $o->order_code }}', '{{ number_format($o->total, 2) }}')"><i class="fas fa-undo"></i> Refund…</a>
                                    </li>
                                    <li class="divider"></li>
                                    <li>
                                        <a onclick="openCancelModal('{{ $o->order_code }}')" style="color:#c0392b;"><i class="fas fa-times" style="color:#c0392b;"></i> Cancel order</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('admin.orders.invoice', ['order' => $o->order_code, 'download' => 1]) }}"><i class="fas fa-download"></i> Download bill</a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="ord-pagination">
            {{ $orders->links() }}
        </div>
    @endif
</div>

{{-- Modals (single set, populated by JS) --}}
<div class="ord-modal-backdrop" id="ordBackdrop"></div>

<form class="ord-modal" id="ordStatusModal" method="post" action="">
    @csrf
    <div class="m-head">
        <h3 id="ordStatusTitle">Set status</h3>
        <button type="button" class="x" onclick="closeModals()">×</button>
    </div>
    <div class="m-body">
        <input type="hidden" name="status" id="ordStatusValue">
        <label>Note (optional)</label>
        <textarea name="note" placeholder="Anything relevant — courier, ETA, internal note…"></textarea>
    </div>
    <div class="m-foot">
        <button type="button" class="btn btn-secondary" onclick="closeModals()">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>

<form class="ord-modal" id="ordCustomStatusModal" method="post" action="">
    @csrf
    <div class="m-head">
        <h3>Add custom status</h3>
        <button type="button" class="x" onclick="closeModals()">×</button>
    </div>
    <div class="m-body">
        <label>Message</label>
        <input type="text" name="message" maxlength="500" placeholder="e.g. Out for delivery — ETA 30 mins" required>
    </div>
    <div class="m-foot">
        <button type="button" class="btn btn-secondary" onclick="closeModals()">Cancel</button>
        <button type="submit" class="btn btn-primary">Add</button>
    </div>
</form>

<form class="ord-modal" id="ordMarkPaidModal" method="post" action="">
    @csrf
    <div class="m-head">
        <h3>Mark order as paid</h3>
        <button type="button" class="x" onclick="closeModals()">×</button>
    </div>
    <div class="m-body">
        <p style="font-size:12px; color:#888; margin-top:0;">Use this for cash on delivery, manual UPI confirmations, or gateway-recovery flows.</p>
        <label>Method</label>
        <select name="method">
            <option value="cash">Cash</option>
            <option value="upi">UPI</option>
            <option value="bank">Bank transfer</option>
            <option value="other">Other</option>
        </select>
        <label style="margin-top:10px;">UTR / reference (optional)</label>
        <input type="text" name="utr" maxlength="100" placeholder="e.g. 412345678910">
    </div>
    <div class="m-foot">
        <button type="button" class="btn btn-secondary" onclick="closeModals()">Cancel</button>
        <button type="submit" class="btn btn-primary">Mark paid</button>
    </div>
</form>

<form class="ord-modal" id="ordCancelModal" method="post" action="">
    @csrf
    <div class="m-head">
        <h3>Cancel order</h3>
        <button type="button" class="x" onclick="closeModals()">×</button>
    </div>
    <div class="m-body">
        <label>Reason (visible in history)</label>
        <textarea name="reason" maxlength="500" placeholder="Why is this being cancelled?" required></textarea>
    </div>
    <div class="m-foot">
        <button type="button" class="btn btn-secondary" onclick="closeModals()">Keep</button>
        <button type="submit" class="btn btn-primary" style="background:#c0392b; border-color:#c0392b;">Cancel order</button>
    </div>
</form>

<form class="ord-modal" id="ordRefundModal" method="post" action="">
    @csrf
    <div class="m-head">
        <h3>Refund</h3>
        <button type="button" class="x" onclick="closeModals()">×</button>
    </div>
    <div class="m-body">
        <label>Amount (₹)</label>
        <input type="number" step="0.01" min="0" name="amount" id="ordRefundAmount" required>
        <label style="margin-top:10px;">Reference (optional)</label>
        <input type="text" name="reference" maxlength="100" placeholder="e.g. REF-2026-0001">
    </div>
    <div class="m-foot">
        <button type="button" class="btn btn-secondary" onclick="closeModals()">Cancel</button>
        <button type="submit" class="btn btn-primary">Record refund</button>
    </div>
</form>

@endsection

@push('scripts')
<script>
    const backdrop = document.getElementById('ordBackdrop');

    function openModal(modal) {
        backdrop.classList.add('show');
        modal.classList.add('show');
    }
    function closeModals() {
        backdrop.classList.remove('show');
        document.querySelectorAll('.ord-modal').forEach(m => m.classList.remove('show'));
    }
    backdrop.addEventListener('click', closeModals);

    const routeStatus       = @json(route('admin.orders.status', ['order' => '__ORDER__']));
    const routeCustomStatus = @json(route('admin.orders.custom-status', ['order' => '__ORDER__']));
    const routeMarkPaid     = @json(route('admin.orders.mark-paid', ['order' => '__ORDER__']));
    const routeCancel       = @json(route('admin.orders.cancel', ['order' => '__ORDER__']));
    const routeRefund       = @json(route('admin.orders.refund', ['order' => '__ORDER__']));

    function openStatusModal(code, status) {
        const m = document.getElementById('ordStatusModal');
        m.action = routeStatus.replace('__ORDER__', code);
        document.getElementById('ordStatusValue').value = status;
        document.getElementById('ordStatusTitle').textContent = 'Mark as ' + status;
        openModal(m);
    }
    function openCustomStatusModal(code) {
        const m = document.getElementById('ordCustomStatusModal');
        m.action = routeCustomStatus.replace('__ORDER__', code);
        m.querySelector('input[name=message]').value = '';
        openModal(m);
    }
    function openMarkPaidModal(code) {
        const m = document.getElementById('ordMarkPaidModal');
        m.action = routeMarkPaid.replace('__ORDER__', code);
        openModal(m);
    }
    function openCancelModal(code) {
        const m = document.getElementById('ordCancelModal');
        m.action = routeCancel.replace('__ORDER__', code);
        m.querySelector('textarea[name=reason]').value = '';
        openModal(m);
    }
    function openRefundModal(code, total) {
        const m = document.getElementById('ordRefundModal');
        m.action = routeRefund.replace('__ORDER__', code);
        document.getElementById('ordRefundAmount').value = total;
        openModal(m);
    }

    // Register Bulk Actions in Universal Bar
    $(function () {
        var html = '';
        html += '<div style="display:inline-flex; align-items:center; gap:6px;">';
        html += '  <select id="bulkOrderStatusSelect" class="form-control input-sm" style="display:inline-block; width:135px; height:32px; background:#1e293b; color:#fff; border-color:#334155; font-size:12px;">';
        html += '    <option value="">Update Status...</option>';
        html += '    <option value="confirmed">Confirmed</option>';
        html += '    <option value="processing">Processing</option>';
        html += '    <option value="packed">Packed</option>';
        html += '    <option value="dispatched">Dispatched</option>';
        html += '    <option value="shipped">Shipped</option>';
        html += '    <option value="delivered">Delivered</option>';
        html += '    <option value="cancelled">Cancelled</option>';
        html += '  </select>';
        html += '  <button type="button" class="bulk-action-btn" onclick="executeBulkOrderStatus()"><i class="fas fa-arrow-right"></i> Apply</button>';
        html += '</div>';
        html += '<button type="button" class="bulk-action-btn btn-bulk-success" onclick="executeOrderBulk(\'mark_paid\', \'Mark {count} selected order(s) as Paid?\')"><i class="fas fa-check-double"></i> Mark Paid</button>';
        $('#bulkBarActions').html(html);
    });

    function executeBulkOrderStatus() {
        var status = $('#bulkOrderStatusSelect').val();
        if (!status) {
            adminToast('Please select a status to apply.', 'error');
            return;
        }
        runBulkAction('{{ route("admin.orders.bulk-action") }}', 'update_status', { status: status }, 'Change status to "' + status + '" for {count} selected order(s)?');
    }

    function executeOrderBulk(action, confirmMsg) {
        runBulkAction('{{ route("admin.orders.bulk-action") }}', action, {}, confirmMsg);
    }
</script>
@endpush

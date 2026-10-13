@extends('layouts.admin')

@section('title', 'Cancellations & Returns')

@push('styles')
<style>
.kpi-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 18px;
}
.kpi {
    background: #fff;
    border: 1px solid #e7e7e7;
    border-radius: 8px;
    padding: 16px 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}
.kpi .icon {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.kpi.c1 .icon { background: #fdecea; color: #a93226; }
.kpi.c2 .icon { background: #fff7e6; color: #946a00; }
.kpi.c3 .icon { background: #e8f5e9; color: #1a7a42; }
.kpi .num { font-size: 22px; font-weight: 700; color: #1a1a2e; line-height: 1.2; }
.kpi .lbl { font-size: 12px; color: #777; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 2px; }

@media (max-width: 860px) {
    .kpi-row { grid-template-columns: repeat(2, 1fr); gap: 10px; }
}
@media (max-width: 520px) {
    .kpi-row { grid-template-columns: 1fr; gap: 8px; }
    .kpi { padding: 12px 14px; }
}

.tabs {
    display: flex;
    gap: 4px;
    border-bottom: 2px solid #e7e7e7;
    margin-bottom: 14px;
    overflow-x: auto;
    overflow-y: visible;
    -webkit-overflow-scrolling: touch;
    touch-action: pan-x pan-y;
    overscroll-behavior-x: contain;
    white-space: nowrap;
    scrollbar-width: none;
}
.tabs::-webkit-scrollbar {
    display: none;
}
.tabs a {
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 600;
    color: #555;
    text-decoration: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
    flex-shrink: 0;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
}
.tabs a.active { color: #007185; border-color: #007185; }
.tabs a .count {
    background: #f0f0f0;
    color: #555;
    padding: 1px 8px;
    border-radius: 100px;
    font-size: 11px;
    margin-left: 6px;
}
.tabs a.active .count { background: #e3f2fd; color: #0a4b87; }

.toolbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
    gap: 10px;
    flex-wrap: wrap;
}
.toolbar form {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
    width: 100%;
}
.toolbar input[type=text], .toolbar select {
    padding: 8px 12px;
    border: 1px solid #d5d9d9;
    border-radius: 6px;
    font-size: 13px;
    background: #fff;
    height: 36px;
    box-sizing: border-box;
}
.toolbar input[type=text] {
    flex: 1 1 200px;
    min-width: 0;
    max-width: 360px;
}
.toolbar select {
    flex: 0 1 auto;
    min-width: 130px;
}
.toolbar button {
    padding: 0 16px;
    height: 36px;
    background: #3a7bd5;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    flex-shrink: 0;
}
.toolbar button:hover { background: #2f6bc4; }

@media (max-width: 576px) {
    .toolbar form {
        flex-direction: column;
        align-items: stretch;
    }
    .toolbar input[type=text],
    .toolbar select,
    .toolbar button {
        width: 100%;
        max-width: 100%;
    }
    .toolbar button {
        justify-content: center;
    }
}

/* ── Scrollable Table Wrapper ── */
.table-responsive {
    width: 100% !important;
    max-width: 100% !important;
    overflow-x: auto !important;
    overflow-y: visible !important;
    -webkit-overflow-scrolling: touch !important;
    touch-action: pan-x pan-y !important;
    overscroll-behavior-x: contain;
    border: 1px solid #e7e7e7;
    border-radius: 8px;
    background: #fff;
    margin-bottom: 14px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f8fafc;
}
.table-responsive > table,
.table-responsive > .table,
.table-responsive th,
.table-responsive td {
    touch-action: pan-x pan-y !important;
    max-width: none !important;
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
.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.mobile-scroll-hint {
    display: none;
    font-size: 11.5px;
    color: #64748b;
    margin: 4px 0 8px;
    align-items: center;
    gap: 6px;
}
@media (max-width: 900px) {
    .mobile-scroll-hint {
        display: flex;
    }
}

table.data {
    width: 100%;
    border-collapse: collapse;
    background: #fff;
    border: none;
    margin: 0;
}
table.data-cancellations {
    min-width: 760px;
}
table.data-returns {
    min-width: 920px;
}
table.data th {
    background: #f6f8fa;
    text-align: left;
    font-size: 12px;
    color: #555;
    padding: 11px 12px;
    border-bottom: 1px solid #e7e7e7;
    white-space: nowrap;
}
table.data td {
    padding: 11px 12px;
    border-bottom: 1px solid #f0f0f0;
    font-size: 13px;
    vertical-align: top;
}
table.data tr:last-child td {
    border-bottom: none;
}
table.data tr:hover td {
    background: #fafbfc;
}

/* Explicit column min-widths for reliable horizontal touch scrolling */
.col-order    { min-width: 110px; white-space: nowrap; }
.col-customer { min-width: 150px; }
.col-date     { min-width: 135px; white-space: nowrap; }
.col-reason   { min-width: 180px; max-width: 320px; word-break: break-word; }
.col-total    { min-width: 90px; white-space: nowrap; }
.col-payment  { min-width: 100px; white-space: nowrap; }
.col-action   { min-width: 75px; text-align: right; }
.col-product  { min-width: 220px; }
.col-status   { min-width: 120px; white-space: nowrap; }
.col-manage   { min-width: 260px; }

.tag { display: inline-block; padding: 2px 8px; border-radius: 100px; font-size: 11px; font-weight: 600; }
.tag.cancelled { background: #f8d7da; color: #721c24; }
.tag.requested        { background: #fff8e1; color: #946a00; }
.tag.accepted         { background: #e3f2fd; color: #0a4b6e; }
.tag.pickup_scheduled { background: #e0f2f1; color: #00695c; }
.tag.picked_up        { background: #d4edda; color: #155724; }
.tag.completed        { background: #d4edda; color: #155724; }
.tag.rejected         { background: #f8d7da; color: #721c24; }

.reason-line { font-size: 12px; color: #444; margin-top: 4px; }
.thumb { width: 40px; height: 40px; border-radius: 4px; object-fit: cover; background: #f0f0f0; flex-shrink: 0; }
.thumb-ph { width: 40px; height: 40px; border-radius: 4px; background: #f0f0f0; display:flex; align-items:center; justify-content:center; color:#bbb; font-size:14px; flex-shrink: 0; }

.status-form {
    display: flex;
    gap: 6px;
    align-items: center;
}
.status-form select {
    flex: 1;
    min-width: 130px;
    padding: 6px 8px;
    border: 1px solid #d5d9d9;
    border-radius: 4px;
    font-size: 12px;
    background: #fff;
    height: 32px;
    box-sizing: border-box;
}
.status-form select:focus {
    border-color: #007185;
    outline: none;
}
.status-form button {
    padding: 0 12px;
    background: #007185;
    color: #fff;
    border: none;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    height: 32px;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.15s ease;
    flex-shrink: 0;
}
.status-form button:hover { background: #005f6b; }

.note-input {
    flex: 1;
    min-width: 120px;
    border: 1px solid #d5d9d9;
    border-radius: 4px;
    padding: 6px 8px;
    font-size: 12px;
    height: 32px;
    box-sizing: border-box;
}
.note-input:focus {
    border-color: #007185;
    outline: none;
}

.empty { padding: 30px; text-align: center; color: #999; }
.section-title { font-size: 14px; font-weight: 700; color: #1a1a2e; margin: 0 0 10px; padding-bottom: 6px; border-bottom: 1px solid #f0f0f0; }

@media (max-width: 768px) {
    .page-main-wrap {
        padding: 0 4px;
    }
}
</style>
@endpush

@section('content')
<div class="page-main-wrap" style="max-width: 1280px; margin: 0 auto;">

    <h2 style="margin: 0 0 14px; font-size: 22px; color: #1a1a2e;">
        <i class="fas fa-undo" style="color:#c7511f;"></i> Cancellations &amp; Returns
    </h2>

    <div class="kpi-row">
        <div class="kpi c1">
            <div class="icon"><i class="fas fa-ban"></i></div>
            <div><div class="num">{{ $stats['cancellations'] }}</div><div class="lbl">Cancellations</div></div>
        </div>
        <div class="kpi c2">
            <div class="icon"><i class="fas fa-undo"></i></div>
            <div><div class="num">{{ $stats['returns_open'] }}</div><div class="lbl">Open returns</div></div>
        </div>
        <div class="kpi c3">
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <div><div class="num">{{ $stats['returns_done'] }}</div><div class="lbl">Closed returns</div></div>
        </div>
    </div>

    <div class="tabs">
        <a href="{{ route('admin.cancellations-returns.index', ['tab' => 'all']) }}" class="{{ $tab === 'all' ? 'active' : '' }}">All <span class="count">{{ $stats['cancellations'] + $stats['returns_open'] + $stats['returns_done'] }}</span></a>
        <a href="{{ route('admin.cancellations-returns.index', ['tab' => 'cancellations']) }}" class="{{ $tab === 'cancellations' ? 'active' : '' }}">Cancellations <span class="count">{{ $stats['cancellations'] }}</span></a>
        <a href="{{ route('admin.cancellations-returns.index', ['tab' => 'returns']) }}" class="{{ $tab === 'returns' ? 'active' : '' }}">Returns <span class="count">{{ $stats['returns_open'] + $stats['returns_done'] }}</span></a>
    </div>

    @if(session('success'))
        <div style="background:#d4edda; color:#155724; padding:10px 14px; border-radius:6px; margin-bottom:12px; font-size:13px;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    @if($tab !== 'returns')
    {{-- ═══ Cancellations ═══ --}}
    <h3 class="section-title"><i class="fas fa-ban" style="color:#c7511f;"></i> Cancelled orders</h3>
    <div class="toolbar">
        <form method="GET" action="{{ route('admin.cancellations-returns.index') }}">
            <input type="hidden" name="tab" value="cancellations">
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search by order code, name, mobile…">
            <button><i class="fas fa-search"></i> Search</button>
        </form>
    </div>

    <div class="mobile-scroll-hint"><i class="fas fa-arrows-alt-h"></i> Scroll horizontally to view all columns &amp; actions</div>
    <div class="table-responsive">
        <table class="data data-cancellations">
            <thead>
                <tr>
                    <th class="col-order">Order</th>
                    <th class="col-customer">Customer</th>
                    <th class="col-date">Cancelled on</th>
                    <th class="col-reason">Reason</th>
                    <th class="col-total">Total</th>
                    <th class="col-payment">Payment</th>
                    <th class="col-action"></th>
                </tr>
            </thead>
            <tbody>
            @forelse($cancellations as $o)
                <tr>
                    <td class="col-order">
                        <a href="{{ route('admin.orders.show', $o->order_code) }}" style="color:#007185; font-weight:600; text-decoration:none;">{{ $o->order_code }}</a>
                    </td>
                    <td class="col-customer">
                        {{ $o->contact_name }}<br>
                        <small style="color:#888;">{{ $o->contact_mobile }}</small>
                    </td>
                    <td class="col-date">
                        <small>{{ $o->cancelled_at ? $o->cancelled_at->format('d M Y, h:i A') : '—' }}</small>
                    </td>
                    <td class="col-reason">
                        <div class="reason-line">{{ $o->cancelled_reason ?: '—' }}</div>
                    </td>
                    <td class="col-total">₹{{ number_format($o->total, 0) }}</td>
                    <td class="col-payment"><span class="tag {{ $o->payment_status }}">{{ ucfirst($o->payment_status) }}</span></td>
                    <td class="col-action">
                        <a href="{{ route('admin.orders.show', $o->order_code) }}" class="btn-secondary" style="font-size:11.5px; padding:4px 10px; border:1px solid #d5d9d9; border-radius:4px; color:#333; text-decoration:none; display:inline-block;">Open</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty">No cancellations yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:10px;">{{ $cancellations->appends(request()->query())->links() }}</div>
    @endif

    @if($tab !== 'cancellations')
    {{-- ═══ Returns ═══ --}}
    <h3 class="section-title" style="margin-top:24px;"><i class="fas fa-undo" style="color:#946a00;"></i> Return requests</h3>
    <div class="toolbar">
        <form method="GET" action="{{ route('admin.cancellations-returns.index') }}">
            <input type="hidden" name="tab" value="returns">
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search by order, customer, reason…">
            <select name="status">
                <option value="">All statuses</option>
                @foreach(\App\Models\OrderReturn::STATUSES as $key => $label)
                    <option value="{{ $key }}" {{ ($filters['status'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button><i class="fas fa-filter"></i> Filter</button>
        </form>
    </div>

    <div class="mobile-scroll-hint"><i class="fas fa-arrows-alt-h"></i> Scroll horizontally to view all columns &amp; actions</div>
    <div class="table-responsive">
        <table class="data data-returns">
            <thead>
                <tr>
                    <th class="col-product">Product</th>
                    <th class="col-order">Order</th>
                    <th class="col-customer">Customer</th>
                    <th class="col-reason">Reason</th>
                    <th class="col-status">Status</th>
                    <th class="col-date">Requested</th>
                    <th class="col-manage">Manage</th>
                </tr>
            </thead>
            <tbody>
            @forelse($returns as $r)
                @php $order = $r->order; @endphp
                <tr>
                    <td class="col-product">
                        <div style="display:flex; gap:8px; align-items:center;">
                            @if($r->image_path)
                                <img src="{{ asset('storage/' . $r->image_path) }}" class="thumb" alt="">
                            @elseif($r->product && $r->product->image)
                                <img src="{{ $r->product->image }}" class="thumb" alt="">
                            @else
                                <div class="thumb-ph"><i class="fas fa-image"></i></div>
                            @endif
                            <div style="min-width:0;">
                                <div style="font-weight:600; line-height:1.3;">{{ $r->product->name ?? ($order->items->firstWhere('id', $r->order_item_id)->product_name ?? '—') }}</div>
                                <small style="color:#888;">₹{{ number_format($r->product->price ?? 0, 0) }}</small>
                            </div>
                        </div>
                    </td>
                    <td class="col-order">
                        <a href="{{ $order ? route('admin.orders.show', $order->order_code) : '#' }}" style="color:#007185; font-weight:600; text-decoration:none;">{{ $order->order_code ?? '—' }}</a>
                    </td>
                    <td class="col-customer">
                        {{ $r->customer->name ?? '—' }}<br>
                        <small style="color:#888;">{{ $r->customer->email ?? '' }}</small>
                    </td>
                    <td class="col-reason">
                        <div class="reason-line"><strong>{{ $r->reason }}</strong></div>
                        @if($r->description)
                            <div class="reason-line" style="color:#666; margin-top:2px;">{{ $r->description }}</div>
                        @endif
                    </td>
                    <td class="col-status"><span class="tag {{ $r->status }}">{{ $r->status_label }}</span></td>
                    <td class="col-date"><small>{{ $r->requested_at ? $r->requested_at->format('d M Y, h:i A') : '—' }}</small></td>
                    <td class="col-manage">
                        <form method="POST" action="{{ route('admin.returns.status', $r) }}" class="status-form" style="margin-bottom:6px;">
                            @csrf
                            <select name="status" aria-label="Update return status">
                                @foreach(\App\Models\OrderReturn::STATUSES as $key => $label)
                                    <option value="{{ $key }}" {{ $r->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="submit">Save</button>
                        </form>
                        <form method="POST" action="{{ route('admin.returns.note', $r) }}" class="status-form">
                            @csrf
                            <input type="text" name="admin_note" class="note-input" placeholder="Add admin note" value="{{ $r->admin_note }}" aria-label="Admin note">
                            <button type="submit" style="background:#64748b;">Note</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="empty">No return requests.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:10px;">{{ $returns->appends(request()->query())->links() }}</div>
    @endif

</div>
@endsection


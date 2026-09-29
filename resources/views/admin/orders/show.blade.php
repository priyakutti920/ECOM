@extends('layouts.admin')

@section('title', 'Order ' . $order->order_code)

@php
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
    $rowStyle = $statusBadge[$order->status] ?? 'background:#e2e3e5;color:#383d41;';
    $payStyle = $payBadge[$order->payment_status] ?? 'background:#fff8e1;color:#946a00;';
@endphp

@push('styles')
<style>
    .ord-show-head { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:14px; flex-wrap:wrap; gap:12px; }
    .ord-show-head h2 { margin:0 0 4px; font-weight:700; }
    .ord-show-head .meta { font-size:12px; color:#888; }
    .ord-show-head .right { text-align:right; }
    .ord-show-head .total { font-size:24px; font-weight:800; color:#c7511f; }

    .status-pill { display:inline-block; padding:3px 10px; border-radius:100px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; }
    .pay-pill { display:inline-block; padding:3px 10px; border-radius:100px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.4px; }

    .panel { background:#fff; border:1px solid #e7e7e7; border-radius:6px; margin-bottom:16px; }
    .panel-head { padding:12px 16px; border-bottom:1px solid #eee; font-size:13px; font-weight:700; color:#333; text-transform:uppercase; letter-spacing:0.4px; display:flex; align-items:center; gap:8px; }
    .panel-head i { color:#3a7bd5; }
    .panel-body { padding:14px 16px; }

    .ord-info-row { display:flex; font-size:13px; padding:4px 0; }
    .ord-info-row .lbl { color:#888; min-width:130px; flex-shrink:0; }
    .ord-info-row .val { color:#222; }

    /* Items table */
    .ord-items-table { width:100%; border-collapse:collapse; }
    .ord-items-table th, .ord-items-table td { padding:10px 12px; font-size:13px; text-align:left; }
    .ord-items-table thead th { background:#fafafa; border-bottom:1px solid #eee; font-weight:600; color:#555; text-transform:uppercase; font-size:11px; letter-spacing:0.3px; }
    .ord-items-table tbody tr { border-top:1px solid #f0f0f0; }
    .ord-items-table .img-cell { width:50px; }
    .ord-items-table .img-cell img, .ord-items-table .img-cell .ph { width:40px; height:40px; border-radius:4px; background:#f3f3f3; object-fit:cover; display:flex; align-items:center; justify-content:center; color:#ccc; }
    .ord-items-table .provider { color:#2980b9; font-size:12px; }
    .ord-items-table .provider i { color:#999; margin-right:3px; }

    .ord-totals { background:#fff7e6; border:1px solid #f0c14b; border-radius:5px; padding:12px 16px; margin-top:10px; }
    .ord-totals .row { display:flex; justify-content:space-between; font-size:13px; padding:3px 0; }
    .ord-totals .row.tot { font-size:15px; font-weight:700; color:#c7511f; border-top:1px dashed #d5b878; margin-top:6px; padding-top:8px; }

    /* Status timeline — neat + spaced */
    .ord-timeline { padding:8px 0 8px 26px; position:relative; margin:0; }
    .ord-timeline::before { content:''; position:absolute; left:9px; top:14px; bottom:14px; width:2px; background:#e7e7e7; border-radius:1px; }
    .ord-tl-step {
        position:relative;
        padding:10px 0 10px 16px;
        margin:0;
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    .ord-tl-step::before {
        content:'';
        position:absolute;
        left:-19px; top:14px;
        width:14px; height:14px;
        border-radius:50%;
        background:#fff;
        border:2px solid #d5d9d9;
        box-shadow: 0 0 0 3px #fff;
    }
    .ord-tl-step:last-child { padding-bottom: 4px; }
    .ord-tl-step.done::before { background:#007600; border-color:#007600; }
    .ord-tl-step.active::before { background:#3a7bd5; border-color:#3a7bd5; box-shadow:0 0 0 4px rgba(58,123,213,0.15); }
    .ord-tl-step .lbl {
        font-size:13.5px;
        font-weight:600;
        color:#222;
        line-height: 1.4;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .ord-tl-step.done .lbl { color:#007600; }
    .ord-tl-step .when {
        font-size:11.5px;
        color:#777;
        line-height: 1.5;
        padding-left: 22px;
    }
    .ord-tl-step .note {
        font-size:12px;
        color:#555;
        line-height: 1.5;
        padding-left: 22px;
        background: #fafbfc;
        border-left: 2px solid #e7e7e7;
        padding: 6px 8px 6px 10px;
        margin-top: 4px;
        border-radius: 0 4px 4px 0;
    }
    .ord-tl-step.done .note { border-left-color: #b6e0bf; background: #f4faf5; }
    .ord-tl-step.active .note { border-left-color: #c1d8f0; background: #f4f9fd; }

    @media (max-width: 480px) {
        .ord-timeline { padding-left: 22px; }
        .ord-tl-step::before { left: -17px; }
        .ord-tl-step .when, .ord-tl-step .note { padding-left: 14px; }
    }

    /* Custom status list */
    .ord-cs { display:flex; flex-direction:column; gap:8px; }
    .ord-cs-item { display:flex; justify-content:space-between; align-items:flex-start; gap:8px; padding:10px 12px; background:#fff8e1; border:1px solid #f0c14b; border-radius:5px; }
    .ord-cs-item .body { font-size:13px; }
    .ord-cs-item .body .msg { color:#222; }
    .ord-cs-item .body .who { font-size:11px; color:#946a00; margin-top:2px; }
    .ord-cs-item form { margin:0; }
    .ord-cs-item .rm { background:none; border:none; color:#c0392b; cursor:pointer; font-size:12px; padding:2px 6px; }

    /* Add custom status */
    .ord-cs-form { display:flex; gap:6px; margin-top:10px; }
    .ord-cs-form input { flex:1; padding:7px 10px; border:1px solid #ddd; border-radius:4px; font-size:13px; }
    .ord-cs-form input:focus { outline:none; border-color:#3a7bd5; box-shadow:0 0 0 2px rgba(58,123,213,0.15); }
    .ord-cs-form button { background:#3a7bd5; color:#fff; border:none; border-radius:4px; padding:7px 14px; font-size:13px; font-weight:600; cursor:pointer; }
    .ord-cs-form button:hover { background:#2f6bc4; }

    /* Actions dropdown (in head) */
    .ord-actions { position:relative; }
    .ord-actions .dropdown-menu { z-index:10000 !important; right:0; left:auto; min-width:230px; }
    .ord-actions .btn-actions {
        background:#3a7bd5; color:#fff; border:none; border-radius:4px; padding:8px 18px; font-size:13px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:6px;
    }
    .ord-actions .btn-actions:hover { background:#2f6bc4; }
    .ord-actions .dropdown-menu > li > a { padding:7px 14px; font-size:13px; cursor:pointer; }
    .ord-actions .dropdown-menu > li > a i { width:18px; color:#888; }
    .ord-actions .dropdown-menu .divider { margin:4px 0; }
    .ord-actions .dropdown-menu .action-section { padding:4px 14px; font-size:10px; color:#999; text-transform:uppercase; letter-spacing:0.4px; font-weight:700; }

    /* Payment completed callout */
    .ord-pay-callout { background:#d4edda; border:1px solid #b1dfbb; border-radius:5px; padding:10px 14px; font-size:12px; color:#155724; margin-top:10px; }
    .ord-pay-callout i { color:#007600; }
    .ord-pay-callout .when { color:#1a7a42; font-weight:600; }

    /* History */
    .ord-history-list { display:flex; flex-direction:column; gap:6px; }
    .ord-history-list .hi { font-size:12px; padding:8px 10px; background:#fafbfc; border:1px solid #eef0f1; border-radius:4px; }
    .ord-history-list .hi .when { color:#888; font-size:11px; }
    .ord-history-list .hi .event { font-weight:700; color:#222; margin-right:6px; text-transform:uppercase; font-size:10px; letter-spacing:0.3px; }
    .ord-history-list .hi .detail { color:#444; }
    .ord-history-list .hi .by { color:#888; font-size:11px; margin-left:6px; }

    /* Bill placeholder */
    .ord-bill-slot { background:linear-gradient(180deg, #f9fbff 0%, #fff 100%); border:1px dashed #c5d3e6; border-radius:6px; padding:24px; text-align:center; color:#5b6b80; }
    .ord-bill-slot i { font-size:36px; color:#c5d3e6; margin-bottom:8px; }
    .ord-bill-slot .sub { font-size:12px; color:#888; }

    /* Modal (shared with index) */
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
    .ord-modal .btn-danger { background:#c0392b; color:#fff; border:1px solid #c0392b; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    <div class="ord-show-head">
        <div>
            <a href="{{ route('admin.orders.index') }}" style="font-size:12px; color:#3a7bd5; text-decoration:none;"><i class="fas fa-arrow-left"></i> All orders</a>
            <h2>Order {{ $order->order_code }}</h2>
            <div class="meta">
                Placed on <strong>{{ $order->created_at->format('d M Y, h:i A') }}</strong>
                &middot; {{ $order->items->count() }} {{ \Illuminate\Support\Str::plural('item', $order->items->count()) }}
                &middot; UTR: <strong>{{ $order->payment_utr ?: '—' }}</strong>
            </div>
            <div style="margin-top:6px; display:flex; gap:6px; flex-wrap:wrap;">
                <span class="status-pill" style="{{ $rowStyle }}">{{ $order->status }}</span>
                <span class="pay-pill" style="{{ $payStyle }}">Payment: {{ $order->payment_status }}</span>
            </div>
        </div>
        <div class="right">
            <div class="total">₹{{ number_format($order->total, 2) }}</div>
            <div class="meta" style="font-size:12px;">{{ strtoupper($order->payment_method) }}{{ $order->payment_gateway ? ' · '.ucfirst($order->payment_gateway) : '' }}</div>

            <div style="display:flex; gap:8px; align-items:center; justify-content:flex-end; margin-top:8px; flex-wrap:wrap;">
                <a href="{{ route('admin.invoices.from-order', $order->order_code) }}" class="btn btn-sm" style="background:#2563eb; color:#fff; font-weight:600; padding:7px 12px; border-radius:6px; text-decoration:none; display:inline-flex; align-items:center; gap:6px;">
                    <i class="fas fa-file-invoice"></i> Manage Invoice
                </a>

                <div class="ord-actions dropdown">
                    <button type="button" class="btn-actions dropdown-toggle" data-toggle="dropdown">
                        <i class="fas fa-cog"></i> Actions <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                        @if(in_array($order->status, ['placed', 'accepted', 'dispatched']))
                            <li class="action-section">Set status</li>
                            @if($order->status === 'placed')
                                <li>
                                    <a onclick="openStatusModal('accepted')"><i class="fas fa-arrow-right"></i> Accepted</a>
                                </li>
                            @elseif($order->status === 'accepted')
                                <li>
                                    <a onclick="openStatusModal('dispatched')"><i class="fas fa-arrow-right"></i> Dispatched</a>
                                </li>
                                <li>
                                    <a onclick="openDispatchModal()"><i class="fas fa-truck"></i> Dispatch with courier…</a>
                                </li>
                            @elseif($order->status === 'dispatched')
                                <li>
                                    <a onclick="openStatusModal('delivered')"><i class="fas fa-arrow-right"></i> Delivered</a>
                                </li>
                            @endif
                            <li class="divider"></li>
                        @endif
                        <li class="action-section">Money</li>
                        @if($order->payment_status !== 'paid')
                            <li>
                                <a onclick="openMarkPaidModal()"><i class="fas fa-money-bill-wave"></i> Mark as paid</a>
                            </li>
                        @else
                            <li class="disabled"><a style="color:#888; cursor:default;"><i class="fas fa-check"></i> Already paid</a></li>
                        @endif
                        <li>
                            <a onclick="openRefundModal({{ (float) $order->total }})"><i class="fas fa-undo"></i> Refund…</a>
                        </li>
                        <li class="divider"></li>
                        <li class="action-section">Invoices & Templates</li>
                        <li>
                            <a href="{{ route('admin.invoices.from-order', $order->order_code) }}" style="color:#2563eb; font-weight:600;"><i class="fas fa-file-invoice"></i> Open in Invoice Manager</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.templates.index') }}"><i class="fas fa-swatchbook"></i> Switch Invoice Template</a>
                        </li>
                        <li>
                            <a href="{{ route('admin.orders.invoice', ['order' => $order->order_code, 'download' => 1]) }}"><i class="fas fa-download"></i> Quick Download Bill</a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a onclick="openCancelModal()" style="color:#c0392b;"><i class="fas fa-times" style="color:#c0392b;"></i> Cancel order</a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="{{ app(\App\Services\WhatsAppService::class)->getShareUrl($order) }}" target="_blank" style="color:#25d366; font-weight:600;"><i class="fab fa-whatsapp" style="color:#25d366;"></i> WhatsApp Customer</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="ord-pay-callout" style="background:#f0fdf4;border-color:#bbf7d0;color:#166534;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Payment completed callout --}}
    @if($order->payment_status === 'paid')
        <div class="ord-pay-callout">
            <i class="fas fa-check-circle"></i>
            <strong>Payment completed</strong>
            &middot; <span class="when">{{ $order->paid_at?->format('d M Y, h:i A') ?? '—' }}</span>
            &middot; {{ strtoupper($order->payment_method) }}{{ $order->payment_gateway ? ' / '.ucfirst($order->payment_gateway) : '' }}
            @if($order->payment_utr) &middot; UTR <strong>{{ $order->payment_utr }}</strong> @endif
            @if($order->marked_paid_at && $order->marked_paid_by)
                &middot; <em>Manually marked by {{ $order->markedPaidBy?->name ?? 'admin' }} on {{ $order->marked_paid_at->format('d M, h:i A') }}</em>
            @endif
        </div>
    @elseif(in_array($order->payment_status, ['failed', 'pending']))
        <div class="ord-pay-callout" style="background:#fff0f0;border-color:#f5c2c2;color:#a93226;">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Payment not completed ({{ ucfirst($order->payment_status) }})</strong>
            &middot; You can manually mark this order as paid (cash / offline UPI / gateway recovery) using the <em>Mark as paid</em> action.
        </div>
    @endif

    <div class="row">
        <div class="col-md-7">

            {{-- Items --}}
            <div class="panel">
                <div class="panel-head"><i class="fas fa-box"></i> Items in this order</div>
                <div class="panel-body" style="padding:0;">
                    <table class="ord-items-table">
                        <thead>
                            <tr>
                                <th class="img-cell"></th>
                                <th>Product</th>
                                <th style="width:120px;">Qty × Price</th>
                                <th style="width:90px; text-align:right;">Line total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $it)
                                <tr>
                                    <td class="img-cell">
                                        @if($it->product_image)
                                            <img src="{{ $it->product_image }}" alt="">
                                        @else
                                            <div class="ph"><i class="fas fa-image"></i></div>
                                        @endif
                                    </td>
                                    <td>
                                        <div style="font-weight:600; color:#222;">{{ $it->product_name }}</div>
                                        <div class="provider">
                                            <i class="fas fa-long-arrow-alt-right"></i>
                                            <strong>{{ $it->product_name }}</strong> &rarr; {{ $it->provider_names ? implode(', ', $it->provider_names) : '—' }}
                                        </div>
                                    </td>
                                    <td>{{ $it->quantity }} × ₹{{ number_format($it->unit_price, 2) }}</td>
                                    <td style="text-align:right; font-weight:700; color:#c7511f;">₹{{ number_format($it->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="ord-totals" style="margin:0 16px 16px;">
                        <div class="row"><span>Subtotal</span><span>₹{{ number_format($order->subtotal, 2) }}</span></div>
                        @if($order->discount > 0)
                            <div class="row" style="color:#007600;"><span>Discount @if($order->coupon_code) <small style="color:#888;">(Coupon {{ $order->coupon_code }})</small> @endif</span><span>−₹{{ number_format($order->discount, 2) }}</span></div>
                        @endif
                        <div class="row"><span>Shipping</span><span style="color:#007600;">FREE</span></div>
                        <div class="row tot"><span>Total paid</span><span>₹{{ number_format($order->total, 2) }}</span></div>
                        @if($order->refunded_amount)
                            <div class="row" style="color:#c0392b;"><span>Refunded</span><span>−₹{{ number_format($order->refunded_amount, 2) }}{{ $order->refund_reference ? ' ('.$order->refund_reference.')' : '' }}</span></div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Status timeline --}}
            <div class="panel">
                <div class="panel-head"><i class="fas fa-stream"></i> Status timeline</div>
                <div class="panel-body">
                    @php
                        $order_steps = ['placed' => 'Order placed', 'accepted' => 'Accepted', 'dispatched' => 'Dispatched', 'delivered' => 'Delivered'];
                        if ($order->status === 'cancelled') { $currentIdx = 0; }
                        elseif ($order->status === 'refunded') { $currentIdx = array_search('delivered', array_keys($order_steps)); }
                        else { $currentIdx = array_search($order->status, array_keys($order_steps)); }
                        if ($currentIdx === false) $currentIdx = 0;
                    @endphp
                    <div class="ord-timeline">
                        @foreach($order_steps as $key => $label)
                            @php
                                $cls = '';
                                if ($key === $order->status) $cls = 'active';
                                elseif (array_search($key, array_keys($order_steps)) < $currentIdx) $cls = 'done';
                            @endphp
                            <div class="ord-tl-step {{ $cls }}">
                                <div class="lbl">
                                    {{ $label }}
                                </div>
                                @if($key === 'placed' && $order->created_at)
                                    <div class="when">
                                        <i class="far fa-clock" style="margin-right:4px;"></i>
                                        {{ $order->created_at->format('d M Y, h:i A') }}
                                    </div>
                                @elseif($key === 'dispatched' && $order->dispatched_at)
                                    <div class="when">
                                        <i class="far fa-clock" style="margin-right:4px;"></i>
                                        {{ $order->dispatched_at->format('d M Y, h:i A') }}
                                        @if($order->dispatched_via) <span style="margin-left:6px;">· via {{ $order->dispatched_via }}</span> @endif
                                        @if($order->tracking_number) <span style="margin-left:6px;">· tracking <strong>{{ $order->tracking_number }}</strong></span> @endif
                                    </div>
                                @elseif($cls === 'active' && $key === 'accepted')
                                    <div class="when"><i class="far fa-clock" style="margin-right:4px;"></i>In progress</div>
                                @elseif($cls === 'active' && $key === 'delivered')
                                    <div class="when"><i class="far fa-clock" style="margin-right:4px;"></i>Out for delivery</div>
                                @endif
                            </div>
                        @endforeach

                        {{-- Cancelled/Refunded as terminal --}}
                        @if($order->status === 'cancelled')
                            <div class="ord-tl-step active">
                                <div class="lbl">Cancelled</div>
                                <div class="when"><i class="far fa-clock" style="margin-right:4px;"></i>{{ $order->cancelled_at?->format('d M Y, h:i A') }}</div>
                                @if($order->cancelled_reason) <div class="note">{{ $order->cancelled_reason }}</div> @endif
                            </div>
                        @elseif($order->status === 'refunded')
                            <div class="ord-tl-step active">
                                <div class="lbl">Refunded</div>
                                <div class="when"><i class="far fa-clock" style="margin-right:4px;"></i>{{ $order->refunded_at?->format('d M Y, h:i A') }} @if($order->refund_reference) · ref {{ $order->refund_reference }} @endif</div>
                                <div class="note">Amount refunded: ₹{{ number_format($order->refunded_amount, 2) }}</div>
                            </div>
                        @endif
                    </div>

                    {{-- Custom statuses (admin-typed) --}}
                    <div style="margin-top:14px;">
                        <div style="font-size:12px; font-weight:700; color:#555; text-transform:uppercase; letter-spacing:0.3px; margin-bottom:8px;">Custom status updates</div>
                        <div class="ord-cs">
                            @forelse($order->custom_statuses ?? [] as $idx => $cs)
                                <div class="ord-cs-item">
                                    <div class="body">
                                        <div class="msg">{{ $cs['message'] }}</div>
                                        <div class="who">
                                            {{ \Illuminate\Support\Carbon::parse($cs['at'])->format('d M, h:i A') }}
                                            @if(!empty($cs['by'])) &middot; by {{ $cs['by'] }} @endif
                                        </div>
                                    </div>
                                    <form method="post" action="{{ route('admin.orders.custom-status.remove', ['order' => $order->order_code]) }}" onsubmit="return confirm('Remove this status update?')">
                                        @csrf
                                        <input type="hidden" name="index" value="{{ $idx }}">
                                        <button class="rm" title="Remove"><i class="fas fa-times"></i></button>
                                    </form>
                                </div>
                            @empty
                                <div style="font-size:12px; color:#888; font-style:italic;">None yet — add the first one below.</div>
                            @endforelse
                        </div>

                        <form class="ord-cs-form" method="post" action="{{ route('admin.orders.custom-status', ['order' => $order->order_code]) }}">
                            @csrf
                            <input type="text" name="message" maxlength="500" placeholder="e.g. Out for delivery — ETA 30 mins" required>
                            <button type="submit">Add</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Status history audit trail --}}
            <div class="panel">
                <div class="panel-head"><i class="fas fa-history"></i> Status history</div>
                <div class="panel-body">
                    <div class="ord-history-list">
                        @forelse($order->status_history ?? [] as $h)
                            <div class="hi">
                                <span class="event">{{ $h['event'] }}</span>
                                <span class="detail">{{ $h['detail'] }}</span>
                                <span class="by">— {{ $h['by'] ?? 'system' }} · {{ \Illuminate\Support\Carbon::parse($h['at'])->format('d M Y, h:i A') }}</span>
                            </div>
                        @empty
                            <div style="font-size:12px; color:#888; font-style:italic;">No changes yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Bill placeholder (will use the template you send later) --}}
            <div class="panel">
                <div class="panel-head"><i class="fas fa-file-invoice"></i> Bill / Invoice</div>
                <div class="panel-body">
                    <div class="ord-bill-slot">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <div style="font-weight:600; color:#3a7bd5;">Bill will render here</div>
                        <div class="sub"><a href="{{ route('admin.orders.invoice', ['order' => $order->order_code, 'download' => 1]) }}"><i class="fas fa-download"></i> Download the bill</a> &nbsp;·&nbsp; <a href="{{ route('admin.orders.invoice', ['order' => $order->order_code]) }}">preview in browser</a></div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-md-5">

            {{-- Customer --}}
            <div class="panel">
                <div class="panel-head"><i class="fas fa-user"></i> Customer</div>
                <div class="panel-body">
                    <div class="ord-info-row"><span class="lbl">Name</span><span class="val">{{ $order->contact_name ?: '—' }}</span></div>
                    <div class="ord-info-row"><span class="lbl">Mobile</span><span class="val">{{ $order->contact_mobile ?: '—' }}</span></div>
                    @if($order->contact_email)
                        <div class="ord-info-row"><span class="lbl">Email</span><span class="val">{{ $order->contact_email }}</span></div>
                    @endif
                    @if($order->customer)
                        <div class="ord-info-row"><span class="lbl">Account</span><span class="val">#{{ $order->customer->id }} · {{ $order->customer->name ?? $order->customer->email ?? '—' }}</span></div>
                    @endif
                </div>
            </div>

            {{-- Address --}}
            <div class="panel">
                <div class="panel-head"><i class="fas fa-map-marker-alt"></i> Shipping address</div>
                <div class="panel-body">
                    <div class="ord-info-row"><span class="lbl">Recipient</span><span class="val">{{ $order->addr_full_name }} <span style="color:#888;">({{ ucfirst($order->addr_type) }})</span></span></div>
                    <div class="ord-info-row"><span class="lbl">Address</span><span class="val">{{ $order->addr_line_1 }}{{ $order->addr_line_2 ? ', '.$order->addr_line_2 : '' }}<br>{{ $order->addr_city }}, {{ $order->addr_state }} — {{ $order->addr_pincode }}</span></div>
                    <div class="ord-info-row"><span class="lbl">Mobile</span><span class="val">{{ $order->addr_mobile_primary }}{{ $order->addr_mobile_alternate ? ' / '.$order->addr_mobile_alternate : '' }}</span></div>
                </div>
            </div>

            {{-- Payment --}}
            <div class="panel">
                <div class="panel-head"><i class="fas fa-credit-card"></i> Payment</div>
                <div class="panel-body">
                    <div class="ord-info-row"><span class="lbl">Method</span><span class="val">{{ ucfirst($order->payment_method) }}</span></div>
                    <div class="ord-info-row"><span class="lbl">Gateway</span><span class="val">{{ $order->payment_gateway ? ucfirst($order->payment_gateway) : '—' }}</span></div>
                    <div class="ord-info-row"><span class="lbl">Status</span><span class="val"><span class="pay-pill" style="{{ $payStyle }}">{{ $order->payment_status }}</span></span></div>
                    <div class="ord-info-row"><span class="lbl">UTR</span><span class="val">{{ $order->payment_utr ?: '—' }}</span></div>
                    <div class="ord-info-row"><span class="lbl">Gateway ref</span><span class="val">{{ $order->payment_order_id ?: '—' }}</span></div>
                    <div class="ord-info-row"><span class="lbl">Paid at</span><span class="val">{{ $order->paid_at?->format('d M Y, h:i A') ?? '—' }}</span></div>
                    @if($order->marked_paid_at)
                        <div class="ord-info-row"><span class="lbl">Manually marked</span><span class="val">{{ $order->marked_paid_at->format('d M Y, h:i A') }} by {{ $order->markedPaidBy?->name ?? 'admin' }}</span></div>
                    @endif
                </div>
            </div>

            @if($order->cancelled_reason)
                <div class="panel">
                    <div class="panel-head"><i class="fas fa-times-circle" style="color:#c0392b;"></i> Cancellation</div>
                    <div class="panel-body">
                        <div class="ord-info-row"><span class="lbl">Reason</span><span class="val">{{ $order->cancelled_reason }}</span></div>
                        <div class="ord-info-row"><span class="lbl">Cancelled at</span><span class="val">{{ $order->cancelled_at?->format('d M Y, h:i A') ?? '—' }}</span></div>
                    </div>
                </div>
            @endif

            @if($order->refunded_at)
                <div class="panel">
                    <div class="panel-head"><i class="fas fa-undo" style="color:#6c757d;"></i> Refund</div>
                    <div class="panel-body">
                        <div class="ord-info-row"><span class="lbl">Amount</span><span class="val">₹{{ number_format($order->refunded_amount, 2) }}</span></div>
                        <div class="ord-info-row"><span class="lbl">Reference</span><span class="val">{{ $order->refund_reference ?: '—' }}</span></div>
                        <div class="ord-info-row"><span class="lbl">Refunded at</span><span class="val">{{ $order->refunded_at->format('d M Y, h:i A') }}</span></div>
                    </div>
                </div>
            @endif

        </div>
    </div>

</div>

{{-- Modals --}}
<div class="ord-modal-backdrop" id="ordBackdrop"></div>

<form class="ord-modal" id="ordStatusModal" method="post" action="{{ route('admin.orders.status', ['order' => $order->order_code]) }}">
    @csrf
    <div class="m-head">
        <h3 id="ordStatusTitle">Set status</h3>
        <button type="button" class="x" onclick="closeModals()">×</button>
    </div>
    <div class="m-body">
        <input type="hidden" name="status" id="ordStatusValue">
        <label>Note (optional)</label>
        <textarea name="note" placeholder="Internal note — courier, ETA, anything relevant…"></textarea>
    </div>
    <div class="m-foot">
        <button type="button" class="btn btn-secondary" onclick="closeModals()">Cancel</button>
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>

<form class="ord-modal" id="ordDispatchModal" method="post" action="{{ route('admin.orders.dispatch', ['order' => $order->order_code]) }}">
    @csrf
    <div class="m-head">
        <h3>Dispatch with courier</h3>
        <button type="button" class="x" onclick="closeModals()">×</button>
    </div>
    <div class="m-body">
        <label>Courier / via</label>
        <input type="text" name="via" maxlength="100" placeholder="e.g. DTDC, Delhivery, Local" value="{{ $order->dispatched_via }}">
        <label style="margin-top:10px;">Tracking number</label>
        <input type="text" name="tracking" maxlength="200" placeholder="e.g. DTDC1234567890" value="{{ $order->tracking_number }}">
    </div>
    <div class="m-foot">
        <button type="button" class="btn btn-secondary" onclick="closeModals()">Cancel</button>
        <button type="submit" class="btn btn-primary">Mark dispatched</button>
    </div>
</form>

<form class="ord-modal" id="ordMarkPaidModal" method="post" action="{{ route('admin.orders.mark-paid', ['order' => $order->order_code]) }}">
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
        <input type="text" name="utr" maxlength="100" placeholder="e.g. 412345678910" value="{{ $order->payment_utr }}">
    </div>
    <div class="m-foot">
        <button type="button" class="btn btn-secondary" onclick="closeModals()">Cancel</button>
        <button type="submit" class="btn btn-primary">Mark paid</button>
    </div>
</form>

<form class="ord-modal" id="ordCancelModal" method="post" action="{{ route('admin.orders.cancel', ['order' => $order->order_code]) }}">
    @csrf
    <div class="m-head">
        <h3>Cancel order</h3>
        <button type="button" class="x" onclick="closeModals()">×</button>
    </div>
    <div class="m-body">
        <label>Reason (visible in history)</label>
        <textarea name="reason" maxlength="500" placeholder="Why is this being cancelled?" required>{{ $order->cancelled_reason }}</textarea>
    </div>
    <div class="m-foot">
        <button type="button" class="btn btn-secondary" onclick="closeModals()">Keep</button>
        <button type="submit" class="btn btn-danger">Cancel order</button>
    </div>
</form>

<form class="ord-modal" id="ordRefundModal" method="post" action="{{ route('admin.orders.refund', ['order' => $order->order_code]) }}">
    @csrf
    <div class="m-head">
        <h3>Refund</h3>
        <button type="button" class="x" onclick="closeModals()">×</button>
    </div>
    <div class="m-body">
        <label>Amount (₹)</label>
        <input type="number" step="0.01" min="0" name="amount" id="ordRefundAmount" required>
        <label style="margin-top:10px;">Reference (optional)</label>
        <input type="text" name="reference" maxlength="100" placeholder="e.g. REF-2026-0001" value="{{ $order->refund_reference }}">
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
    function openModal(m) { backdrop.classList.add('show'); m.classList.add('show'); }
    function closeModals() { backdrop.classList.remove('show'); document.querySelectorAll('.ord-modal').forEach(m => m.classList.remove('show')); }
    backdrop.addEventListener('click', closeModals);

    function openStatusModal(status) {
        const m = document.getElementById('ordStatusModal');
        document.getElementById('ordStatusValue').value = status;
        document.getElementById('ordStatusTitle').textContent = 'Mark as ' + status;
        openModal(m);
    }
    function openDispatchModal() { openModal(document.getElementById('ordDispatchModal')); }
    function openMarkPaidModal()   { openModal(document.getElementById('ordMarkPaidModal')); }
    function openCancelModal()    { openModal(document.getElementById('ordCancelModal')); }
    function openRefundModal(total) {
        document.getElementById('ordRefundAmount').value = total.toFixed(2);
        openModal(document.getElementById('ordRefundModal'));
    }
</script>
@endpush

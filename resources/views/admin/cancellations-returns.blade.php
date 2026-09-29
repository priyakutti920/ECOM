@extends('layouts.admin')

@section('title', 'Cancellations & Returns')

@push('styles')
<style>
.kpi-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 18px; }
.kpi { background: #fff; border: 1px solid #e7e7e7; border-radius: 8px; padding: 16px 18px; display: flex; align-items: center; gap: 14px; }
.kpi .icon { width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; }
.kpi.c1 .icon { background: #fdecea; color: #a93226; }
.kpi.c2 .icon { background: #fff7e6; color: #946a00; }
.kpi.c3 .icon { background: #e8f5e9; color: #1a7a42; }
.kpi .num { font-size: 22px; font-weight: 700; color: #1a1a2e; }
.kpi .lbl { font-size: 12px; color: #777; text-transform: uppercase; letter-spacing: 0.5px; }

.tabs { display: flex; gap: 4px; border-bottom: 2px solid #e7e7e7; margin-bottom: 14px; }
.tabs a { padding: 10px 18px; font-size: 13px; font-weight: 600; color: #555; text-decoration: none; border-bottom: 2px solid transparent; margin-bottom: -2px; }
.tabs a.active { color: #007185; border-color: #007185; }
.tabs a .count { background: #f0f0f0; color: #555; padding: 1px 8px; border-radius: 100px; font-size: 11px; margin-left: 4px; }
.tabs a.active .count { background: #e3f2fd; color: #0a4b87; }

.toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; gap: 10px; flex-wrap: wrap; }
.toolbar form { display: flex; gap: 6px; }
.toolbar input[type=text], .toolbar select { padding: 7px 10px; border: 1px solid #d5d9d9; border-radius: 6px; font-size: 13px; }
.toolbar input[type=text] { min-width: 240px; }
.toolbar button { padding: 7px 14px; background: #3a7bd5; color: #fff; border: none; border-radius: 6px; font-size: 13px; cursor: pointer; }

table.data { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #e7e7e7; border-radius: 8px; overflow: hidden; }
table.data th { background: #f6f8fa; text-align: left; font-size: 12px; color: #555; padding: 10px 12px; border-bottom: 1px solid #e7e7e7; }
table.data td { padding: 10px 12px; border-bottom: 1px solid #f0f0f0; font-size: 13px; vertical-align: top; }
table.data tr:hover td { background: #fafbfc; }

.tag { display: inline-block; padding: 2px 8px; border-radius: 100px; font-size: 11px; font-weight: 600; }
.tag.cancelled { background: #f8d7da; color: #721c24; }
.tag.requested        { background: #fff8e1; color: #946a00; }
.tag.accepted         { background: #e3f2fd; color: #0a4b6e; }
.tag.pickup_scheduled { background: #e0f2f1; color: #00695c; }
.tag.picked_up        { background: #d4edda; color: #155724; }
.tag.completed        { background: #d4edda; color: #155724; }
.tag.rejected         { background: #f8d7da; color: #721c24; }

.reason-line { font-size: 12px; color: #444; margin-top: 4px; }
.thumb { width: 40px; height: 40px; border-radius: 4px; object-fit: cover; background: #f0f0f0; }
.thumb-ph { width: 40px; height: 40px; border-radius: 4px; background: #f0f0f0; display:flex; align-items:center; justify-content:center; color:#bbb; font-size:14px; }

.status-form { display: flex; gap: 4px; align-items: center; }
.status-form select { padding: 5px 7px; border: 1px solid #d5d9d9; border-radius: 4px; font-size: 12px; background: #fff; }
.status-form button { padding: 5px 10px; background: #007185; color: #fff; border: none; border-radius: 4px; font-size: 12px; cursor: pointer; }
.status-form button:hover { background: #005f6b; }

.empty { padding: 30px; text-align: center; color: #999; }
.section-title { font-size: 14px; font-weight: 700; color: #1a1a2e; margin: 0 0 10px; padding-bottom: 6px; border-bottom: 1px solid #f0f0f0; }
.note-input { width: 100%; border: 1px solid #d5d9d9; border-radius: 4px; padding: 5px 7px; font-size: 12px; }
</style>
@endpush

@section('content')
<div style="max-width: 1280px; margin: 0 auto;">

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

    <table class="data">
        <thead>
            <tr>
                <th>Order</th>
                <th>Customer</th>
                <th>Cancelled on</th>
                <th>Reason</th>
                <th>Total</th>
                <th>Payment</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @forelse($cancellations as $o)
            <tr>
                <td>
                    <a href="{{ route('admin.orders.show', $o->order_code) }}" style="color:#007185; font-weight:600; text-decoration:none;">{{ $o->order_code }}</a>
                </td>
                <td>
                    {{ $o->contact_name }}<br>
                    <small style="color:#888;">{{ $o->contact_mobile }}</small>
                </td>
                <td>
                    <small>{{ $o->cancelled_at ? $o->cancelled_at->format('d M Y, h:i A') : '—' }}</small>
                </td>
                <td style="max-width:320px;">
                    <div class="reason-line">{{ $o->cancelled_reason ?: '—' }}</div>
                </td>
                <td>₹{{ number_format($o->total, 0) }}</td>
                <td><span class="tag {{ $o->payment_status }}">{{ ucfirst($o->payment_status) }}</span></td>
                <td>
                    <a href="{{ route('admin.orders.show', $o->order_code) }}" class="btn-secondary" style="font-size:11.5px; padding:4px 10px; border:1px solid #d5d9d9; border-radius:4px; color:#333; text-decoration:none;">Open</a>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="empty">No cancellations yet.</td></tr>
        @endforelse
        </tbody>
    </table>
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

    <table class="data">
        <thead>
            <tr>
                <th>Product</th>
                <th>Order</th>
                <th>Customer</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Requested</th>
                <th>Manage</th>
            </tr>
        </thead>
        <tbody>
        @forelse($returns as $r)
            @php $order = $r->order; @endphp
            <tr>
                <td>
                    <div style="display:flex; gap:8px; align-items:center;">
                        @if($r->image_path)
                            <img src="{{ asset('storage/' . $r->image_path) }}" class="thumb">
                        @elseif($r->product && $r->product->image)
                            <img src="{{ $r->product->image }}" class="thumb">
                        @else
                            <div class="thumb-ph"><i class="fas fa-image"></i></div>
                        @endif
                        <div>
                            <div style="font-weight:600;">{{ $r->product->name ?? ($order->items->firstWhere('id', $r->order_item_id)->product_name ?? '—') }}</div>
                            <small style="color:#888;">₹{{ number_format($r->product->price ?? 0, 0) }}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <a href="{{ $order ? route('admin.orders.show', $order->order_code) : '#' }}" style="color:#007185; font-weight:600; text-decoration:none;">{{ $order->order_code ?? '—' }}</a>
                </td>
                <td>
                    {{ $r->customer->name ?? '—' }}<br>
                    <small style="color:#888;">{{ $r->customer->email ?? '' }}</small>
                </td>
                <td style="max-width:280px;">
                    <div class="reason-line"><strong>{{ $r->reason }}</strong></div>
                    @if($r->description)
                        <div class="reason-line" style="color:#666; margin-top:2px;">{{ $r->description }}</div>
                    @endif
                </td>
                <td><span class="tag {{ $r->status }}">{{ $r->status_label }}</span></td>
                <td><small>{{ $r->requested_at ? $r->requested_at->format('d M Y, h:i A') : '—' }}</small></td>
                <td>
                    <form method="POST" action="{{ route('admin.returns.status', $r) }}" class="status-form" style="margin-bottom:6px;">
                        @csrf
                        <select name="status">
                            @foreach(\App\Models\OrderReturn::STATUSES as $key => $label)
                                <option value="{{ $key }}" {{ $r->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <button type="submit">Save</button>
                    </form>
                    <form method="POST" action="{{ route('admin.returns.note', $r) }}" class="status-form">
                        @csrf
                        <input type="text" name="admin_note" class="note-input" placeholder="Add admin note" value="{{ $r->admin_note }}">
                        <button type="submit" style="background:#888;">Note</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="empty">No return requests.</td></tr>
        @endforelse
        </tbody>
    </table>
    <div style="margin-top:10px;">{{ $returns->appends(request()->query())->links() }}</div>
    @endif

</div>
@endsection

@extends('layouts.admin')

@section('title', 'Support Inbox')

@push('styles')
<style>
.kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 18px; }
.kpi { background: #fff; border: 1px solid #e7e7e7; border-radius: 8px; padding: 14px 18px; display: flex; align-items: center; gap: 12px; }
.kpi .icon { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
.kpi.c1 .icon { background: #fff8e1; color: #946a00; }
.kpi.c2 .icon { background: #e3f2fd; color: #0a4b6e; }
.kpi.c3 .icon { background: #e2e3e5; color: #383d41; }
.kpi.c4 .icon { background: #e8f5e9; color: #1a7a42; }
.kpi .num { font-size: 22px; font-weight: 700; color: #1a1a2e; }
.kpi .lbl { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 0.4px; }

@media (max-width: 900px) {
    .kpi-row { grid-template-columns: repeat(2, 1fr); gap: 10px; }
}
@media (max-width: 480px) {
    .kpi-row { grid-template-columns: 1fr; gap: 8px; }
    .kpi { padding: 12px 14px; }
}

.toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; gap: 10px; flex-wrap: wrap; }
.toolbar form { display: flex; gap: 6px; flex-wrap: wrap; width: 100%; }
.toolbar input[type=text], .toolbar select { padding: 7px 10px; border: 1px solid #d5d9d9; border-radius: 6px; font-size: 13px; }
.toolbar input[type=text] { flex: 1 1 240px; min-width: 0; }
.toolbar select { flex: 0 1 auto; }
.toolbar button { padding: 7px 14px; background: #3a7bd5; color: #fff; border: none; border-radius: 6px; font-size: 13px; cursor: pointer; white-space: nowrap; }

@media (max-width: 600px) {
    .toolbar form { flex-direction: column; align-items: stretch; }
    .toolbar input[type=text],
    .toolbar select,
    .toolbar button { width: 100%; box-sizing: border-box; }
    .toolbar button { justify-content: center; text-align: center; }
}

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
    border-bottom: 1px solid #e7e7e7;
    align-items: center;
    gap: 6px;
}
@media (max-width: 900px) {
    .mobile-scroll-hint { display: flex; }
}

table.data { width: 100%; min-width: 720px; border-collapse: collapse; background: #fff; margin: 0; }
table.data th { background: #f6f8fa; text-align: left; font-size: 12px; color: #555; padding: 10px 12px; border-bottom: 1px solid #e7e7e7; touch-action: pan-x pan-y !important; }
table.data td { padding: 10px 12px; border-bottom: 1px solid #f0f0f0; font-size: 13px; vertical-align: middle; touch-action: pan-x pan-y !important; }
table.data tr:hover td { background: #fafbfc; }

.col-ticket   { min-width: 75px; white-space: nowrap; }
.col-subject  { min-width: 240px; word-break: break-word; overflow-wrap: anywhere; }
.col-customer { min-width: 170px; word-break: break-word; overflow-wrap: anywhere; }
.col-status   { min-width: 120px; white-space: nowrap; }
.col-activity { min-width: 125px; white-space: nowrap; }
.col-action   { min-width: 85px; text-align: right; white-space: nowrap; }

.tk-id { font-weight: 700; color: #007185; text-decoration: none; word-break: break-word; overflow-wrap: anywhere; }
.tk-id:hover { text-decoration: underline; }
.preview { color: #666; font-size: 12px; margin-top: 2px; max-width: 480px; word-break: break-word; overflow-wrap: anywhere; }

.status-pill { display: inline-block; padding: 2px 8px; border-radius: 100px; font-size: 10.5px; font-weight: 700; text-transform: uppercase; }
.status-open                { background: #fff8e1; color: #946a00; }
.status-awaiting_admin      { background: #e3f2fd; color: #0a4b6e; }
.status-awaiting_customer   { background: #fce4ec; color: #880e4f; }
.status-closed              { background: #e2e3e5; color: #383d41; }

.cat-pill { display: inline-block; padding: 1px 6px; border-radius: 4px; font-size: 10.5px; font-weight: 600; background: #f0f0f0; color: #555; }

.btn-open-ticket {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 11px;
    background: #f0f7ff;
    color: #007185;
    border: 1px solid #cce4f7;
    border-radius: 5px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.15s;
    white-space: nowrap;
}
.btn-open-ticket:hover {
    background: #007185;
    color: #fff;
    border-color: #007185;
}

.empty { padding: 30px; text-align: center; color: #999; }
</style>
@endpush

@section('content')
<div style="max-width: 1280px; margin: 0 auto; width: 100%; min-width: 0;">

 <h2 style="margin: 0 0 14px; font-size: 22px; color: #1a1a2e;">
 <i class="fas fa-headset" style="color:#3a7bd5;"></i> Support Inbox
 </h2>

 @if(session('success'))
 <div style="background:#d4edda; color:#155724; padding:10px 14px; border-radius:6px; margin-bottom:14px; font-size:13px;"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
 @endif
 @if(session('error'))
 <div style="background:#f8d7da; color:#721c24; padding:10px 14px; border-radius:6px; margin-bottom:14px; font-size:13px;"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</div>
 @endif

 <div class="kpi-row">
 <div class="kpi c1"><div class="icon"><i class="fas fa-inbox"></i></div><div><div class="num">{{ $stats['open'] }}</div><div class="lbl">Open</div></div></div>
 <div class="kpi c2"><div class="icon"><i class="fas fa-bell"></i></div><div><div class="num">{{ $stats['awaiting'] }}</div><div class="lbl">Awaiting you</div></div></div>
 <div class="kpi c3"><div class="icon"><i class="fas fa-check-circle"></i></div><div><div class="num">{{ $stats['closed'] }}</div><div class="lbl">Closed</div></div></div>
 <div class="kpi c4"><div class="icon"><i class="fas fa-ticket-alt"></i></div><div><div class="num">{{ $stats['all'] }}</div><div class="lbl">All tickets</div></div></div>
 </div>

 <div class="toolbar">
 <form method="GET" action="{{ route('admin.support.index') }}">
 <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search subject, order, customer…">
 <select name="status">
 <option value="">All statuses</option>
 @foreach(\App\Models\SupportTicket::STATUSES as $key => $label)
 <option value="{{ $key }}" {{ ($filters['status'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
 @endforeach
 </select>
 <button><i class="fas fa-filter"></i> Filter</button>
 </form>
 </div>

 <div class="table-responsive">
     <div class="mobile-scroll-hint">
         <i class="fas fa-arrows-alt-h"></i> Scroll right to view subject, customer details, and activity &rarr;
     </div>
     <table class="data">
     <thead>
     <tr>
     <th class="col-ticket">Ticket</th>
     <th class="col-subject">Subject</th>
     <th class="col-customer">Customer</th>
     <th class="col-status">Status</th>
     <th class="col-activity">Last activity</th>
     <th class="col-action">Action</th>
     </tr>
     </thead>
     <tbody>
     @forelse($tickets as $t)
     <tr class="ticket-row" data-href="{{ route('admin.support.show', $t) }}" style="cursor:pointer;">
     <td class="col-ticket"><a class="tk-id" href="{{ route('admin.support.show', $t) }}">#{{ $t->id }}</a></td>
     <td class="col-subject">
     <a class="tk-id" href="{{ route('admin.support.show', $t) }}" style="font-weight:600; font-size:13.5px;">{{ $t->subject }}</a>
     <div class="preview">
     <span class="cat-pill">{{ $t->category_label }}</span>
     @if($t->order_code) · Order <strong>{{ $t->order_code }}</strong> @endif
     @if($t->lastMessage) — {{ \Illuminate\Support\Str::limit($t->lastMessage->message, 100) }} @endif
     </div>
     </td>
     <td class="col-customer">
     <div style="font-weight:600;">{{ $t->customer->name ?? '—' }}</div>
     <div style="color:#888; font-size:11.5px; word-break:break-word;">{{ $t->customer->email ?? '' }}</div>
     </td>
     <td class="col-status"><span class="status-pill status-{{ $t->status }}">{{ $t->status_label }}</span></td>
     <td class="col-activity"><small>{{ $t->last_message_at ? $t->last_message_at->diffForHumans() : $t->created_at->diffForHumans() }}</small></td>
     <td class="col-action">
     <a class="btn-open-ticket" href="{{ route('admin.support.show', $t) }}">Open &rarr;</a>
     </td>
     </tr>
     @empty
     <tr><td colspan="6" class="empty">No tickets found.</td></tr>
     @endforelse
     </tbody>
     </table>
 </div>
 <div style="margin-top:10px;">{{ $tickets->appends(request()->query())->links() }}</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var rows = document.querySelectorAll('tr.ticket-row');
    rows.forEach(function (row) {
        var startX = 0, startY = 0, moved = false;
        row.addEventListener('touchstart', function (e) {
            if (e.touches && e.touches[0]) {
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
                moved = false;
            }
        }, { passive: true });
        row.addEventListener('touchmove', function (e) {
            if (e.touches && e.touches[0]) {
                if (Math.abs(e.touches[0].clientX - startX) > 8 || Math.abs(e.touches[0].clientY - startY) > 8) {
                    moved = true;
                }
            }
        }, { passive: true });
        row.addEventListener('click', function (e) {
            if (moved) {
                moved = false;
                return;
            }
            if (e.target.closest('a, button, select, input')) return;
            var url = row.getAttribute('data-href');
            if (url) window.location.href = url;
        });
    });
});
</script>
@endsection

@extends('layouts.admin')

@section('title', 'Support Inbox')

@push('styles')
<style>
.kpi-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 18px; }
.kpi { background: #fff; border: 1px solid #e7e7e7; border-radius: 8px; padding: 14px 18px; display: flex; align-items: center; gap: 12px; }
.kpi .icon { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; }
.kpi.c1 .icon { background: #fff8e1; color: #946a00; }
.kpi.c2 .icon { background: #e3f2fd; color: #0a4b6e; }
.kpi.c3 .icon { background: #e2e3e5; color: #383d41; }
.kpi.c4 .icon { background: #e8f5e9; color: #1a7a42; }
.kpi .num { font-size: 22px; font-weight: 700; color: #1a1a2e; }
.kpi .lbl { font-size: 11px; color: #888; text-transform: uppercase; letter-spacing: 0.4px; }

.toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; gap: 10px; flex-wrap: wrap; }
.toolbar form { display: flex; gap: 6px; }
.toolbar input[type=text], .toolbar select { padding: 7px 10px; border: 1px solid #d5d9d9; border-radius: 6px; font-size: 13px; }
.toolbar input[type=text] { min-width: 260px; }
.toolbar button { padding: 7px 14px; background: #3a7bd5; color: #fff; border: none; border-radius: 6px; font-size: 13px; cursor: pointer; }

table.data { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #e7e7e7; border-radius: 8px; overflow: hidden; }
table.data th { background: #f6f8fa; text-align: left; font-size: 12px; color: #555; padding: 10px 12px; border-bottom: 1px solid #e7e7e7; }
table.data td { padding: 10px 12px; border-bottom: 1px solid #f0f0f0; font-size: 13px; vertical-align: top; }
table.data tr:hover td { background: #fafbfc; }

.tk-id { font-weight: 700; color: #007185; text-decoration: none; }
.tk-id:hover { text-decoration: underline; }
.preview { color: #666; font-size: 12px; margin-top: 2px; max-width: 480px; }

.status-pill { display: inline-block; padding: 2px 8px; border-radius: 100px; font-size: 10.5px; font-weight: 700; text-transform: uppercase; }
.status-open                { background: #fff8e1; color: #946a00; }
.status-awaiting_admin      { background: #e3f2fd; color: #0a4b6e; }
.status-awaiting_customer   { background: #fce4ec; color: #880e4f; }
.status-closed              { background: #e2e3e5; color: #383d41; }

.cat-pill { display: inline-block; padding: 1px 6px; border-radius: 4px; font-size: 10.5px; font-weight: 600; background: #f0f0f0; color: #555; }

.empty { padding: 30px; text-align: center; color: #999; }
</style>
@endpush

@section('content')
<div style="max-width: 1280px; margin: 0 auto;">

 <h2 style="margin: 0 0 14px; font-size: 22px; color: #1a1a2e;">
 <i class="fas fa-headset" style="color:#3a7bd5;"></i> Support Inbox
 </h2>

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

 <table class="data">
 <thead>
 <tr>
 <th style="width:80px;">Ticket</th>
 <th>Subject</th>
 <th style="width:180px;">Customer</th>
 <th style="width:120px;">Status</th>
 <th style="width:130px;">Last activity</th>
 </tr>
 </thead>
 <tbody>
 @forelse($tickets as $t)
 <tr onclick="window.location='{{ route('admin.support.show', $t) }}'" style="cursor:pointer;">
 <td><a class="tk-id" href="{{ route('admin.support.show', $t) }}" onclick="event.stopPropagation();">#{{ $t->id }}</a></td>
 <td>
 <a class="tk-id" href="{{ route('admin.support.show', $t) }}" style="font-weight:600; font-size:13.5px;">{{ $t->subject }}</a>
 <div class="preview">
 <span class="cat-pill">{{ $t->category_label }}</span>
 @if($t->order_code) · Order <strong>{{ $t->order_code }}</strong> @endif
 @if($t->lastMessage) — {{ \Illuminate\Support\Str::limit($t->lastMessage->message, 100) }} @endif
 </div>
 </td>
 <td>
 <div style="font-weight:600;">{{ $t->customer->name ?? '—' }}</div>
 <div style="color:#888; font-size:11.5px;">{{ $t->customer->email ?? '' }}</div>
 </td>
 <td><span class="status-pill status-{{ $t->status }}">{{ $t->status_label }}</span></td>
 <td><small>{{ $t->last_message_at ? $t->last_message_at->diffForHumans() : $t->created_at->diffForHumans() }}</small></td>
 </tr>
 @empty
 <tr><td colspan="5" class="empty">No tickets found.</td></tr>
 @endforelse
 </tbody>
 </table>
 <div style="margin-top:10px;">{{ $tickets->appends(request()->query())->links() }}</div>

</div>
@endsection

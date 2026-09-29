@extends('layouts.shop')

@section('title', 'My Support Tickets — ' . $storeName)

@push('styles')
<style>
.wrap { max-width: 900px; margin: 0 auto; padding: 24px 16px 60px; }
.wrap h1 { margin: 0 0 14px; font-size: 22px; color: #0F1111; }
.toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px; flex-wrap: wrap; gap: 8px; }
.btn-new { background: #ffd814; border: 1px solid #fcd200; padding: 8px 18px; border-radius: 100px; font-size: 13px; font-weight: 600; color: #0F1111; text-decoration: none; }
.btn-new:hover { background: #f7ca00; color: #0F1111; }

.tk-card { background: #fff; border: 1px solid #e7e7e7; border-radius: 8px; padding: 14px 18px; margin-bottom: 10px; display: block; color: #0F1111; text-decoration: none; transition: box-shadow 0.15s, border-color 0.15s; }
.tk-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.06); border-color: #d5d9d9; color: #0F1111; }
.tk-card .head { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
.tk-card .head .subj { font-size: 15px; font-weight: 600; }
.tk-card .head .meta { font-size: 11.5px; color: #888; margin-top: 3px; }
.tk-card .preview { font-size: 13px; color: #555; margin-top: 6px; line-height: 1.4; }

.status-pill { display: inline-block; padding: 2px 8px; border-radius: 100px; font-size: 10.5px; font-weight: 700; text-transform: uppercase; }
.status-open { background: #fff8e1; color: #946a00; }
.status-awaiting_admin { background: #e3f2fd; color: #0a4b6e; }
.status-awaiting_customer { background: #fce4ec; color: #880e4f; }
.status-closed { background: #e2e3e5; color: #383d41; }

.empty { background: #fff; border: 1px dashed #d5d9d9; border-radius: 8px; padding: 50px 20px; text-align: center; color: #888; }
.empty i { font-size: 40px; color: #d5d9d9; margin-bottom: 10px; }
</style>
@endpush

@section('content')
<div class="wrap">
 <h1><i class="fas fa-ticket-alt" style="color: var(--amazon-orange);"></i> My Support Tickets</h1>

 <div class="toolbar">
 <span style="color:#777; font-size:13px;">{{ $tickets->total() }} {{ \Illuminate\Support\Str::plural('ticket', $tickets->total()) }}</span>
 <a href="{{ route('shop.help') }}#newTicket" class="btn-new"><i class="fas fa-plus-circle"></i> New ticket</a>
 </div>

 @if($tickets->isEmpty())
 <div class="empty">
 <i class="fas fa-inbox"></i>
 <div>No tickets yet.</div>
 <p style="font-size:13px; color:#888; margin-top:6px;">Need help? Open a ticket and our team will respond.</p>
 </div>
 @else
 @foreach($tickets as $t)
 <a class="tk-card" href="{{ route('shop.support.show', ['ticket' => $t->id]) }}" style="display:block;">
 <div class="head">
 <div>
 <div class="subj">#{{ $t->id }} · {{ $t->subject }}</div>
 <div class="meta">
 {{ $t->category_label }}
 @if($t->order_code) · Order {{ $t->order_code }} @endif
 · {{ $t->last_message_at ? $t->last_message_at->diffForHumans() : $t->created_at->diffForHumans() }}
 </div>
 </div>
 <span class="status-pill status-{{ $t->status }}">{{ $t->status_label }}</span>
 </div>
 @if($t->lastMessage)
 <div class="preview">{{ \Illuminate\Support\Str::limit($t->lastMessage->message, 140) }}</div>
 @endif
 </a>
 @endforeach

 <div style="margin-top:18px;">{{ $tickets->links() }}</div>
 @endif

</div>
@endsection

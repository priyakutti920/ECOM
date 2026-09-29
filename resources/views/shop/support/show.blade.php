@extends('layouts.shop')

@section('title', 'Ticket #' . $ticket->id . ' — ' . $storeName)

@push('styles')
<style>
.wrap { max-width: 800px; margin: 0 auto; padding: 24px 16px 60px; }
.head { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; flex-wrap: wrap; margin-bottom: 16px; }
.head h1 { margin: 0; font-size: 20px; color: #0F1111; }
.head .subj { font-size: 14px; color: #444; margin-top: 4px; }
.head .meta { font-size: 12px; color: #888; margin-top: 4px; }
.head .right { display: flex; gap: 8px; align-items: center; }

.status-pill { display: inline-block; padding: 3px 10px; border-radius: 100px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
.status-open { background: #fff8e1; color: #946a00; }
.status-awaiting_admin { background: #e3f2fd; color: #0a4b6e; }
.status-awaiting_customer { background: #fce4ec; color: #880e4f; }
.status-closed { background: #e2e3e5; color: #383d41; }

.thread { background: #fff; border: 1px solid #e7e7e7; border-radius: 8px; padding: 18px 22px; margin-bottom: 16px; }
.msg { display: flex; gap: 10px; padding: 10px 0; }
.msg + .msg { border-top: 1px dashed #f0f0f0; }
.msg .av { width: 32px; height: 32px; border-radius: 50%; flex-shrink: 0; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 12px; }
.msg.admin .av { background: #3a7bd5; }
.msg.customer .av { background: #c7511f; }
.msg .body { flex: 1; min-width: 0; }
.msg .who { font-size: 12px; font-weight: 700; color: #222; }
.msg .when { font-size: 11px; color: #888; margin-left: 6px; font-weight: 400; }
.msg .bubble { background: #f8f9fa; border-radius: 8px; padding: 10px 14px; margin-top: 5px; font-size: 13.5px; line-height: 1.5; color: #222; white-space: pre-wrap; word-wrap: break-word; }
.msg.admin .bubble { background: #e3f2fd; }
.msg.customer .bubble { background: #fff7e6; }

.reply-box { background: #fff; border: 1px solid #e7e7e7; border-radius: 8px; padding: 18px 22px; }
.reply-box label { display: block; font-size: 12px; font-weight: 600; color: #444; margin-bottom: 6px; }
.reply-box textarea { width: 100%; border: 1px solid #d5d9d9; border-radius: 4px; padding: 10px 12px; font-size: 13.5px; font-family: inherit; min-height: 90px; resize: vertical; }
.reply-box textarea:focus { outline: none; border-color: #007185; box-shadow: 0 0 0 2px rgba(0,113,133,0.10); }
.reply-box .actions { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; flex-wrap: wrap; gap: 8px; }
.btn-submit { background: #ffd814; border: 1px solid #fcd200; padding: 9px 22px; border-radius: 100px; font-size: 13px; font-weight: 600; cursor: pointer; }
.btn-submit:hover { background: #f7ca00; }
.btn-close { background: #fff; border: 1px solid #d5d9d9; color: #555; padding: 8px 16px; border-radius: 100px; font-size: 12.5px; cursor: pointer; }
.btn-close:hover { background: #f7f7f7; }
.closed-banner { background: #e2e3e5; color: #383d41; padding: 10px 16px; border-radius: 6px; font-size: 12.5px; margin-bottom: 12px; }
</style>
@endpush

@section('content')
<div class="wrap">

 <a href="{{ route('shop.support.index') }}" style="display:inline-flex; align-items:center; gap:6px; color:#0066c0; font-size:13px; text-decoration:none; margin-bottom:14px;">
 <i class="fas fa-arrow-left"></i> All tickets
 </a>

 <div class="head">
 <div>
 <h1>Ticket #{{ $ticket->id }}</h1>
 <div class="subj">{{ $ticket->subject }}</div>
 <div class="meta">
 {{ $ticket->category_label }}
 @if($ticket->order_code) · Order <strong>{{ $ticket->order_code }}</strong> @endif
 · Opened {{ $ticket->created_at->format('d M Y, h:i A') }}
 </div>
 </div>
 <div class="right">
 <span class="status-pill status-{{ $ticket->status }}">{{ $ticket->status_label }}</span>
 </div>
 </div>

 @if(session('success'))
 <div style="background:#d4edda; color:#155724; padding:10px 14px; border-radius:6px; margin-bottom:12px; font-size:13px;"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
 @endif

 @if($ticket->isClosed())
 <div class="closed-banner"><i class="fas fa-info-circle"></i> This ticket is closed. You can no longer reply.</div>
 @endif

 <div class="thread">
 @foreach($ticket->messages as $m)
 <div class="msg {{ $m->sender_role }}">
 <div class="av">{{ strtoupper(mb_substr($m->sender->name ?? ($m->sender_role === 'admin' ? 'A' : 'U'), 0, 1)) }}</div>
 <div class="body">
 <div>
 <span class="who">{{ $m->sender->name ?? ($m->sender_role === 'admin' ? 'Support' : 'You') }}</span>
 <span class="when">{{ $m->created_at->format('d M Y, h:i A') }}</span>
 </div>
 <div class="bubble">{{ $m->message }}</div>
 </div>
 </div>
 @endforeach
 </div>

 @unless($ticket->isClosed())
 <form class="reply-box" method="POST" action="{{ route('shop.support.reply', ['ticket' => $ticket->id]) }}">
 @csrf
 <label for="message">Reply</label>
 <textarea id="message" name="message" required maxlength="5000" placeholder="Type your message…"></textarea>
 <div class="actions">
 <button type="button" class="btn-close" onclick="document.getElementById('close-form').submit();">Close ticket</button>
 <button type="submit" class="btn-submit">Send reply</button>
 </div>
 </form>

 <form id="close-form" method="POST" action="{{ route('shop.support.close', ['ticket' => $ticket->id]) }}" style="display:none;">
 @csrf
 </form>
 @endunless

</div>
@endsection

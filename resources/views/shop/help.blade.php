@extends('layouts.shop')

@section('title', 'Help & Support — ' . $storeName)

@push('styles')
<style>
.help-wrap { max-width: 1100px; margin: 0 auto; padding: 24px 16px 60px; }
.help-hero {
 background: linear-gradient(135deg, #fdf6e3 0%, #fff 100%);
 border: 1px solid #f0c14b; border-radius: 12px;
 padding: 28px 32px; margin-bottom: 22px;
 display: flex; align-items: center; gap: 20px; flex-wrap: wrap;
}
.help-hero .icon { font-size: 36px; color: var(--amazon-orange); }
.help-hero h1 { margin: 0 0 6px; font-size: 22px; color: #0F1111; }
.help-hero p { margin: 0; color: #555; font-size: 14px; }
.help-hero .wa-btn {
 background: #25D366; color: #fff; padding: 12px 22px; border-radius: 100px;
 font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
 font-size: 14px; box-shadow: 0 4px 12px rgba(37,211,102,0.3); transition: transform 0.15s;
}
.help-hero .wa-btn:hover { transform: translateY(-1px); background: #1ebe5a; color: #fff; }
.help-hero .ticket-btn {
 background: #ffd814; border: 1px solid #fcd200; color: #0F1111;
 padding: 12px 22px; border-radius: 100px; font-weight: 600; text-decoration: none; font-size: 14px;
}
.help-hero .ticket-btn:hover { background: #f7ca00; color: #0F1111; }

.grid-2 { display: grid; grid-template-columns: 1.5fr 1fr; gap: 18px; }
@media (max-width: 900px) { .grid-2 { grid-template-columns: 1fr; } }

.card { background: #fff; border: 1px solid #e7e7e7; border-radius: 8px; padding: 22px 24px; margin-bottom: 16px; }
.card h2 { margin: 0 0 14px; font-size: 16px; color: #0F1111; display: flex; align-items: center; gap: 8px; }
.card h2 i { color: var(--amazon-orange); }

.faq { border-bottom: 1px solid #f0f0f0; padding: 10px 0; }
.faq:last-child { border-bottom: none; }
.faq summary {
 font-weight: 600; font-size: 14px; color: #007185; cursor: pointer;
 padding: 4px 0; list-style: none; display: flex; justify-content: space-between; align-items: center;
}
.faq summary::-webkit-details-marker { display: none; }
.faq summary::after { content: '\f078'; font-family: 'Font Awesome 5 Free'; font-weight: 900; color: #999; font-size: 11px; transition: transform 0.2s; }
.faq[open] summary::after { transform: rotate(180deg); }
.faq .ans { font-size: 13.5px; color: #444; line-height: 1.55; margin-top: 8px; }

.ticket-row { display: flex; gap: 10px; padding: 10px 0; border-bottom: 1px solid #f0f0f0; align-items: center; }
.ticket-row:last-child { border-bottom: none; }
.ticket-row .t-info { flex: 1; min-width: 0; }
.ticket-row .t-subj { font-weight: 600; color: #0F1111; font-size: 13.5px; text-decoration: none; }
.ticket-row .t-subj:hover { color: #c7511f; text-decoration: underline; }
.ticket-row .t-meta { font-size: 11.5px; color: #888; margin-top: 2px; }
.status-pill { display: inline-block; padding: 2px 8px; border-radius: 100px; font-size: 10.5px; font-weight: 700; text-transform: uppercase; }
.status-open { background: #fff8e1; color: #946a00; }
.status-awaiting_admin { background: #e3f2fd; color: #0a4b6e; }
.status-awaiting_customer { background: #fce4ec; color: #880e4f; }
.status-closed { background: #e2e3e5; color: #383d41; }

.form-group { margin-bottom: 12px; }
.form-group label { display: block; font-size: 12px; font-weight: 600; color: #444; margin-bottom: 4px; }
.form-group input, .form-group select, .form-group textarea {
 width: 100%; padding: 8px 10px; border: 1px solid #d5d9d9; border-radius: 4px; font-size: 13px; font-family: inherit;
}
.form-group textarea { min-height: 90px; resize: vertical; }
.btn-submit { background: #ffd814; border: 1px solid #fcd200; padding: 9px 22px; border-radius: 100px; font-size: 13px; font-weight: 600; cursor: pointer; }
.btn-submit:hover { background: #f7ca00; }
</style>
@endpush

@section('content')
<div class="help-wrap">

 <div class="help-hero">
 <div class="icon"><i class="fas fa-headset"></i></div>
 <div style="flex:1; min-width: 240px;">
 <h1>How can we help?</h1>
 <p>Find answers to common questions, raise a support ticket, or chat with us on WhatsApp.</p>
 </div>
 <div style="display:flex; gap:10px; flex-wrap:wrap;">
 @if($whatsappNumber)
 <a class="wa-btn" target="_blank" rel="noopener" href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsappNumber) }}?text={{ urlencode($whatsappText) }}">
 <i class="fab fa-whatsapp" style="font-size:18px;"></i> WhatsApp us
 </a>
 @endif
 <a class="ticket-btn" href="#newTicket"><i class="fas fa-plus-circle"></i> Open a ticket</a>
 </div>
 </div>

 <div class="grid-2">
 <div>

 <div class="card">
 <h2><i class="fas fa-question-circle"></i> Frequently asked questions</h2>
 @foreach($faqs as $f)
 <details class="faq">
 <summary>{{ $f['q'] }}</summary>
 <div class="ans">{{ $f['a'] }}</div>
 </details>
 @endforeach
 </div>

 <div class="card" id="newTicket">
 <h2><i class="fas fa-ticket-alt"></i> Open a support ticket</h2>
 <form method="POST" action="{{ route('shop.support.store') }}">
 @csrf
 <div class="form-group">
 <label for="subject">Subject <span style="color:#c7511f;">*</span></label>
 <input id="subject" name="subject" maxlength="200" required placeholder="Briefly describe your issue">
 </div>
 <div class="form-group">
 <label for="category">Category</label>
 <select id="category" name="category">
 @foreach(\App\Models\SupportTicket::CATEGORIES as $key => $label)
 <option value="{{ $key }}">{{ $label }}</option>
 @endforeach
 </select>
 </div>
 <div class="form-group">
 <label for="order_code">Order code (optional)</label>
 <input id="order_code" name="order_code" maxlength="50" placeholder="e.g. NS-20260602-ABCD">
 </div>
 <div class="form-group">
 <label for="message">Describe your issue <span style="color:#c7511f;">*</span></label>
 <textarea id="message" name="message" maxlength="5000" required placeholder="Please give us as much detail as possible…"></textarea>
 </div>
 <button type="submit" class="btn-submit">Submit ticket</button>
 </form>
 </div>

 </div>

 <div>

 <div class="card">
 <h2><i class="fas fa-clock"></i> Your recent tickets</h2>
 @if($tickets->isEmpty())
 <div style="font-size:13px; color:#888; padding:6px 0;">You haven’t opened any tickets yet.</div>
 @else
 @foreach($tickets as $t)
 <div class="ticket-row">
 <div class="t-info">
 <a class="t-subj" href="{{ route('shop.support.show', ['ticket' => $t->id]) }}">#{{ $t->id }} · {{ $t->subject }}</a>
 <div class="t-meta">
 {{ $t->category_label }}
 @if($t->order_code) · Order {{ $t->order_code }} @endif
 · {{ $t->last_message_at ? $t->last_message_at->diffForHumans() : $t->created_at->diffForHumans() }}
 </div>
 </div>
 <span class="status-pill status-{{ $t->status }}">{{ $t->status_label }}</span>
 </div>
 @endforeach
 <a href="{{ route('shop.support.index') }}" style="display:inline-block; margin-top:10px; font-size:12.5px; color:#007185; text-decoration:none;">View all tickets →</a>
 @endif
 </div>

 @if(session('success'))
 <div class="card" style="background:#f0f7ff; border-color:#cce0ff; color:#0a4b87; padding:12px 18px;">{{ session('success') }}</div>
 @endif

 </div>
 </div>

</div>
@endsection

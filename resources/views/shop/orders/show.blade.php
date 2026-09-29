@extends('layouts.shop')

@section('title', 'Order ' . $order->order_code . ' — ' . $storeName)

@push('styles')
<style>
.order-detail-wrap { max-width: 1100px; margin: 0 auto; padding: 24px 16px 60px; }
.order-back { display: inline-flex; align-items: center; gap: 6px; color: #0066c0; font-size: 13px; text-decoration: none; margin-bottom: 14px; }
.order-back:hover { color: #c7511f; text-decoration: underline; }

.order-detail-head {
 background: #fff; border: 1px solid #e7e7e7; border-radius: 8px; padding: 20px 24px; margin-bottom: 16px;
 display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;
}
.order-detail-head h1 { margin: 0; font-size: 22px; color: var(--amazon-charcoal); }
.order-detail-head .meta { font-size: 13px; color: var(--medium-gray); margin-top: 4px; }
.order-detail-head .right { text-align: right; }
.order-detail-head .total { font-size: 22px; font-weight: 700; color: #c7511f; }

.status-badge { display: inline-block; padding: 4px 12px; border-radius: 100px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; }
.status-placed   { background: #fff8e1; color: #946a00; }
.status-packed   { background: #e3f2fd; color: #0a4b6e; }
.status-shipped  { background: #e0f2f1; color: #00695c; }
.status-delivered{ background: #d4edda; color: #155724; }
.status-cancelled{ background: #f8d7da; color: #721c24; }
.status-paid     { background: #d4edda; color: #155724; }
.status-failed   { background: #f8d7da; color: #721c24; }
.status-refunded { background: #e2e3e5; color: #383d41; }

.grid-2 { display: grid; grid-template-columns: 1.4fr 1fr; gap: 16px; }
@media (max-width: 900px) { .grid-2 { grid-template-columns: 1fr; } }

.card { background: #fff; border: 1px solid #e7e7e7; border-radius: 8px; padding: 18px 22px; }
.card h2 { font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px; color: var(--amazon-charcoal); margin: 0 0 12px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
.card h2 i { color: var(--amazon-orange); }
.card .row { display: flex; font-size: 13px; padding: 4px 0; }
.card .row .lbl { color: var(--medium-gray); min-width: 110px; flex-shrink: 0; }
.card .row .val { color: #0F1111; }

.item-row { display: flex; gap: 12px; padding: 12px 0; border-bottom: 1px solid #f0f0f0; align-items: center; }
.item-row:last-child { border-bottom: none; }
.item-row img { width: 64px; height: 64px; object-fit: cover; border-radius: 4px; background: #f3f3f3; flex-shrink: 0; }
.item-row .placeholder { width: 64px; height: 64px; background: #f3f3f3; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #ccc; flex-shrink: 0; }
.item-row .info { flex: 1; min-width: 0; }
.item-row .name { font-size: 14px; font-weight: 600; color: #0F1111; }
.item-row .qty { font-size: 12px; color: var(--medium-gray); margin-top: 2px; }
.item-row .price { font-size: 14px; font-weight: 700; color: #c7511f; flex-shrink: 0; }

.totals { background: #fff7e6; border: 1px solid #f0c14b; border-radius: 6px; padding: 14px 18px; margin-top: 14px; }
.totals .row { display: flex; justify-content: space-between; font-size: 13px; padding: 3px 0; }
.totals .row.tot { font-size: 16px; font-weight: 700; color: #c7511f; border-top: 1px dashed #d5b878; margin-top: 6px; padding-top: 8px; }

/* Tracking timeline */
.timeline { position: relative; padding: 4px 0 0 32px; }
.timeline::before {
 content: ''; position: absolute; left: 11px; top: 10px; bottom: 10px; width: 2px; background: #e7e7e7;
}
.step { position: relative; padding: 0 0 24px 0; }
.step:last-child { padding-bottom: 0; }
.step-icon {
 position: absolute; left: -29px; top: 0px; width: 16px; height: 16px;
 display: flex; align-items: center; justify-content: center;
 background: #fff; border-radius: 50%; z-index: 2;
 font-size: 14px;
}
.step-icon.done { color: #007600; }
.step-icon.active { color: var(--amazon-orange); }
.step .lbl { font-size: 14px; font-weight: 600; color: #0F1111; line-height: 1.2; }
.step.done .lbl { color: #007600; }
.step.active .lbl { color: var(--amazon-charcoal); }
.step .when { font-size: 11px; color: var(--medium-gray); margin-top: 3px; }
.step .note { font-size: 12px; color: var(--medium-gray); margin-top: 5px; }

.tracking-note {
 background: #fff8e1; border: 1px solid #f0c14b; border-radius: 6px;
 padding: 10px 14px; font-size: 12px; color: #946a00; margin-top: 14px;
}

.actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 18px; }
.btn-primary { background: #ffd814; border: 1px solid #fcd200; border-radius: 100px; padding: 10px 22px; font-size: 13px; font-weight: 600; text-decoration: none; color: #0F1111; }
.btn-primary:hover { background: #f7ca00; }
.btn-secondary { background: #fff; border: 1px solid #d5d9d9; border-radius: 100px; padding: 10px 22px; font-size: 13px; font-weight: 600; text-decoration: none; color: #0F1111; }
.btn-secondary:hover { background: #f7f7f7; }
.btn-danger-outline { background: #fff; border: 1px solid #c7511f; color: #c7511f; border-radius: 100px; padding: 9px 22px; font-size: 13px; font-weight: 600; text-decoration: none; cursor: pointer; }
.btn-danger-outline:hover { background: #fdf2ec; }

/* Cancel / Return modals */
.modal-backdrop { position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 1000; display: none; align-items: center; justify-content: center; padding: 16px; }
.modal-backdrop.open { display: flex; }
.modal { background: #fff; border-radius: 8px; width: 100%; max-width: 480px; padding: 22px 24px; }
.modal h3 { margin: 0 0 6px; font-size: 17px; color: #111; }
.modal p { margin: 0 0 14px; font-size: 13px; color: #555; }
.modal label { font-size: 12px; color: #444; font-weight: 600; display: block; margin-top: 10px; margin-bottom: 4px; }
.modal textarea, .modal input[type=text], .modal select { width: 100%; border: 1px solid #d5d9d9; border-radius: 4px; padding: 8px 10px; font-size: 13px; font-family: inherit; }
.modal textarea:focus, .modal input:focus, .modal select:focus { outline: none; border-color: #007185; box-shadow: 0 0 0 2px rgba(0,113,133,0.10); }
.modal-actions { display: flex; gap: 8px; justify-content: flex-end; margin-top: 16px; }
.modal-actions .btn-secondary, .modal-actions .btn-primary { padding: 8px 18px; }

.return-row { display: flex; gap: 12px; align-items: center; padding: 12px 0; border-top: 1px solid #f0f0f0; }
.return-row:first-of-type { border-top: none; }
.return-row img { width: 56px; height: 56px; object-fit: cover; border-radius: 4px; background: #f3f3f3; }
.return-row .meta { flex: 1; min-width: 0; }
.return-row .meta .name { font-size: 13px; font-weight: 600; }
.return-row .meta .sub { font-size: 11.5px; color: #777; margin-top: 2px; }
.return-row .actions-cell { display: flex; align-items: center; gap: 6px; flex-shrink: 0; }
.return-status-pill { font-size: 10.5px; font-weight: 700; padding: 2px 8px; border-radius: 100px; background: #fff8e1; color: #946a00; text-transform: uppercase; }
.return-status-pill.completed  { background: #d4edda; color: #155724; }
.return-status-pill.rejected   { background: #f8d7da; color: #721c24; }
.return-status-pill.picked_up  { background: #e0f2f1; color: #00695c; }
</style>
@endpush

@section('content')
<div class="order-detail-wrap">
 <a href="{{ route('shop.orders.index') }}" class="order-back"><i class="fas fa-arrow-left"></i> Back to all orders</a>

 <div class="order-detail-head">
 <div>
 <h1>Order {{ $order->order_code }}</h1>
 <div class="meta">
 Placed on <strong>{{ $order->created_at->format('d M Y, h:i A') }}</strong>
 &middot; {{ $order->items->count() }} {{ \Illuminate\Support\Str::plural('item', $order->items->count()) }}
 &middot; UTR: <strong>{{ $order->payment_utr ?: '—' }}</strong>
 </div>
 <div style="margin-top:8px; display:flex; gap:6px; flex-wrap:wrap;">
 <span class="status-badge status-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
 <span class="status-badge status-{{ $order->payment_status }}">Payment: {{ ucfirst($order->payment_status) }}</span>
 </div>
 </div>
 <div class="right">
 <div class="total">₹{{ number_format($order->total, 2) }}</div>
 <div class="meta" style="font-size:12px;">{{ ucfirst($order->payment_method) }} &middot; {{ ucfirst($order->payment_gateway ?: '—') }}</div>
 </div>
 </div>

 <div class="grid-2">
 <div>
 {{-- Items --}}
 <div class="card">
 <h2><i class="fas fa-box"></i> Items in this order</h2>
 @foreach($order->items as $it)
 <div class="item-row">
 @if($it->product_image)
 <img src="{{ $it->product_image }}" alt="">
 @else
 <div class="placeholder"><i class="fas fa-image"></i></div>
 @endif
 <div class="info">
 <div class="name">{{ $it->product_name }}</div>
 <div class="qty">Qty: {{ $it->quantity }} &times; ₹{{ number_format($it->unit_price, 2) }}</div>
 </div>
 <div class="price">₹{{ number_format($it->line_total, 2) }}</div>
 </div>
 @endforeach

 <div class="totals">
 <div class="row"><span>Subtotal</span><span>₹{{ number_format($order->subtotal, 2) }}</span></div>
 @if($order->discount > 0)
 <div class="row" style="color:#007600;">
 <span>{{ $order->coupon_code ? 'Coupon discount (' . $order->coupon_code . ')' : 'Bonus discount' }}</span>
 <span>−₹{{ number_format($order->discount, 2) }}</span>
 </div>
 @endif
 <div class="row"><span>Shipping</span><span style="color:#007600;">FREE</span></div>
 <div class="row tot"><span>Total paid</span><span>₹{{ number_format($order->total, 2) }}</span></div>
 </div>
 </div>

 {{-- Tracking --}}
 <div class="card" style="margin-top:16px;">
 <h2><i class="fas fa-truck"></i> Order tracking</h2>

  @php
  // Build dynamic timeline steps based on order history
  $timelineSteps = [
      [
          'label' => 'Order placed',
          'at' => $order->created_at,
          'desc' => 'We received your order and payment.',
      ]
  ];

  foreach ($order->status_history ?? [] as $h) {
      $at = \Illuminate\Support\Carbon::parse($h['at']);
      $event = $h['event'] ?? '';
      $detail = $h['detail'] ?? '';

      if ($event === 'status_change') {
          // Parse the transition target
          $parts = explode(' — ', $detail, 2);
          $transition = trim($parts[0]);
          $note = trim($parts[1] ?? '');

          $toStatus = null;
          if (preg_match('/→\s*([a-zA-Z0-9_-]+)/', $transition, $matches)) {
              $toStatus = $matches[1];
          }

          if ($toStatus === 'accepted') {
              $timelineSteps[] = [
                  'label' => 'Order accepted',
                  'at' => $at,
                  'desc' => $note ?: 'We have accepted your order.',
              ];
          } elseif ($toStatus === 'dispatched') {
              $timelineSteps[] = [
                  'label' => 'Order dispatched',
                  'at' => $at,
                  'desc' => $note ?: 'Your order has been dispatched.',
              ];
          } elseif ($toStatus === 'delivered') {
              $timelineSteps[] = [
                  'label' => 'Delivered',
                  'at' => $at,
                  'desc' => $note ?: 'Your order has been delivered. Enjoy!',
              ];
          } elseif ($toStatus === 'cancelled') {
              $timelineSteps[] = [
                  'label' => 'Cancelled',
                  'at' => $at,
                  'desc' => $note ?: 'Your order has been cancelled.',
              ];
          } elseif ($toStatus === 'refunded') {
              $timelineSteps[] = [
                  'label' => 'Refunded',
                  'at' => $at,
                  'desc' => $note ?: 'Your order has been refunded.',
              ];
          }
      } elseif ($event === 'dispatched') {
          $desc = 'Your order is on its way.';
          if (!empty($detail)) {
              $desc = str_replace(' — ', ', ', $detail);
              $desc = ucfirst($desc);
          }
          $timelineSteps[] = [
              'label' => 'Order dispatched',
              'at' => $at,
              'desc' => $desc,
          ];
      } elseif ($event === 'cancelled') {
          $timelineSteps[] = [
              'label' => 'Cancelled',
              'at' => $at,
              'desc' => $detail ?: 'Your order has been cancelled.',
          ];
      } elseif ($event === 'refunded') {
          $timelineSteps[] = [
              'label' => 'Refunded',
              'at' => $at,
              'desc' => $detail ?: 'Your order has been refunded.',
          ];
      } elseif ($event === 'custom') {
          // If custom status, show the custom message directly
          $timelineSteps[] = [
              'label' => $detail,
              'at' => $at,
              'desc' => '',
          ];
      }
  }

  $totalSteps = count($timelineSteps);
  @endphp

   <div class="timeline">
   @foreach($timelineSteps as $i => $s)
   @php
   $class = '';
   if ($i < $totalSteps - 1) {
       $class = 'done';
   } else {
       $class = 'active';
   }
   @endphp
   <div class="step {{ $class }}">
       <div class="step-icon {{ $class }}">
           @if($class === 'done') 
               <i class="fas fa-check-circle"></i>
           @else 
               <i class="fas fa-circle" style="font-size: 10px;"></i>
           @endif
       </div>
       <div class="lbl">
           {{ $s['label'] }}
       </div>
       <div class="when">{{ $s['at']->format('d M Y, h:i A') }}</div>
       @if(!empty($s['desc']))
           <div class="note">{{ $s['desc'] }}</div>
       @endif
   </div>
   @endforeach
   </div>

  <div class="tracking-note">
  <i class="fas fa-info-circle"></i>
  Live tracking (courier location, ETA) will appear here once it ships.
  </div>
 </div>
 </div>

 <div>
 {{-- Address --}}
 <div class="card">
 <h2><i class="fas fa-map-marker-alt"></i> Shipping address</h2>
 <div class="row"><span class="lbl">Name</span><span class="val">{{ $order->addr_full_name }} ({{ ucfirst($order->addr_type) }})</span></div>
 <div class="row"><span class="lbl">Address</span><span class="val">{{ $order->addr_line_1 }}@if($order->addr_line_2), {{ $order->addr_line_2 }}@endif, {{ $order->addr_city }}, {{ $order->addr_state }} — {{ $order->addr_pincode }}</span></div>
 <div class="row"><span class="lbl">Phone</span><span class="val">{{ $order->addr_mobile_primary }}@if($order->addr_mobile_alternate) / {{ $order->addr_mobile_alternate }}@endif</span></div>
 </div>

 {{-- Contact --}}
 <div class="card" style="margin-top:16px;">
 <h2><i class="fas fa-user"></i> Contact</h2>
 <div class="row"><span class="lbl">Name</span><span class="val">{{ $order->contact_name }}</span></div>
 <div class="row"><span class="lbl">Mobile</span><span class="val">{{ $order->contact_mobile }}</span></div>
 @if($order->contact_email)
 <div class="row"><span class="lbl">Email</span><span class="val">{{ $order->contact_email }}</span></div>
 @endif
 </div>

 {{-- Returns panel (per-item) --}}
 @if($returnItems->where('is_returnable', true)->count() > 0 || $order->status === 'delivered')
 <div class="card" style="margin-top:16px;">
 <h2><i class="fas fa-undo"></i> Returns</h2>
 @if($order->status !== 'delivered')
 <div style="font-size: 12.5px; color: var(--medium-gray);">You can request a return once your order is delivered.</div>
 @else
 @foreach($returnItems as $ri)
 <div class="return-row">
 @if($ri['item']->product_image)
 <img src="{{ $ri['item']->product_image }}" alt="">
 @else
 <div class="meta"><div class="placeholder" style="width:56px;height:56px;background:#f3f3f3;border-radius:4px;display:flex;align-items:center;justify-content:center;color:#ccc;"><i class="fas fa-image"></i></div></div>
 @endif
 <div class="meta">
 <div class="name">{{ $ri['item']->product_name }}</div>
 <div class="sub">
 @if($ri['return'])
 Return: {{ $ri['return']->status_label }}
 @if($ri['return']->admin_note) — {{ $ri['return']->admin_note }} @endif
 @elseif($ri['is_returnable'])
 Returnable item
 @else
 Not eligible for return
 @endif
 </div>
 </div>
 <div class="actions-cell">
 @if($ri['return'])
 <span class="return-status-pill {{ $ri['return']->status }}">{{ $ri['return']->status_label }}</span>
 @elseif($ri['can_request'])
 @php $it = $ri['item']; @endphp
 <button type="button" class="btn-danger-outline" onclick="openReturnModal({{ $it->id }}, '{{ addslashes($it->product_name) }}')">Return</button>
 @endif
 </div>
 </div>
 @endforeach
 @endif
 </div>
 @endif

 @if(session('success'))
 <div class="card" style="margin-top:16px; border-color:#cce0ff; background:#f0f7ff; color:#0a4b87; padding:12px 18px;">{{ session('success') }}</div>
 @endif
 @if($errors->any())
 <div class="card" style="margin-top:16px; border-color:#f8d7da; background:#fdf2f2; color:#721c24; padding:12px 18px;">{{ $errors->first() }}</div>
 @endif

 {{-- Payment --}}
 <div class="card" style="margin-top:16px;">
 <h2><i class="fas fa-credit-card"></i> Payment</h2>
 <div class="row"><span class="lbl">Method</span><span class="val">{{ ucfirst($order->payment_method) }}</span></div>
 <div class="row"><span class="lbl">Gateway</span><span class="val">{{ ucfirst($order->payment_gateway ?: '—') }}</span></div>
 <div class="row"><span class="lbl">Status</span><span class="val"><span class="status-badge status-{{ $order->payment_status }}">{{ ucfirst($order->payment_status) }}</span></span></div>
 <div class="row"><span class="lbl">UTR</span><span class="val">{{ $order->payment_utr ?: '—' }}</span></div>
 <div class="row"><span class="lbl">Paid at</span><span class="val">{{ $order->paid_at ? $order->paid_at->format('d M Y, h:i A') : '—' }}</span></div>
 </div>

 <div class="actions">
 <a href="{{ url('/') }}" class="btn-primary">Continue shopping</a>
 <a href="{{ route('shop.orders.index') }}" class="btn-secondary">All orders</a>
 <a href="{{ route('shop.orders.invoice', ['order' => $order->order_code]) }}" class="btn-secondary"><i class="fas fa-file-pdf"></i> Download Invoice</a>
 @if($canCancel)
 <button type="button" class="btn-danger-outline" onclick="openCancelModal()">Cancel order</button>
 @endif
 </div>
 </div>
 </div>
</div>

{{-- Cancel modal --}}
@if($canCancel)
<div class="modal-backdrop" id="cancelModal" onclick="if(event.target===this) closeCancelModal()">
 <div class="modal">
 <h3>Cancel order?</h3>
 <p>This will cancel order <strong>{{ $order->order_code }}</strong>. Please tell us why.</p>
 <form method="POST" action="{{ route('shop.orders.cancel', ['order' => $order->order_code]) }}">
 @csrf
 <label for="cancel-reason">Reason</label>
 <textarea id="cancel-reason" name="reason" rows="3" required minlength="3" maxlength="500" placeholder="e.g. I changed my mind / Found a better price / Ordered by mistake"></textarea>
 <div class="modal-actions">
 <button type="button" class="btn-secondary" onclick="closeCancelModal()">Keep order</button>
 <button type="submit" class="btn-primary" style="background:#c7511f; border-color:#c7511f; color:#fff;">Cancel order</button>
 </div>
 </form>
 </div>
</div>
@endif

{{-- Return modal --}}
<div class="modal-backdrop" id="returnModal" onclick="if(event.target===this) closeReturnModal()">
 <div class="modal">
 <h3>Request a return</h3>
 <p>Item: <strong id="returnItemName"></strong></p>
 <form method="POST" action="{{ route('shop.orders.return', ['order' => $order->order_code]) }}" enctype="multipart/form-data">
 @csrf
 <input type="hidden" name="order_item_id" id="returnOrderItemId" value="">
 <label for="return-reason">Reason <span style="color:#c7511f;">*</span></label>
 <select id="return-reason-select" onchange="document.getElementById('return-reason').value=this.value; document.getElementById('return-reason-hidden-row').style.display=this.value==='Other'?'block':'none';">
 <option value="">— select a reason —</option>
 <option value="Damaged product">Damaged product</option>
 <option value="Wrong item received">Wrong item received</option>
 <option value="Quality not as expected">Quality not as expected</option>
 <option value="Not as described">Not as described</option>
 <option value="Other">Other</option>
 </select>
 <input type="hidden" name="reason" id="return-reason" value="" required>
 <div id="return-reason-hidden-row" style="display:none; margin-top:8px;">
 <label for="return-reason-other">Type your reason</label>
 <input type="text" id="return-reason-other" maxlength="1000" oninput="document.getElementById('return-reason').value=this.value">
 </div>

 <label for="return-description" style="margin-top:12px;">Details (optional)</label>
 <textarea id="return-description" name="description" rows="3" maxlength="2000" placeholder="Anything that will help us process your return faster…"></textarea>

 <label for="return-image" style="margin-top:12px;">Photo (optional)</label>
 <input type="file" id="return-image" name="image" accept="image/png,image/jpeg,image/webp">

 <div class="modal-actions">
 <button type="button" class="btn-secondary" onclick="closeReturnModal()">Close</button>
 <button type="submit" class="btn-primary">Submit return</button>
 </div>
 </form>
 </div>
</div>

@push('scripts')
<script>
function openCancelModal() {
 document.getElementById('cancelModal').classList.add('open');
 document.body.style.overflow = 'hidden';
}
function closeCancelModal() {
 document.getElementById('cancelModal').classList.remove('open');
 document.body.style.overflow = '';
}
function openReturnModal(itemId, itemName) {
 document.getElementById('returnOrderItemId').value = itemId;
 document.getElementById('returnItemName').textContent = itemName;
 document.getElementById('returnModal').classList.add('open');
 document.body.style.overflow = 'hidden';
}
function closeReturnModal() {
 document.getElementById('returnModal').classList.remove('open');
 document.body.style.overflow = '';
}
document.addEventListener('keydown', function(e) {
 if (e.key === 'Escape') { closeCancelModal(); closeReturnModal(); }
});
</script>
@endpush
@endsection

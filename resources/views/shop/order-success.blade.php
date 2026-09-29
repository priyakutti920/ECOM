@extends('layouts.shop')

@section('title', 'Order ' . $order->order_code . ' confirmed — ' . $storeName)

@push('styles')
<style>
 .success-page { max-width: 760px; margin: 0 auto; padding: 40px 16px; }
 .success-card { background: #fff; padding: 32px 28px; border-radius: 8px; border: 1px solid #e7e7e7; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
 .success-head { text-align: center; padding-bottom: 22px; border-bottom: 1px solid #eee; margin-bottom: 22px; }
 .success-icon { width: 80px; height: 80px; border-radius: 50%; background: #d4edda; color: #155724; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; font-size: 36px; }
 .success-title { font-size: 26px; font-weight: 700; color: var(--amazon-charcoal); margin: 0 0 6px; }
 .success-sub { color: var(--medium-gray); font-size: 14px; margin: 0 0 12px; }
 .order-id-row { display: inline-flex; align-items: center; gap: 10px; background: #fff8e1; border: 1px solid #f0c14b; padding: 10px 18px; border-radius: 100px; font-size: 15px; }
 .order-id-row .lbl { font-weight: 600; color: #946a00; font-size: 12px; text-transform: uppercase; letter-spacing: 0.6px; }
 .order-id-row .code { font-weight: 800; color: #0F1111; letter-spacing: 1px; font-size: 16px; }
 .panels { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
 .panel { background: #fafafa; border: 1px solid #eee; border-radius: 6px; padding: 16px 18px; }
 .panel h3 { margin: 0 0 8px; font-size: 13px; color: var(--amazon-charcoal); font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; display: flex; align-items: center; gap: 6px; }
 .panel h3 i { color: var(--amazon-orange); }
 .panel .row { display: flex; gap: 8px; font-size: 13px; margin: 4px 0; }
 .panel .row .lbl { color: var(--medium-gray); min-width: 90px; }
 .panel .row .val { color: #0F1111; word-break: break-word; }
 .items-block { margin-top: 18px; }
 .items-block h3 { font-size: 13px; color: var(--amazon-charcoal); font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; margin: 0 0 10px; display: flex; align-items: center; gap: 6px; }
 .items-block h3 i { color: var(--amazon-orange); }
 .item-row { display: flex; gap: 12px; padding: 10px 0; border-bottom: 1px solid #eee; align-items: center; }
 .item-row:last-child { border-bottom: none; }
 .item-row img { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; background: #f3f3f3; flex-shrink: 0; }
 .item-row .placeholder { width: 50px; height: 50px; background: #f3f3f3; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #ccc; flex-shrink: 0; }
 .item-row .info { flex: 1; min-width: 0; }
 .item-row .name { font-size: 13px; font-weight: 600; color: #0F1111; }
 .item-row .qty { font-size: 12px; color: var(--medium-gray); }
 .item-row .price { font-size: 13px; font-weight: 700; color: #c7511f; }
 .totals { margin-top: 18px; background: #fff7e6; border: 1px solid #f0c14b; border-radius: 6px; padding: 14px 18px; }
 .totals .row { display: flex; justify-content: space-between; font-size: 13px; padding: 3px 0; color: #444; }
 .totals .row.tot { font-size: 16px; font-weight: 700; color: #c7511f; border-top: 1px dashed #d5b878; margin-top: 6px; padding-top: 8px; }
 .next-steps { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin: 24px 0 18px; }
 .step { text-align: center; padding: 12px; background: #fafafa; border-radius: 6px; }
 .step i { font-size: 24px; color: var(--amazon-orange); margin-bottom: 6px; }
 .step .lbl { font-size: 12px; color: #0F1111; font-weight: 600; }
 .action-btns { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-top: 18px; }
 .btn-primary { background: #ffd814; border: 1px solid #fcd200; border-radius: 100px; padding: 11px 26px; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; color: #0F1111; }
 .btn-primary:hover { background: #f7ca00; }
 .btn-secondary { background: #fff; border: 1px solid #d5d9d9; border-radius: 100px; padding: 11px 26px; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; color: #0F1111; }
 .btn-secondary:hover { background: #f7f7f7; }
 @media (max-width: 700px) {
 .panels { grid-template-columns: 1fr; }
 .next-steps { grid-template-columns: 1fr; }
 .success-title { font-size: 22px; }
 }
</style>
@endpush

@section('content')
<div class="success-page">
 <div class="success-card">

 <div class="success-head">
 <div class="success-icon"><i class="fas fa-check"></i></div>
 <h1 class="success-title">Payment received — order confirmed!</h1>
 <p class="success-sub">Thank you for shopping at {{ $storeName }}. We've started processing your order.</p>
 <div class="order-id-row">
 <span class="lbl">Order ID</span>
 <span class="code">{{ $order->order_code }}</span>
 </div>
 </div>

 <div class="panels">
 <div class="panel">
 <h3><i class="fas fa-user"></i> Contact</h3>
 <div class="row"><span class="lbl">Name</span><span class="val">{{ $order->contact_name }}</span></div>
 <div class="row"><span class="lbl">Mobile</span><span class="val">{{ $order->contact_mobile }}</span></div>
 @if($order->contact_email)
 <div class="row"><span class="lbl">Email</span><span class="val">{{ $order->contact_email }}</span></div>
 @endif
 </div>
 <div class="panel">
 <h3><i class="fas fa-map-marker-alt"></i> Delivery Address</h3>
 <div class="row"><span class="lbl">To</span><span class="val">{{ $order->addr_full_name }} ({{ ucfirst($order->addr_type) }})</span></div>
 <div class="row"><span class="lbl">Address</span><span class="val">{{ $order->addr_line_1 }}@if($order->addr_line_2), {{ $order->addr_line_2 }}@endif, {{ $order->addr_city }}, {{ $order->addr_state }} &mdash; {{ $order->addr_pincode }}</span></div>
 <div class="row"><span class="lbl">Phone</span><span class="val">{{ $order->addr_mobile_primary }}@if($order->addr_mobile_alternate) / {{ $order->addr_mobile_alternate }}@endif</span></div>
 </div>
 </div>

 <div class="items-block">
 <h3><i class="fas fa-box"></i> Items ({{ $order->items->count() }})</h3>
 @foreach($order->items as $it)
 <div class="item-row">
 @if($it->product_image)
 <img src="{{ $it->product_image }}" alt="">
 @else
 <div class="placeholder"><i class="fas fa-image"></i></div>
 @endif
 <div class="info">
 <div class="name">{{ $it->product_name }}</div>
 @if($it->variation_name)
 <div style="font-size:11.5px; color:#565959;">Variation: <strong>{{ $it->variation_name }}</strong></div>
 @endif
 @if($it->color)
 <div style="font-size:11.5px; color:#565959;">Color: <strong>{{ $it->color }}</strong></div>
 @endif
 @if(!empty($it->options) && is_array($it->options))
 <div style="font-size:11px; color:#007185;">Options: {{ collect($it->options)->pluck('name')->join(', ') }}</div>
 @endif
 <div class="qty">{{ $it->quantity }} × ₹{{ number_format($it->unit_price, 2) }}</div>
 </div>
 <div class="price">₹{{ number_format($it->line_total, 2) }}</div>
 </div>
 @endforeach
 </div>

 <div class="totals">
 <div class="row"><span>Subtotal</span><span>₹{{ number_format($order->subtotal, 2) }}</span></div>
 @if($order->discount > 0)
 <div class="row" style="color:#007600;"><span>Bonus discount</span><span>−₹{{ number_format($order->discount, 2) }}</span></div>
 @endif
 <div class="row"><span>Shipping</span><span style="color:#007600;">FREE</span></div>
 <div class="row tot">
 <span>{{ $order->payment_method === 'cod' ? 'Total (Pay on Delivery)' : 'Total paid' }}</span>
 <span>₹{{ number_format($order->total, 2) }}</span>
 </div>
 @if($order->payment_utr)
 <div class="row" style="margin-top:8px; font-size:12px; color: var(--medium-gray);">
 <span>Payment UTR</span><span>{{ $order->payment_utr }}</span>
 </div>
 @endif
 </div>

 <div class="next-steps">
 <div class="step"><i class="fas fa-receipt"></i><div class="lbl">Order received</div></div>
 <div class="step"><i class="fas fa-box"></i><div class="lbl">Packed &amp; ready</div></div>
 <div class="step"><i class="fas fa-truck"></i><div class="lbl">Out for delivery</div></div>
 </div>

 <div class="action-btns">
 <a href="{{ url('/') }}" class="btn-primary">Continue Shopping</a>
 <a href="{{ route('shop.orders.show', ['order' => $order->order_code]) }}" class="btn-secondary">View this order</a>
 <a href="{{ route('shop.orders.invoice', ['order' => $order->order_code]) }}" class="btn-secondary"><i class="fas fa-file-pdf"></i> Download Invoice</a>
 <a href="{{ app(\App\Services\WhatsAppService::class)->getShareUrl($order) }}" target="_blank" class="btn-secondary" style="background:#25d366; color:#fff; border-color:#25d366; font-weight:700;">
 <i class="fab fa-whatsapp"></i> Share on WhatsApp
 </a>
 </div>

 <p style="color: var(--medium-gray); font-size: 12px; margin-top: 18px; text-align:center;">
 We'll contact you on {{ $order->addr_mobile_primary }} to confirm delivery details.
 </p>
 </div>
</div>

@push('scripts')
<script>
const CART_KEY = 'store_cart';
const SINGLE_KEY = 'buyNowItem';

document.addEventListener('DOMContentLoaded', () => {
 // If the order was a "Buy Now" of a single product, remove ONLY that
 // product from the cart. Otherwise (full-cart checkout) clear the cart.
 const single = JSON.parse(localStorage.getItem(SINGLE_KEY) || 'null');
 if (single && single.id) {
  try {
   const list = JSON.parse(localStorage.getItem(CART_KEY) || localStorage.getItem('nellai_cart') || '[]');
   const filtered = list.filter(it => Number(it.id) !== Number(single.id));
   localStorage.setItem(CART_KEY, JSON.stringify(filtered));
   localStorage.setItem('nellai_cart', JSON.stringify(filtered));
  } catch (e) {}
  localStorage.removeItem(SINGLE_KEY);
 } else {
  try { 
   localStorage.removeItem(CART_KEY); 
   localStorage.removeItem('nellai_cart'); 
  } catch (e) {}
 }
 if (typeof updateCartCount === 'function') updateCartCount();
});
</script>
@endpush
@endsection

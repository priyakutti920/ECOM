@extends('layouts.shop')

@section('title', 'Buy Now — ' . $storeName)
@section('description', 'Quick checkout at ' . $storeName . '. Enter your name, mobile and delivery address.')

@push('styles')
<style>
.buy-now-page { max-width: 1100px; margin: 0 auto; padding: 16px; }
.buy-now-grid { display: grid; grid-template-columns: 1.3fr 1fr; gap: 16px; align-items: start; }
.buy-now-form { background: #fff; padding: 24px; border-radius: 4px; border: 1px solid #e7e7e7; }
.buy-now-summary { background: #fff; padding: 20px; border-radius: 4px; border: 1px solid #e7e7e7; position: sticky; top: 80px; }
.section-title { font-size: 18px; font-weight: 700; color: var(--amazon-charcoal); margin: 0 0 12px; padding-bottom: 8px; border-bottom: 1px solid #e7e7e7; }
.sub-title { font-size: 14px; font-weight: 700; color: var(--amazon-charcoal); margin: 16px 0 8px; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 10px; }
.form-field { margin-bottom: 10px; }
.form-field label { display:block; font-size: 12px; color: #0F1111; font-weight: 600; margin-bottom: 4px; }
.form-field label .req { color: #c7511f; }
.form-field input, .form-field select { width: 100%; padding: 9px 10px; border: 1px solid #d5d9d9; border-radius: 4px; font-size: 14px; outline: none; transition: border 0.15s, box-shadow 0.15s; background: #fff; }
.form-field input:focus, .form-field select:focus { border-color: #007185; box-shadow: 0 0 0 3px rgba(0,113,133,0.15); }
.form-field input.error, .form-field select.error { border-color: #c7511f; box-shadow: 0 0 0 3px rgba(199,81,31,0.15); }
.form-field .err-msg { display:none; color: #c7511f; font-size: 12px; margin-top: 3px; }
.form-field.has-error .err-msg { display: block; }
.address-type-row { display:flex; gap: 10px; }
.address-type-row label { display:flex; align-items:center; gap:6px; padding:8px 12px; border:1px solid #d5d9d9; border-radius: 18px; cursor:pointer; font-weight: 500; font-size: 13px; flex:1; }
.address-type-row label:has(input:checked) { background: #fef7e3; border-color: #f0c14b; }
.address-type-row input { display: none; }
.address-type-row i { color: #555; }
.empty-cart-warning { background: #fef2f2; border: 1px solid #c7511f; padding: 14px 16px; border-radius: 4px; margin-bottom: 12px; color: #842029; font-size: 14px; }
.summary-item { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #f0f0f0; }
.summary-item img { width: 48px; height: 48px; object-fit: cover; border-radius: 4px; background: #f3f3f3; }
.summary-item .info { flex: 1; min-width: 0; }
.summary-item .name { font-size: 13px; font-weight: 600; color: #0F1111; overflow:hidden; text-overflow:ellipsis; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; }
.summary-item .meta { font-size: 11px; color: var(--medium-gray); margin-top: 2px; }
.summary-item .price { font-size: 14px; font-weight: 700; color: #c7511f; white-space: nowrap; }
.bn-qty-row { display: inline-flex; align-items: center; gap: 4px; margin-top: 4px; }
.bn-qty-btn { width: 22px; height: 22px; border: 1px solid #d5d9d9; background: #f0f2f2; border-radius: 4px; cursor: pointer; font-size: 13px; font-weight: 700; padding: 0; }
.bn-qty-btn:hover { background: #e7e7e7; }
.bn-qty-input { width: 32px; height: 22px; text-align: center; border: 1px solid #d5d9d9; border-radius: 4px; font-size: 12px; font-weight: 600; padding: 0; -moz-appearance: textfield; }
.bn-qty-input::-webkit-outer-spin-button, .bn-qty-input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.bn-unit-price { margin-left: 4px; color: var(--medium-gray); font-size: 11px; }
.summary-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 13px; }
.summary-row.total { font-size: 18px; font-weight: 700; border-top: 1px solid #e7e7e7; padding-top: 10px; margin-top: 6px; }
.summary-row.discount { color: #c7511f; }
.btn-place { width: 100%; background: #ffd814; border: 1px solid #fcd200; border-radius: 100px; padding: 12px; font-size: 14px; font-weight: 600; cursor: pointer; margin-top: 12px; }
.btn-place:hover { background: #f7ca00; }
.btn-place:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-place i { margin-right: 6px; }
.secure-note { font-size: 11px; color: var(--medium-gray); text-align: center; margin-top: 10px; display: flex; align-items: center; justify-content: center; gap: 4px; }
.secure-note i { color: #007600; }
.bonus-note { background: #f0f7ff; border: 1px solid #cce0ff; padding: 8px 10px; border-radius: 4px; font-size: 12px; color: #0a4b87; margin: 10px 0; }

/* Coupon block */
.coupon-block { margin-top: 10px; padding-top: 10px; border-top: 1px dashed #d5d9d9; }
.coupon-block-row { display: flex; gap: 6px; }
.coupon-block-row input { flex: 1; padding: 8px 10px; border: 1px solid #d5d9d9; border-radius: 4px; font-size: 12.5px; text-transform: uppercase; letter-spacing: 0.5px; }
.coupon-block-row input:focus { outline: none; border-color: #007185; box-shadow: 0 0 0 2px rgba(0,113,133,0.10); }
.coupon-block-row button { background: #f0f2f2; border: 1px solid #d5d9d9; padding: 8px 14px; border-radius: 4px; font-size: 12.5px; font-weight: 600; cursor: pointer; color: #111; }
.coupon-block-row button:hover { background: #e7e9e9; }
.coupon-msg { margin-top: 6px; font-size: 11.5px; padding: 4px 0; }
.coupon-msg.ok { color: #007600; }
.coupon-msg.err { color: #c7511f; }
.coupon-toggle { display: inline-block; margin-top: 6px; font-size: 11.5px; color: #007185; cursor: pointer; }
.coupon-toggle:hover { text-decoration: underline; }
.coupon-list { margin-top: 6px; max-height: 160px; overflow-y: auto; }
.coupon-item { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 7px 10px; border: 1px dashed #cce0ff; border-radius: 4px; background: #f7fbff; margin-bottom: 6px; font-size: 12px; }
.coupon-item .ci-body { flex: 1; }
.coupon-item .ci-code { font-weight: 700; color: #0a4b87; letter-spacing: 0.5px; }
.coupon-item .ci-min { color: #888; font-size: 10.5px; }
.coupon-item .ci-use { background: #007185; color: #fff; border: none; padding: 4px 10px; border-radius: 4px; font-size: 11px; cursor: pointer; }
.coupon-item .ci-use:hover { background: #005f6b; }

/* Saved address picker */
.saved-address-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 10px; margin-bottom: 12px; }
.saved-address-option { display: flex; gap: 10px; padding: 12px; border: 1px solid #d5d9d9; border-radius: 6px; cursor: pointer; background: #fff; transition: border 0.15s, background 0.15s; }
.saved-address-option:has(input:checked) { border-color: #007185; background: #f0f8fa; box-shadow: 0 0 0 2px rgba(0,113,133,0.12); }
.saved-address-option input { margin-top: 4px; flex-shrink: 0; accent-color: #007185; }
.saved-address-body { flex: 1; min-width: 0; }
.saved-address-head { display: flex; align-items: center; gap: 8px; margin-bottom: 4px; font-size: 14px; color: #111; flex-wrap: wrap; }
.saved-address-text { font-size: 12.5px; color: #444; line-height: 1.55; }
.type-pill { background: #fef7e3; color: #946a00; padding: 1px 7px; border-radius: 100px; font-size: 10.5px; font-weight: 600; display: inline-flex; align-items: center; gap: 3px; }
.type-pill.type-work { background: #e7f2fb; color: #1a5a8d; }
.default-pill { background: #007600; color: #fff; padding: 1px 7px; border-radius: 100px; font-size: 10.5px; font-weight: 700; }
.btn-toggle-new { background: #fff; border: 1px dashed #007185; color: #007185; border-radius: 6px; padding: 9px 14px; font-size: 13px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 14px; }
.btn-toggle-new:hover { background: #f0f8fa; }
.btn-toggle-new.open { background: #fff5e7; border-color: #c7511f; color: #c7511f; }
#new-address-block.collapsed { display: none; }

@media (max-width: 768px) {
 .buy-now-grid { grid-template-columns: 1fr; }
 .buy-now-summary { position: static; }
 .form-row { grid-template-columns: 1fr; }
}
</style>
@endpush

@section('content')
<div class="buy-now-page">

 <h1 style="font-size: 24px; font-weight: 700; color: var(--amazon-charcoal); margin: 0 0 16px;">Buy Now</h1>

 <div id="empty-cart-warning" class="empty-cart-warning" style="display:none;">
 <i class="fas fa-exclamation-circle"></i> Your cart is empty. Add products before checking out.
 <a href="{{ url('/') }}" style="margin-left: 8px; color: var(--amazon-blue); text-decoration:underline;">Continue shopping</a>
 </div>

 <div class="buy-now-grid">
 <!-- Left: form -->
 <form class="buy-now-form" id="buy-now-form" method="POST" action="{{ url('/buy-now') }}" novalidate>
 @csrf

 <!-- Contact -->
 <h2 class="section-title"><i class="fas fa-user" style="color: var(--amazon-orange); margin-right:8px;"></i>Contact Details</h2>
 <div class="form-row">
 <div class="form-field">
 <label>Full Name <span class="req">*</span></label>
 <input type="text" name="name" id="bn-name" maxlength="120" placeholder="e.g. Kannan" required value="{{ old('name') }}">
 <div class="err-msg">Please enter your name</div>
 </div>
 <div class="form-field">
 <label>Mobile Number (Primary) <span class="req">*</span></label>
 <input type="tel" name="mobile" id="bn-mobile" maxlength="10" pattern="[0-9]{10}" placeholder="10 digit mobile" required value="{{ old('mobile') }}">
 <div class="err-msg">Enter a valid 10-digit mobile number</div>
 </div>
 </div>
 <div class="form-field">
 <label>Email Address <span style="color: var(--medium-gray); font-weight:400;">(optional, for order updates)</span></label>
 <input type="email" name="email" id="bn-email" maxlength="120" placeholder="you@example.com" value="{{ old('email') }}">
 <div class="err-msg">Enter a valid email address</div>
 </div>

 <!-- Address -->
 <h2 class="section-title" style="margin-top:24px;"><i class="fas fa-map-marker-alt" style="color: var(--amazon-orange); margin-right:8px;"></i>Delivery Address</h2>

 @if($addresses->isNotEmpty())
 <div class="saved-address-list" id="saved-address-list">
 @foreach($addresses as $addr)
 <label class="saved-address-option {{ $addr->is_default ? 'is-default' : '' }}">
 <input type="radio" name="address_id" value="{{ $addr->id }}"
 {{ $addr->is_default ? 'checked' : '' }}
 data-default="{{ $addr->is_default ? '1' : '0' }}"
 onchange="bnOnAddressChange()">
 <div class="saved-address-body">
 <div class="saved-address-head">
 <strong>{{ $addr->full_name }}</strong>
 <span class="type-pill type-{{ $addr->type }}">
 <i class="fas fa-{{ $addr->type === 'work' ? 'briefcase' : 'home' }}"></i>
 {{ ucfirst($addr->type) }}
 </span>
 @if($addr->is_default)
 <span class="default-pill">Default</span>
 @endif
 </div>
 <div class="saved-address-text">
 {{ $addr->address_line_1 }}@if($addr->address_line_2), {{ $addr->address_line_2 }}@endif,
 {{ $addr->city }}, {{ $addr->state }} &mdash; {{ $addr->pincode }}
 <br>
 <span style="color: var(--medium-gray); font-size: 12px;">
 <i class="fas fa-phone"></i> {{ $addr->mobile_primary }}@if($addr->mobile_alternate), {{ $addr->mobile_alternate }}@endif
 </span>
 </div>
 </div>
 </label>
 @endforeach
 </div>

 <button type="button" class="btn-toggle-new" id="btn-toggle-new" onclick="bnToggleNewAddress()">
 <i class="fas fa-plus-circle"></i> Add a new address
 </button>
 @endif

 <div id="new-address-block" class="{{ $addresses->isEmpty() ? '' : 'collapsed' }}" style="{{ $addresses->isEmpty() ? '' : 'display:none;' }}">
 <div class="form-field">
 <label>Full Name <span class="req">*</span></label>
 <input type="text" name="full_name" id="bn-addr-name" maxlength="120" placeholder="Recipient's name" value="{{ old('full_name') }}">
 <div class="err-msg">Please enter the recipient's name</div>
 </div>
 <div class="form-field">
 <label>Address Line 1 <span class="req">*</span></label>
 <input type="text" name="address_line_1" id="bn-addr-l1" maxlength="255" placeholder="House no., building, street, area" value="{{ old('address_line_1') }}">
 <div class="err-msg">Please enter address line 1</div>
 </div>
 <div class="form-field">
 <label>Address Line 2 <span style="color: var(--medium-gray); font-weight:400;">(optional)</span></label>
 <input type="text" name="address_line_2" id="bn-addr-l2" maxlength="255" placeholder="Landmark, locality" value="{{ old('address_line_2') }}">
 </div>
 <div class="form-row">
 <div class="form-field">
 <label>City <span class="req">*</span></label>
 <input type="text" name="city" id="bn-addr-city" maxlength="120" placeholder="City" value="{{ old('city') }}">
 <div class="err-msg">Please enter the city</div>
 </div>
 <div class="form-field">
 <label>State <span class="req">*</span></label>
 <input type="text" name="state" id="bn-addr-state" maxlength="120" placeholder="State" value="{{ old('state') }}">
 <div class="err-msg">Please enter the state</div>
 </div>
 </div>
 <div class="form-row">
 <div class="form-field">
 <label>PIN code <span class="req">*</span></label>
 <input type="text" name="pincode" id="bn-addr-pin" maxlength="6" pattern="[0-9]{6}" placeholder="6 digit PIN" value="{{ old('pincode') }}">
 <div class="err-msg">Enter a valid 6-digit PIN</div>
 </div>
 <div class="form-field">
 <label>Mobile Number <span class="req">*</span></label>
 <input type="tel" name="mobile_primary" id="bn-addr-mobile" maxlength="10" pattern="[0-9]{10}" placeholder="10 digit mobile" value="{{ old('mobile_primary') }}">
 <div class="err-msg">Enter a valid 10-digit mobile number</div>
 </div>
 </div>
 <div class="form-row">
 <div class="form-field">
 <label>Alternate Number <span style="color: var(--medium-gray); font-weight:400;">(optional)</span></label>
 <input type="tel" name="mobile_alternate" id="bn-addr-mobile-alt" maxlength="10" pattern="[0-9]{10}" placeholder="10 digit alternate" value="{{ old('mobile_alternate') }}">
 <div class="err-msg">Enter a valid 10-digit number</div>
 </div>
 <div class="form-field">
 <label>Address Type <span class="req">*</span></label>
 <div class="address-type-row">
 <label>
 <input type="radio" name="address_type" value="home" {{ old('address_type', 'home') === 'home' ? 'checked' : '' }}>
 <i class="fas fa-home"></i> Home
 </label>
 <label>
 <input type="radio" name="address_type" value="work" {{ old('address_type') === 'work' ? 'checked' : '' }}>
 <i class="fas fa-briefcase"></i> Work
 </label>
 </div>
 </div>
 </div>
 </div>

 <!-- Payment Method Selection -->
 <h2 class="section-title" style="margin-top:24px;">
 <i class="fas fa-credit-card" style="color: var(--amazon-orange); margin-right:8px;"></i>Payment Method
 </h2>
 <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:16px;">
 <label style="display:flex; align-items:center; gap:12px; padding:12px 16px; border:1px solid #d5d9d9; border-radius:6px; cursor:pointer; background:#fff; transition:all 0.15s;">
 <input type="radio" name="payment_method" value="upi" checked style="width:18px; height:18px; accent-color:#007185;">
 <div>
 <div style="font-weight:700; font-size:14px; color:#0F1111;"><i class="fas fa-qrcode" style="color:#007185; margin-right:6px;"></i> UPI / Instant Online Payment</div>
 <div style="font-size:12px; color:#565959;">Pay securely via Google Pay, PhonePe, Paytm, BHIM, or QR Code.</div>
 </div>
 </label>
 <label style="display:flex; align-items:center; gap:12px; padding:12px 16px; border:1px solid #d5d9d9; border-radius:6px; cursor:pointer; background:#fff; transition:all 0.15s;">
 <input type="radio" name="payment_method" value="cod" style="width:18px; height:18px; accent-color:#007185;">
 <div>
 <div style="font-weight:700; font-size:14px; color:#0F1111;"><i class="fas fa-hand-holding-usd" style="color:#007600; margin-right:6px;"></i> Cash on Delivery (COD)</div>
 <div style="font-size:12px; color:#565959;">Pay with cash or UPI at your doorstep upon receiving the parcel.</div>
 </div>
 </label>
 </div>

 <input type="hidden" name="items" id="bn-items">
 <input type="hidden" name="coupon_code" id="bn-coupon-code-hidden" value="">

 <button type="submit" class="btn-place" id="place-order-btn">
 <i class="fas fa-lock"></i> Place Order
 </button>
 <div class="secure-note"><i class="fas fa-shield-alt"></i> Your information is safe and will only be used to deliver your order.</div>
 </form>

 <!-- Right: order summary -->
 <div class="buy-now-summary">
 <h2 class="section-title">Order Summary</h2>
 <div id="bn-items-list"></div>
 <div class="summary-row" style="margin-top:10px;"><span>Subtotal</span><span>₹<span id="bn-subtotal">0</span></span></div>
 <div class="summary-row discount" id="bn-discount-row" style="display:none;"><span>Discount</span><span id="bn-discount">-₹0</span></div>
 <div class="summary-row"><span>Shipping</span><span style="color:#007600;">FREE</span></div>
 <div class="summary-row total"><span>Total</span><span style="color:#c7511f;">₹<span id="bn-total">0</span></span></div>

 {{-- Coupon --}}
 <div class="coupon-block">
 <div class="coupon-block-row">
 <input type="text" id="bn-coupon-code" placeholder="Have a coupon? Enter code" maxlength="32" autocomplete="off">
 <button type="button" id="bn-apply-coupon" onclick="bnApplyCoupon()">Apply</button>
 </div>
 <div id="bn-coupon-msg" class="coupon-msg" style="display:none;"></div>
 <a href="#" class="coupon-toggle" onclick="bnToggleCoupons(event)">View my coupons</a>
 <div id="bn-my-coupons" class="coupon-list" style="display:none;"></div>
 </div>

 @if($bonuses->count() > 0)
 @php $bestBonus = $bonuses->sortByDesc('bonus_percent')->first(); @endphp
 <div class="bonus-note">
 <i class="fas fa-gift"></i> Spend above ₹{{ number_format($bestBonus->min_amount, 0) }} to earn a <strong>{{ rtrim(rtrim($bestBonus->bonus_percent, '0'), '.') }}% coupon</strong> for your <strong>next</strong> order. Pay full amount today!
 </div>
 @endif
 </div>
 </div>
</div>

@php
 $hasBonuses = $bonuses->count() > 0;
 $bonusJson = $hasBonuses ? $bonuses->toJson() : '[]';
@endphp

@push('scripts')
<script>
const BN_BONUSES = {!! $bonusJson !!};
let bnDiscount = 0;
let bnItems = [];
let bnIsSingleMode = false; // true when the page was opened via the "Buy Now" button on a product

function bnRenderItems() {
 const list = document.getElementById('bn-items-list');
 const itemsField = document.getElementById('bn-items');
 if (!list || !itemsField) return;
 if (!bnItems.length) {
 list.innerHTML = '<p style="color: var(--medium-gray); text-align:center; padding: 20px 0; font-size: 13px;">No items in cart.</p>';
 document.getElementById('bn-subtotal').textContent = '0';
 document.getElementById('bn-total').textContent = '0';
 itemsField.value = '';
 document.getElementById('empty-cart-warning').style.display = 'block';
 document.getElementById('place-order-btn').disabled = true;
 return;
 }
 document.getElementById('empty-cart-warning').style.display = 'none';
 document.getElementById('place-order-btn').disabled = false;
 list.innerHTML = bnItems.map(item => `
 <div class="summary-item">
 ${item.image ? `<img src="${item.image}" alt="">` : '<div style="width:48px; height:48px; background:#f3f3f3; border-radius:4px; display:flex; align-items:center; justify-content:center; color:#cbd5e1;"><i class="fas fa-image"></i></div>'}
 <div class="info">
 <div class="name">${item.name || 'Product #' + item.id}</div>
 ${item.variation_name ? `<div style="font-size:11.5px; color:#565959; margin-top:2px;">Variation: <strong>${item.variation_name}</strong></div>` : ''}
 ${item.color ? `<div style="font-size:11.5px; color:#565959;">Color: <strong>${item.color}</strong></div>` : ''}
 ${item.options && item.options.length ? `<div style="font-size:11px; color:#007185;">Options: ${item.options.map(o => o.name).join(', ')}</div>` : ''}
 <div class="meta">
 <div class="bn-qty-row">
 <button type="button" class="bn-qty-btn" onclick="bnChangeQty(${item.id}, ${item.qty - 1})">−</button>
 <input type="number" class="bn-qty-input" value="${item.qty}" min="1" onchange="bnChangeQty(${item.id}, parseInt(this.value))">
 <button type="button" class="bn-qty-btn" onclick="bnChangeQty(${item.id}, ${item.qty + 1})">+</button>
 <span class="bn-unit-price">× ₹${Number(item.price || 0).toLocaleString('en-IN')}</span>
 </div>
 </div>
 </div>
 <div class="price">₹${(Number(item.price || 0) * item.qty).toLocaleString('en-IN')}</div>
 </div>
 `).join('');
 itemsField.value = JSON.stringify(bnItems);

 // Totals
 const subtotal = bnItems.reduce((s, i) => s + Number(i.price || 0) * i.qty, 0);
 // Prefer bnState.coupon; if it's null but the input has a typed code that
 // matches a known coupon, hydrate bnState so the discount applies immediately.
 if ((!bnState || !bnState.coupon) && bnCouponsCache) {
  const typed = (document.getElementById('bn-coupon-code')?.value || '').trim().toUpperCase();
  if (typed) {
   const m = bnCouponsCache.find(c => c.code === typed);
   if (m) bnState.coupon = m;
  }
 }
 const appliedCoupon = (typeof bnState !== 'undefined' && bnState.coupon) ? bnState.coupon : null;
 let discount = 0, label = '', showDiscount = false;

 if (appliedCoupon) {
 // Re-validate on the client as a UX nicety; the server is still the source of truth.
 if (subtotal < Number(appliedCoupon.min_amount)) {
 if (typeof bnState !== 'undefined') bnState.coupon = null;
 document.getElementById('bn-coupon-msg').className = 'coupon-msg err';
 document.getElementById('bn-coupon-msg').textContent = 'Add items worth at least ₹' + Number(appliedCoupon.min_amount).toLocaleString('en-IN') + ' to use this coupon.';
 document.getElementById('bn-coupon-msg').style.display = 'block';
 } else {
 if (appliedCoupon.type === 'percent') {
 discount = subtotal * (Number(appliedCoupon.value) / 100);
 if (appliedCoupon.max_discount && discount > Number(appliedCoupon.max_discount)) {
 discount = Number(appliedCoupon.max_discount);
 }
 } else {
 discount = Number(appliedCoupon.value);
 }
 if (discount > subtotal) discount = subtotal;
 label = 'Coupon ' + appliedCoupon.code;
 showDiscount = true;
 }
 }

 if (!showDiscount) {
 let bestBonus = null;
 BN_BONUSES.forEach(b => {
 if (subtotal >= parseFloat(b.min_amount || 0)) {
 if (!bestBonus || parseFloat(b.bonus_percent) > parseFloat(bestBonus.bonus_percent)) {
 bestBonus = b;
 }
 }
 });
 const pct = bestBonus ? parseFloat(bestBonus.bonus_percent) : bnDiscount;
 const bonusSavings = subtotal * (pct / 100);
 if (pct > 0) {
 // Show the bonus as a hint only — pay full amount now, get the coupon for next order.
 document.getElementById('bn-subtotal').textContent = subtotal.toLocaleString('en-IN');
 document.getElementById('bn-total').textContent = subtotal.toLocaleString('en-IN', {maximumFractionDigits: 0});
 document.getElementById('bn-discount-row').style.display = 'flex';
 document.getElementById('bn-discount').innerHTML =
 `<span style="color:#007600;">You'll save ₹${bonusSavings.toLocaleString('en-IN', {maximumFractionDigits: 0})}</span> <span style="color:#888; font-size:11px;">(next order, ${pct}% bonus)</span>`;
 return;
 }
 }

 document.getElementById('bn-subtotal').textContent = subtotal.toLocaleString('en-IN');
 document.getElementById('bn-total').textContent = (subtotal - discount).toLocaleString('en-IN', {maximumFractionDigits: 0});
 if (showDiscount) {
 document.getElementById('bn-discount-row').style.display = 'flex';
 document.getElementById('bn-discount').textContent = `-₹${discount.toLocaleString('en-IN', {maximumFractionDigits: 0})} (${label})`;
 } else {
 document.getElementById('bn-discount-row').style.display = 'none';
 }
}

// Change qty for an item in the buy-now summary (also updates localStorage cart)
function bnChangeQty(id, qty) {
 if (qty < 1) {
 // Remove the item from this buy-now view
 bnItems = bnItems.filter(i => i.id !== id);
 // Only sync to the main cart if we're in cart-checkout mode (not Buy Now)
 if (!bnIsSingleMode) updateCartQty(id, 0);
 } else {
 const item = bnItems.find(i => i.id === id);
 if (item) {
 item.qty = qty;
 if (!bnIsSingleMode) updateCartQty(id, qty);
 }
 }
 updateCartCount();
 bnRenderItems();
}

// Load cart from localStorage and refresh pricing
async function bnLoad() {
 const url = new URL(window.location.href);

 // If a coupon code came in via ?coupon_code=… (from cart checkout), pre-fill
 // it so the auto-apply kicks in and updates the totals immediately.
 const cpnParam = (url.searchParams.get('coupon_code') || '').trim().toUpperCase();
 if (cpnParam) {
  const cpnInput = document.getElementById('bn-coupon-code');
  if (cpnInput) cpnInput.value = cpnParam;
 }

 // Pre-warm the coupons cache so the input value can be matched synchronously
 // during the first bnRenderTotals() call below.
 if (cpnParam) {
  try { await bnFetchCoupons(); } catch (e) {}
 }

 // If the user came in via "Buy Now" on a product page, show ONLY that item
 // — leave the cart untouched.
 if (url.searchParams.get('single') === '1') {
 const single = JSON.parse(localStorage.getItem('buyNowItem') || 'null');
 if (single && single.id) {
 bnIsSingleMode = true;
 bnItems = [{ id: single.id, name: single.name, slug: single.slug, image: single.image, price: single.price, qty: single.qty || 1 }];
 bnRenderItems();
 if (cpnParam) bnApplyCoupon(); else bnRenderItems();
 return;
 }
 }
 const cart = getCart();
 if (!cart.length) {
 bnItems = [];
 bnRenderItems();
 return;
 }
 // (Final coupon-apply happens at the bottom of bnLoad after items are loaded.)
 try {
 const res = await fetch('/api/cart/items', {
 method: 'POST',
 headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || ''},
 body: JSON.stringify({ ids: cart.map(c => c.id) })
 });
 const data = await res.json();
 if (data.success && data.data) {
 bnItems = data.data.map(p => {
 const local = cart.find(c => c.id === p.id);
 return { id: p.id, name: p.name, slug: p.slug, image: p.image, price: p.price, qty: local ? local.qty : 1 };
 });
 bnRenderItems();
 }
 } catch (e) {
 console.error('Failed to load cart details', e);
 bnItems = cart;
 bnRenderItems();
 }

 // After everything is loaded, apply the URL-passed coupon (if any) so the
 // discount shows in the totals on the very first paint.
 if (cpnParam) {
  bnApplyCoupon();
 } else {
  bnRenderItems();
 }
}

// Address picker — toggle the "new address" block
function bnNewAddressVisible() {
 const blk = document.getElementById('new-address-block');
 return blk && blk.style.display !== 'none';
}

function bnToggleNewAddress() {
 const blk = document.getElementById('new-address-block');
 const btn = document.getElementById('btn-toggle-new');
 const opening = blk.style.display === 'none' || !blk.style.display;
 blk.style.display = opening ? 'block' : 'none';
 if (btn) {
 btn.classList.toggle('open', opening);
 btn.innerHTML = opening
 ? '<i class="fas fa-times-circle"></i> Cancel new address'
 : '<i class="fas fa-plus-circle"></i> Add a new address';
 }
 // Uncheck saved addresses while new-address mode is on
 if (opening) {
 document.querySelectorAll('input[name="address_id"]').forEach(r => r.checked = false);
 const first = blk.querySelector('input[type=text]');
 if (first) first.focus();
 } else {
 // Re-select the default when collapsing
 const def = document.querySelector('input[name="address_id"][data-default="1"]')
 || document.querySelector('input[name="address_id"]');
 if (def) def.checked = true;
 }
}

// When a saved address is picked, hide the new-address block
function bnOnAddressChange() {
 const blk = document.getElementById('new-address-block');
 const btn = document.getElementById('btn-toggle-new');
 if (blk) blk.style.display = 'none';
 if (btn) {
 btn.classList.remove('open');
 btn.innerHTML = '<i class="fas fa-plus-circle"></i> Add a new address';
 }
}

// Form validation
function bnValidate() {
 let valid = true;
 const fields = [
 {id: 'bn-name', pattern: /^.+$/, msg: 'Please enter your name'},
 {id: 'bn-mobile', pattern: /^[0-9]{10}$/, msg: 'Enter a valid 10-digit mobile number'},
 ];

 // Validate email only if filled
 const emailEl = document.getElementById('bn-email');
 if (emailEl.value.trim() !== '' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailEl.value.trim())) {
 emailEl.classList.add('error');
 emailEl.parentElement.classList.add('has-error');
 valid = false;
 } else {
 emailEl.classList.remove('error');
 emailEl.parentElement.classList.remove('has-error');
 }

 // Contact fields always required
 fields.forEach(f => {
 const el = document.getElementById(f.id);
 if (!el) return;
 if (!f.pattern.test(el.value.trim())) {
 el.classList.add('error');
 el.parentElement.classList.add('has-error');
 valid = false;
 } else {
 el.classList.remove('error');
 el.parentElement.classList.remove('has-error');
 }
 });

 // Address handling — either pick an existing one OR fill the new-address form
 const savedPicked = document.querySelector('input[name="address_id"]:checked');
 const newOpen = bnNewAddressVisible();

 if (!savedPicked && !newOpen) {
 alert('Please choose a delivery address or add a new one.');
 return false;
 }

 if (newOpen) {
 const newFields = [
 {id: 'bn-addr-name', pattern: /^.+$/},
 {id: 'bn-addr-l1', pattern: /^.+$/},
 {id: 'bn-addr-city', pattern: /^.+$/},
 {id: 'bn-addr-state', pattern: /^.+$/},
 {id: 'bn-addr-pin', pattern: /^[0-9]{6}$/},
 {id: 'bn-addr-mobile', pattern: /^[0-9]{10}$/},
 ];
 newFields.forEach(f => {
 const el = document.getElementById(f.id);
 if (!el) return;
 if (!f.pattern.test(el.value.trim())) {
 el.classList.add('error');
 el.parentElement.classList.add('has-error');
 valid = false;
 } else {
 el.classList.remove('error');
 el.parentElement.classList.remove('has-error');
 }
 });

 // Address type
 const addressType = document.querySelector('input[name="address_type"]:checked');
 const typeRow = document.querySelector('#new-address-block .address-type-row');
 if (!addressType) {
 if (typeRow) typeRow.style.outline = '1px solid #c7511f';
 valid = false;
 } else if (typeRow) {
 typeRow.style.outline = 'none';
 }

 // Alternate number — only validate if filled
 const alt = document.getElementById('bn-addr-mobile-alt');
 if (alt && alt.value.trim() !== '' && !/^[0-9]{10}$/.test(alt.value.trim())) {
 alt.classList.add('error');
 alt.parentElement.classList.add('has-error');
 valid = false;
 } else if (alt) {
 alt.classList.remove('error');
 alt.parentElement.classList.remove('has-error');
 }
 }

 return valid;
}

// Live validation: clear error on input
['bn-name','bn-mobile','bn-email','bn-addr-name','bn-addr-l1','bn-addr-l2','bn-addr-city','bn-addr-state','bn-addr-pin','bn-addr-mobile','bn-addr-mobile-alt'].forEach(id => {
 const el = document.getElementById(id);
 if (!el) return;
 el.addEventListener('input', () => {
 el.classList.remove('error');
 el.parentElement.classList.remove('has-error');
 });
});

document.getElementById('buy-now-form').addEventListener('submit', function(e) {
 if (!bnValidate()) {
 e.preventDefault();
 const firstError = document.querySelector('.form-field.has-error input');
 if (firstError) firstError.focus();
 return;
 }
 if (!bnItems.length) {
 e.preventDefault();
 alert('Your cart is empty.');
 return;
 }
 // If a saved address is picked, ensure the new-address inputs don't accidentally
 // get sent (they would be ignored server-side but keep the payload clean).
 if (document.querySelector('input[name="address_id"]:checked') && !bnNewAddressVisible()) {
 document.querySelectorAll('#new-address-block input').forEach(i => i.disabled = true);
 }
 // Sync the chosen coupon code into the hidden field before submit
 const codeEl = document.getElementById('bn-coupon-code');
 const hidden = document.getElementById('bn-coupon-code-hidden');
 if (hidden) hidden.value = (bnState.coupon ? bnState.coupon.code : (codeEl ? codeEl.value.trim().toUpperCase() : ''));
 // Clear local cart after successful submit
 setTimeout(() => localStorage.setItem(CART_KEY, '[]'), 100);
});

// === Coupon state & helpers ===
const bnState = { coupon: null };

function bnApplyCoupon() {
 const input = document.getElementById('bn-coupon-code');
 const code = (input.value || '').trim().toUpperCase();
 if (!code) { bnCouponMsg('Enter a coupon code.', 'err'); return; }
 bnFetchCoupons().then(list => {
 const m = list.find(c => c.code === code);
 if (!m) { bnCouponMsg('Invalid coupon code.', 'err'); return; }
 const subtotal = bnItems.reduce((s, i) => s + Number(i.price || 0) * i.qty, 0);
 if (subtotal < Number(m.min_amount)) {
 bnCouponMsg('Add items worth at least ₹' + Number(m.min_amount).toLocaleString('en-IN') + ' to use this coupon.', 'err');
 return;
 }
 bnState.coupon = m;
 bnCouponMsg('Coupon ' + m.code + ' applied! ' + m.display, 'ok');
 bnRenderItems();
 });
}

function bnToggleCoupons(e) {
 e.preventDefault();
 const box = document.getElementById('bn-my-coupons');
 if (box.style.display === 'none' || !box.style.display) {
 box.innerHTML = '<div style="font-size:11.5px;color:#888;">Loading…</div>';
 box.style.display = 'block';
 bnFetchCoupons().then(list => {
 if (!list.length) {
 box.innerHTML = '<div style="font-size:11.5px;color:#888;">No active coupons right now. Earn one by placing an order.</div>';
 return;
 }
 box.innerHTML = list.map(c =>
 '<div class="coupon-item"><div class="ci-body"><div class="ci-code">' + c.code + '</div>' +
 '<div class="ci-min">' + c.display + (c.min_amount > 0 ? ' · Min ₹' + Number(c.min_amount).toLocaleString('en-IN') : '') + '</div></div>' +
 '<button class="ci-use" data-code="' + c.code + '">Use</button></div>'
 ).join('');
 box.querySelectorAll('.ci-use').forEach(btn => {
 btn.addEventListener('click', () => {
 document.getElementById('bn-coupon-code').value = btn.dataset.code;
 bnApplyCoupon();
 });
 });
 });
 } else {
 box.style.display = 'none';
 }
}

let bnCouponsCache = null;
function bnFetchCoupons() {
 if (bnCouponsCache) return Promise.resolve(bnCouponsCache);
 return fetch('/api/coupons/mine', { headers: { 'Accept': 'application/json' }})
 .then(r => r.json()).then(j => { bnCouponsCache = (j && j.data) ? j.data : []; return bnCouponsCache; })
 .catch(() => []);
}

function bnCouponMsg(msg, kind) {
 const el = document.getElementById('bn-coupon-msg');
 el.textContent = msg;
 el.className = 'coupon-msg ' + (kind || 'err');
 el.style.display = 'block';
}

document.addEventListener('DOMContentLoaded', () => {
 bnLoad();
 // Auto-apply coupon as user types (no need to click Apply).
 // We only re-apply when the value reaches typical coupon length (≥ 4 chars),
 // or is cleared — to avoid spamming the API on every keystroke.
 const cpnInput = document.getElementById('bn-coupon-code');
 if (cpnInput) {
  let lastValue = '';
  cpnInput.addEventListener('input', () => {
   const v = (cpnInput.value || '').trim().toUpperCase();
   cpnInput.value = v; // visual uppercase
   if (v === lastValue) return;
   lastValue = v;
   if (!v) {
    bnState.coupon = null;
    document.getElementById('bn-coupon-msg').style.display = 'none';
    bnRenderItems();
    return;
   }
   if (v.length >= 4) bnApplyCoupon();
  });
 }
});
</script>
@endpush
@endsection

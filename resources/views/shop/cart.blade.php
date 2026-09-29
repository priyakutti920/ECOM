@extends('layouts.shop')

@section('title', 'Shopping Cart — ' . $storeName)

@push('styles')
<style>
.cart-page { max-width: 1400px; margin: 0 auto; padding: 16px; }
.cart-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 16px; align-items: start; }
.cart-main { background: #fff; padding: 20px; border-radius: 4px; border: 1px solid #e7e7e7; }
.cart-title { font-size: 24px; font-weight: 700; color: var(--amazon-charcoal); margin: 0 0 16px; padding-bottom: 12px; border-bottom: 1px solid #e7e7e7; }
.cart-subtitle { font-size: 13px; color: var(--medium-gray); font-weight: 400; margin-left: 8px; }
.cart-empty { text-align: center; padding: 60px 20px; }
.cart-empty i { font-size: 80px; color: #cbd5e1; display: block; margin-bottom: 16px; }
.cart-empty h3 { font-size: 20px; font-weight: 700; color: var(--amazon-charcoal); margin-bottom: 8px; }
.cart-empty p { color: var(--medium-gray); font-size: 14px; margin-bottom: 24px; }
.cart-empty a { display: inline-block; padding: 12px 32px; background: var(--amazon-orange); color: var(--amazon-dark); border-radius: 100px; font-weight: 600; text-decoration: none; }
.cart-item { display: grid; grid-template-columns: 100px 1fr auto; gap: 16px; padding: 16px 0; border-bottom: 1px solid #e7e7e7; align-items: start; }
.cart-item:last-child { border-bottom: none; }
.cart-item-img { width: 100px; height: 100px; background: #f3f3f3; border-radius: 4px; display: flex; align-items: center; justify-content: center; overflow: hidden; }
.cart-item-img img { width: 100%; height: 100%; object-fit: cover; }
.cart-item-info { display: flex; flex-direction: column; gap: 6px; }
.cart-item-name { font-size: 16px; font-weight: 600; color: var(--amazon-charcoal); text-decoration: none; }
.cart-item-name:hover { color: #c45500; text-decoration: underline; }
.cart-item-stock { font-size: 13px; color: #007600; font-weight: 600; }
.cart-item-stock.oos { color: #c7511f; }
.cart-item-price { font-size: 18px; font-weight: 700; color: #0F1111; }
.cart-item-actions { display: flex; align-items: center; gap: 8px; margin-top: 8px; }
.cart-qty { display: flex; align-items: center; border: 1px solid #d5d9d9; border-radius: 4px; overflow: hidden; }
.cart-qty button { background: #f0f2f2; border: none; width: 28px; height: 28px; cursor: pointer; font-size: 14px; }
.cart-qty button:hover { background: #e7e7e7; }
.cart-qty input { width: 36px; height: 28px; text-align: center; border: none; border-left: 1px solid #d5d9d9; border-right: 1px solid #d5d9d9; font-size: 13px; font-weight: 600; }
.cart-action-btn { background: none; border: 1px solid #d5d9d9; border-radius: 4px; padding: 4px 10px; font-size: 12px; cursor: pointer; color: var(--amazon-blue); }
.cart-action-btn:hover { background: #f7f7f7; }
.cart-action-btn.delete { color: #c7511f; }
.cart-item-right { text-align: right; font-size: 18px; font-weight: 700; color: #c7511f; }
.cart-summary { background: #fff; padding: 20px; border-radius: 4px; border: 1px solid #e7e7e7; position: sticky; top: 80px; }
.summary-row { display: flex; justify-content: space-between; padding: 6px 0; font-size: 13px; color: #0F1111; }
.summary-row.total { font-size: 18px; font-weight: 700; padding-top: 12px; margin-top: 8px; border-top: 1px solid #e7e7e7; }
.summary-row.discount { color: #c7511f; }
.summary-bonus { background: #f0f7ff; border: 1px solid #cce0ff; padding: 8px 10px; border-radius: 4px; font-size: 12px; color: #0a4b87; margin: 10px 0; }
.checkout-btn { width: 100%; background: #ffd814; border: 1px solid #fcd200; border-radius: 100px; padding: 12px; font-size: 14px; font-weight: 600; cursor: pointer; margin-top: 12px; }
.checkout-btn:hover { background: #f7ca00; }
.checkout-btn:disabled { opacity: 0.5; cursor: not-allowed; }
.continue-btn { width: 100%; background: #fff; border: 1px solid #d5d9d9; border-radius: 100px; padding: 10px; font-size: 13px; cursor: pointer; margin-top: 8px; }
.continue-btn:hover { background: #f7f7f7; }
.secure-note { font-size: 11px; color: var(--medium-gray); text-align: center; margin-top: 10px; display: flex; align-items: center; justify-content: center; gap: 4px; }
.secure-note i { color: #007600; }
.clear-cart-btn { background: none; border: 1px solid #c7511f; color: #c7511f; border-radius: 4px; padding: 6px 12px; font-size: 12px; cursor: pointer; }
.clear-cart-btn:hover { background: #fef2f2; }
.cart-promo-input { display: flex; gap: 6px; margin: 12px 0; }
.cart-promo-input input { flex: 1; padding: 8px 10px; border: 1px solid #d5d9d9; border-radius: 4px; font-size: 13px; }
.cart-promo-input button { padding: 8px 14px; background: #f0f2f2; border: 1px solid #d5d9d9; border-radius: 4px; font-size: 12px; cursor: pointer; }

@media (max-width: 992px) {
 .cart-grid { grid-template-columns: 1fr; }
 .cart-summary { position: static; }
 .cart-item { grid-template-columns: 80px 1fr; }
 .cart-item-img { width: 80px; height: 80px; }
 .cart-item-right { grid-column: 1 / -1; text-align: left; padding-left: 96px; }
}
</style>
@endpush

@section('content')
<div class="cart-page">
 <h1 class="cart-title">Shopping Cart<span class="cart-subtitle" id="cart-count-display"></span></h1>

 <div id="cart-empty" class="cart-empty" style="display:none; background:#fff; border-radius:4px; border:1px solid #e7e7e7;">
 <i class="fas fa-shopping-cart"></i>
 <h3>Your {{ $storeName }} Cart is empty</h3>
 <p>Check your saved-for-later items below or continue shopping.</p>
 <a href="{{ url('/') }}">Continue Shopping</a>
 </div>

 <div id="cart-with-items" class="cart-grid" style="display:none;">
 <div class="cart-main">
 <div style="display:flex; justify-content:space-between; align-items:center; padding-bottom:12px; border-bottom:1px solid #e7e7e7; margin-bottom:12px;">
 <div style="font-size:14px; color: var(--medium-gray);">Price</div>
 <button class="clear-cart-btn" onclick="clearCart()">Clear cart</button>
 </div>
 <div id="cart-items-list"></div>
 <div style="text-align:right; padding-top:16px; font-size:18px;">
 Subtotal (<span id="cart-items-count">0</span> items): <strong style="color:#c7511f;">₹<span id="cart-subtotal">0</span></strong>
 </div>
 </div>

 <div class="cart-summary">
 <div class="cart-promo-input">
 <input type="text" id="promo-input" placeholder="Enter promo code">
 <button onclick="applyPromo()">Apply</button>
 </div>

 {{-- Customer coupons (auto-issued from past orders / bonuses) --}}
 @auth('customer')
 @if($coupons->count() > 0)
 <div class="cpn-list">
 <div class="cpn-list-head"><i class="fas fa-ticket-alt"></i> Your coupons</div>
 @foreach($coupons as $cp)
 @php
 $isPct = $cp->type === 'percent';
 $label = $isPct ? rtrim(rtrim(number_format($cp->value, 2), '0'), '.') . '% OFF' : '₹' . number_format($cp->value, 0) . ' OFF';
 $minNote = $cp->min_amount > 0 ? 'Min order ₹' . number_format($cp->min_amount, 0) : 'No minimum';
 @endphp
 <div class="cpn-row" data-code="{{ $cp->code }}" data-value="{{ $cp->value }}" data-type="{{ $cp->type }}" data-min="{{ (float) $cp->min_amount }}">
 <div class="cpn-icon"><i class="fas fa-tag"></i></div>
 <div class="cpn-body">
 <div class="cpn-name">{{ $label }}</div>
 <div class="cpn-code">{{ $cp->code }} · {{ $minNote }}</div>
 </div>
 <button type="button" class="cpn-apply" onclick="applyCouponFromList('{{ $cp->code }}', this)">Apply</button>
 </div>
 @endforeach
 </div>
 @endif
 @endauth

 <div class="summary-row"><span>Subtotal</span><span>₹<span id="summary-subtotal">0</span></span></div>
 <div class="summary-row discount" id="summary-discount-row" style="display:none;"><span>Discount <span id="applied-coupon-label" style="color:#888;"></span> <a href="javascript:void(0)" id="remove-coupon" style="font-size:11px; color:#007185; text-decoration:underline; display:none;" onclick="removeAppliedCoupon();">Remove</a></span><span id="summary-discount">-₹0</span></div>
 @if($bonuses->count() > 0)
 @php $bestBonus = $bonuses->sortByDesc('bonus_percent')->first(); @endphp
 <div class="summary-bonus">
 <i class="fas fa-gift"></i> Spend above ₹{{ number_format($bestBonus->min_amount, 0) }} to earn a <strong>{{ rtrim(rtrim($bestBonus->bonus_percent, '0'), '.') }}% coupon</strong> for your <strong>next</strong> order. Pay full amount today!
 </div>
 @endif
 <div class="summary-row total"><span>Total</span><span style="color:#c7511f;">₹<span id="summary-total">0</span></span></div>
 <button class="checkout-btn" onclick="if(getCart().length===0){showToast('Your cart is empty','warning');return;} var cp=document.getElementById('promo-input').value; var u='{{ url('/buy-now') }}'+(cp?'?coupon_code='+encodeURIComponent(cp):''); window.location=u;">Proceed to Checkout</button>
 <a href="{{ url('/') }}" class="continue-btn" style="text-decoration:none; display:block; text-align:center; color:#0F1111;">Continue Shopping</a>
 <div class="secure-note"><i class="fas fa-lock"></i> Secure checkout powered by {{ $storeName }}</div>
 </div>
 </div>
</div>

@php
 $hasBonuses = $bonuses->count() > 0;
 $bonusJson = $hasBonuses ? $bonuses->toJson() : '[]';
@endphp

@push('scripts')
<script>
const BONUSES = {!! $bonusJson !!};
let appliedDiscount = 0;
let appliedBonusPct = 0;

function renderCart() {
 const cart = getCart();
 const empty = document.getElementById('cart-empty');
 const withItems = document.getElementById('cart-with-items');
 const list = document.getElementById('cart-items-list');

 if (cart.length === 0) {
 empty.style.display = 'block';
 withItems.style.display = 'none';
 document.getElementById('cart-count-display').textContent = '';
 return;
 }

 empty.style.display = 'none';
 withItems.style.display = 'grid';

 list.innerHTML = cart.map(item => `
 <div class="cart-item" data-id="${item.id}">
 <a href="/product/${item.slug || item.id}" class="cart-item-img">
 ${item.image ? `<img src="${item.image}" alt="${item.name}">` : '<i class="fas fa-image" style="color:#cbd5e1; font-size:24px;"></i>'}
 </a>
 <div class="cart-item-info">
 <a href="/product/${item.slug || item.id}" class="cart-item-name">${item.name || 'Product #' + item.id}</a>
 ${item.variation_name ? `<div style="font-size:12px; color:#565959; margin-top:2px;">Variation: <strong>${item.variation_name}</strong></div>` : ''}
 ${item.color ? `<div style="font-size:12px; color:#565959;">Color: <strong>${item.color}</strong></div>` : ''}
 ${item.options && item.options.length ? `<div style="font-size:11.5px; color:#007185;">Options: ${item.options.map(o => o.name).join(', ')}</div>` : ''}
 <div class="cart-item-stock"><i class="fas fa-check-circle"></i> In stock</div>
 <div class="cart-item-price">₹${Number(item.price || 0).toLocaleString('en-IN')}</div>
 <div class="cart-item-actions">
 <div class="cart-qty">
 <button onclick="changeCartQty(${item.id}, ${item.qty - 1})">−</button>
 <input type="number" value="${item.qty}" min="1" onchange="changeCartQty(${item.id}, parseInt(this.value))">
 <button onclick="changeCartQty(${item.id}, ${item.qty + 1})">+</button>
 </div>
 <button class="cart-action-btn delete" onclick="removeCartItem(${item.id})">Delete</button>
 <button class="cart-action-btn" onclick="saveForLater(${item.id})">Save for later</button>
 </div>
 </div>
 <div class="cart-item-right">₹${(Number(item.price || 0) * item.qty).toLocaleString('en-IN')}</div>
 </div>
 `).join('');

 updateCartTotals();
 document.getElementById('cart-count-display').textContent = `(${cart.length} ${cart.length === 1 ? 'item' : 'items'})`;
}

function changeCartQty(id, qty) {
 if (qty < 1) { removeCartItem(id); return; }
 updateCartQty(id, qty);
 renderCart();
}

function removeCartItem(id) {
 removeFromCart(id);
 renderCart();
}

function clearCart() {
 if (confirm('Remove all items from cart?')) {
 localStorage.setItem(CART_KEY, '[]');
 renderCart();
 updateCartCount();
 }
}

function saveForLater(id) {
 const cart = getCart();
 const item = cart.find(c => c.id === id);
 if (item) {
 const later = JSON.parse(localStorage.getItem('store_saved') || localStorage.getItem('nellai_saved') || '[]');
 later.push(item);
 localStorage.setItem('store_saved', JSON.stringify(later));
 localStorage.setItem('nellai_saved', JSON.stringify(later));
 removeFromCart(id);
 renderCart();
 showToast('Saved for later!', 'info');
 }
}

function applyPromo() {
 const code = (document.getElementById('promo-input').value || '').trim().toUpperCase();
 if (!code) return;
 // Match against any of the customer coupons rendered as cards.
 const row = document.querySelector('.cpn-row[data-code="' + code + '"]');
 if (row) {
 applyCouponFromList(code, row.querySelector('.cpn-apply'));
 return;
 }
 const bonus = BONUSES.find(b => (b.name || '').toLowerCase() === code.toLowerCase());
 if (bonus) {
 appliedBonusPct = parseFloat(bonus.bonus_percent || 0);
 showToast(`Promo applied: ${appliedBonusPct}% bonus tier active!`, 'success');
 } else {
 showToast('Invalid promo code', 'error');
 }
 updateCartTotals();
}

function applyCouponFromList(code, btn) {
 const cart = getCart();
 const subtotal = cart.reduce((s, i) => s + Number(i.price || 0) * i.qty, 0);
 const row = btn.closest('.cpn-row');
 const min = parseFloat(row.dataset.min || 0);
 if (min > 0 && subtotal < min) {
 showToast('Add items worth at least ₹' + min.toLocaleString('en-IN') + ' to use this coupon.', 'error');
 return;
 }
 const type = row.dataset.type;
 const value = parseFloat(row.dataset.value);
 let discount = type === 'percent' ? (subtotal * value / 100) : value;
 if (discount > subtotal) discount = subtotal;
 appliedDiscount = discount;
 document.getElementById('promo-input').value = code;
 document.getElementById('summary-discount-row').style.display = 'flex';
 document.getElementById('summary-discount').textContent = '-₹' + discount.toLocaleString('en-IN');
 document.getElementById('applied-coupon-label').textContent = '(' + code + ')';
 document.getElementById('remove-coupon').style.display = 'inline';
 document.querySelectorAll('.cpn-apply').forEach(b => { b.textContent = 'Apply'; b.disabled = false; });
 btn.textContent = 'Applied ✓';
 btn.disabled = true;
 updateCartTotals();
 showToast('Coupon ' + code + ' applied.', 'success');
}

function removeAppliedCoupon() {
 appliedDiscount = 0;
 document.getElementById('promo-input').value = '';
 document.getElementById('summary-discount-row').style.display = 'none';
 document.getElementById('summary-discount').textContent = '-₹0';
 document.getElementById('applied-coupon-label').textContent = '';
 document.getElementById('remove-coupon').style.display = 'none';
 document.querySelectorAll('.cpn-apply').forEach(b => { b.textContent = 'Apply'; b.disabled = false; });
 updateCartTotals();
}

function updateCartTotals() {
 const cart = getCart();
 const subtotal = cart.reduce((sum, item) => sum + Number(item.price || 0) * item.qty, 0);
 const itemCount = cart.reduce((sum, item) => sum + item.qty, 0);

 // Auto-apply best bonus by amount
 let bestBonus = null;
 BONUSES.forEach(b => {
 if (subtotal >= parseFloat(b.min_amount || 0)) {
 if (!bestBonus || parseFloat(b.bonus_percent) > parseFloat(bestBonus.bonus_percent)) {
 bestBonus = b;
 }
 }
 });

 const totalBonusPct = bestBonus ? parseFloat(bestBonus.bonus_percent) : appliedBonusPct;
 const bonusSavings = subtotal * (totalBonusPct / 100);

 // Current order total: only reduced by an applied coupon, never by the bonus.
 const currentTotal = subtotal - appliedDiscount;

 document.getElementById('cart-subtotal').textContent = subtotal.toLocaleString('en-IN');
 document.getElementById('cart-items-count').textContent = itemCount;
 document.getElementById('summary-subtotal').textContent = subtotal.toLocaleString('en-IN');
 document.getElementById('summary-total').textContent = currentTotal.toLocaleString('en-IN', {maximumFractionDigits: 0});

 // Show discount row either for applied coupon, or for the bonus hint.
 if (appliedDiscount > 0) {
 // The coupon discount line is already filled in by applyCouponFromList()
 } else if (totalBonusPct > 0) {
 document.getElementById('summary-discount-row').style.display = 'flex';
 document.getElementById('summary-discount').innerHTML =
 `<span style="color:#007600;">You'll save ₹${bonusSavings.toLocaleString('en-IN', {maximumFractionDigits: 0})}</span> <span style="color:#888; font-size:11px;">(next order, ${totalBonusPct}% bonus)</span>`;
 document.getElementById('applied-coupon-label').textContent = '';
 document.getElementById('remove-coupon').style.display = 'none';
 } else {
 document.getElementById('summary-discount-row').style.display = 'none';
 }
}

document.addEventListener('DOMContentLoaded', renderCart);
</script>
@endpush

@endsection

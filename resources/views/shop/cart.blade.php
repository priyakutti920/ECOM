@extends('layouts.shop')

@section('title', 'Your Shopping Cart — ' . $storeName)

@push('styles')
<style>
.cart-page-wrapper { max-width: 1300px; margin: 24px auto 60px; padding: 0 16px; }
.cart-header-row { display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 20px; }
.cart-title { font-family: var(--font-heading); font-size: 26px; font-weight: 800; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 10px; }
.cart-title .cart-badge { font-size: 14px; font-weight: 600; color: var(--medium-gray); font-family: var(--font-sans); }

/* Free Shipping Goal Meter */
.shipping-meter-card {
  background: #ffffff;
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 16px 20px;
  margin-bottom: 24px;
  box-shadow: var(--shadow-card);
}
.meter-text { font-size: 14px; font-weight: 600; color: #0f172a; display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }
.meter-text i { color: var(--brand-accent); font-size: 16px; }
.meter-bar { width: 100%; height: 8px; background: #e2e8f0; border-radius: 100px; overflow: hidden; }
.meter-fill { height: 100%; background: var(--brand-accent-gradient); border-radius: 100px; transition: width 0.4s ease; width: 0%; }
.meter-fill.qualified { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }

.cart-layout { display: grid; grid-template-columns: 1fr 380px; gap: 28px; align-items: start; }
.cart-main-card {
  background: #ffffff;
  border-radius: var(--radius-lg);
  border: 1px solid var(--border);
  padding: 24px;
  box-shadow: var(--shadow-card);
}
.cart-table-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-bottom: 16px;
  border-bottom: 1px solid var(--border);
  margin-bottom: 12px;
}
.cart-table-top span { font-size: 13px; font-weight: 600; color: var(--medium-gray); text-transform: uppercase; letter-spacing: 0.5px; }
.clear-cart-btn {
  background: none;
  border: none;
  color: #ef4444;
  font-size: 12px;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  border-radius: 6px;
  transition: var(--transition);
}
.clear-cart-btn:hover { background: #fee2e2; }

/* Item Row */
.cart-item-row {
  display: grid;
  grid-template-columns: 96px 1fr auto;
  gap: 20px;
  padding: 20px 0;
  border-bottom: 1px solid var(--border-light);
  align-items: center;
}
.cart-item-row:last-child { border-bottom: none; }
.cart-item-thumb {
  width: 96px;
  height: 96px;
  background: #f8fafc;
  border-radius: var(--radius-md);
  border: 1px solid var(--border-light);
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
}
.cart-item-thumb img { width: 100%; height: 100%; object-fit: contain; }
.cart-item-info { display: flex; flex-direction: column; gap: 6px; }
.cart-item-title { font-size: 15px; font-weight: 700; color: #0f172a; text-decoration: none; line-height: 1.35; }
.cart-item-title:hover { color: var(--brand-accent); }
.cart-item-meta { font-size: 12px; color: var(--medium-gray); }
.cart-item-price-unit { font-size: 15px; font-weight: 700; color: #0f172a; font-family: var(--font-heading); }

/* Quantity Stepper */
.cart-item-actions-row { display: flex; align-items: center; gap: 16px; margin-top: 6px; flex-wrap: wrap; }
.stepper {
  display: inline-flex;
  align-items: center;
  background: #f1f5f9;
  border-radius: var(--radius-pill);
  padding: 3px;
  border: 1px solid var(--border);
}
.stepper button {
  width: 28px;
  height: 28px;
  border-radius: 50%;
  border: none;
  background: #ffffff;
  color: #0f172a;
  font-weight: 700;
  font-size: 14px;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: var(--transition);
  box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.stepper button:hover { background: var(--brand-accent); color: #ffffff; }
.stepper input {
  width: 36px;
  background: transparent;
  border: none;
  text-align: center;
  font-size: 13px;
  font-weight: 700;
  color: #0f172a;
  outline: none;
}
.item-action-link {
  font-size: 12px;
  font-weight: 600;
  color: var(--medium-gray);
  background: none;
  border: none;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  gap: 4px;
  transition: var(--transition);
}
.item-action-link:hover { color: var(--brand-accent); }
.item-action-link.remove:hover { color: #ef4444; }

.cart-item-line-total {
  text-align: right;
  font-family: var(--font-heading);
  font-size: 17px;
  font-weight: 800;
  color: #0f172a;
}

/* Summary Card */
.cart-summary-box {
  background: #ffffff;
  border: 1px solid var(--border);
  border-radius: var(--radius-lg);
  padding: 24px;
  box-shadow: var(--shadow-card);
  position: sticky;
  top: 90px;
}
.summary-heading { font-family: var(--font-heading); font-size: 18px; font-weight: 800; color: #0f172a; margin: 0 0 16px; padding-bottom: 12px; border-bottom: 1px solid var(--border); }
.promo-box { display: flex; gap: 8px; margin-bottom: 16px; }
.promo-box input {
  flex: 1;
  padding: 10px 14px;
  border: 1.5px solid var(--border);
  border-radius: var(--radius-pill);
  font-size: 13px;
  outline: none;
  transition: var(--transition);
}
.promo-box input:focus { border-color: var(--brand-accent); }
.promo-box button {
  padding: 10px 18px;
  border: none;
  background: #0f172a;
  color: #ffffff;
  border-radius: var(--radius-pill);
  font-weight: 700;
  font-size: 13px;
  cursor: pointer;
  transition: var(--transition);
}
.promo-box button:hover { background: #1e293b; }

.summary-line { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; font-size: 14px; color: #475569; }
.summary-line.grand-total {
  border-top: 1.5px dashed var(--border);
  margin-top: 12px;
  padding-top: 16px;
  font-size: 18px;
  font-weight: 800;
  color: #0f172a;
}
.summary-line.grand-total strong { font-family: var(--font-heading); font-size: 24px; color: var(--brand-accent); }
.summary-discount { color: #10b981; font-weight: 700; }

.btn-proceed-checkout {
  width: 100%;
  padding: 14px 20px;
  background: var(--brand-accent-gradient);
  color: #ffffff;
  border: none;
  border-radius: var(--radius-pill);
  font-size: 15px;
  font-weight: 800;
  cursor: pointer;
  box-shadow: 0 4px 16px rgba(249, 115, 22, 0.35);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  margin-top: 18px;
  transition: var(--transition);
}
.btn-proceed-checkout:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 22px rgba(249, 115, 22, 0.45);
}
.btn-continue-shop {
  width: 100%;
  padding: 11px;
  background: #f1f5f9;
  color: #334155;
  border: none;
  border-radius: var(--radius-pill);
  font-size: 13px;
  font-weight: 700;
  text-align: center;
  display: block;
  text-decoration: none;
  margin-top: 10px;
  transition: var(--transition);
}
.btn-continue-shop:hover { background: #e2e8f0; }

.cart-security-badge {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  margin-top: 16px;
  font-size: 11px;
  color: var(--medium-gray);
}
.cart-security-badge i { color: #10b981; font-size: 13px; }

/* Empty state */
.empty-cart-card {
  background: #ffffff;
  border-radius: var(--radius-lg);
  border: 1px solid var(--border);
  padding: 60px 24px;
  text-align: center;
  box-shadow: var(--shadow-card);
}
.empty-cart-icon {
  width: 90px;
  height: 90px;
  background: #fff7ed;
  color: var(--brand-accent);
  border-radius: 50%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  font-size: 38px;
  margin-bottom: 20px;
}
.empty-cart-card h3 { font-family: var(--font-heading); font-size: 22px; font-weight: 800; color: #0f172a; margin-bottom: 8px; }
.empty-cart-card p { font-size: 14px; color: var(--medium-gray); margin-bottom: 24px; }
.empty-cart-btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 28px;
  background: var(--brand-accent-gradient);
  color: #ffffff;
  border-radius: var(--radius-pill);
  font-weight: 700;
  text-decoration: none;
  box-shadow: 0 4px 14px rgba(249, 115, 22, 0.3);
  transition: var(--transition);
}
.empty-cart-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(249, 115, 22, 0.4); }

@media (max-width: 992px) {
  .cart-layout { grid-template-columns: 1fr; }
  .cart-summary-box { position: static; }
}
@media (max-width: 576px) {
  .cart-item-row { grid-template-columns: 70px 1fr; gap: 12px; }
  .cart-item-thumb { width: 70px; height: 70px; }
  .cart-item-line-total { grid-column: 1 / -1; text-align: left; padding-left: 82px; }
}
</style>
@endpush

@section('content')
<div class="cart-page-wrapper">
  <!-- Title row -->
  <div class="cart-header-row">
    <h1 class="cart-title">
      <i class="fas fa-shopping-bag" style="color: var(--brand-accent);"></i>
      Shopping Cart
      <span class="cart-badge" id="cart-count-display"></span>
    </h1>
  </div>

  <!-- Free Shipping Progress Goal -->
  <div class="shipping-meter-card" id="shipping-meter-card">
    <div class="meter-text" id="meter-message">
      <i class="fas fa-truck-fast"></i>
      <span>Add ₹<strong id="meter-remaining">{{ (int)\App\Models\StoreSetting::getValue('free_shipping_min_amount', 499) }}</strong> more to unlock <strong>FREE Express Shipping!</strong></span>
    </div>
    <div class="meter-bar">
      <div class="meter-fill" id="meter-progress"></div>
    </div>
  </div>

  <!-- Empty Cart State -->
  <div id="cart-empty" class="empty-cart-card" style="display:none;">
    <div class="empty-cart-icon"><i class="fas fa-cart-shopping"></i></div>
    <h3>Your cart feels lonely</h3>
    <p>Discover handpicked deals, electronics, and fashion items to fill it up!</p>
    <a href="{{ url('/') }}" class="empty-cart-btn">
      <i class="fas fa-arrow-left"></i> Start Shopping
    </a>
  </div>

  <!-- Cart with Items -->
  <div id="cart-with-items" class="cart-layout" style="display:none;">
    <!-- Items List -->
    <div class="cart-main-card">
      <div class="cart-table-top">
        <span>Cart Items</span>
        <button type="button" class="clear-cart-btn" onclick="clearCart()">
          <i class="fas fa-trash-alt"></i> Clear All
        </button>
      </div>

      <div id="cart-items-list"></div>

      <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 20px; margin-top: 16px; border-top: 1px solid var(--border);">
        <a href="{{ url('/') }}" style="color: var(--brand-accent); font-size: 13px; font-weight: 700; text-decoration: none; display: flex; align-items: center; gap: 6px;">
          <i class="fas fa-arrow-left"></i> Continue Shopping
        </a>
        <div style="font-size: 15px; color: #475569;">
          Subtotal (<span id="cart-items-count">0</span> items):
          <strong style="color: #0f172a; font-family: var(--font-heading); font-size: 20px; margin-left: 6px;">₹<span id="cart-subtotal">0</span></strong>
        </div>
      </div>
    </div>

    <!-- Summary Box -->
    <div class="cart-summary-box">
      <h3 class="summary-heading">Order Summary</h3>

      <!-- Promo code -->
      <div class="promo-box">
        <input type="text" id="promo-input" placeholder="Coupon or Promo code" autocomplete="off">
        <button type="button" onclick="applyPromo()">Apply</button>
      </div>

      {{-- Customer coupons list if available --}}
      @auth('customer')
        @if($coupons->count() > 0)
          <div class="cpn-list" style="margin-bottom: 16px;">
            <div style="font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
              <i class="fas fa-ticket-alt" style="color: var(--brand-accent);"></i> Available Coupons
            </div>
            @foreach($coupons as $cp)
              @php
                $isPct = $cp->type === 'percent';
                $label = $isPct ? rtrim(rtrim(number_format($cp->value, 2), '0'), '.') . '% OFF' : '₹' . number_format($cp->value, 0) . ' OFF';
                $minNote = $cp->min_amount > 0 ? 'Min ₹' . number_format($cp->min_amount, 0) : 'No min';
              @endphp
              <div class="cpn-row" data-code="{{ $cp->code }}" data-value="{{ $cp->value }}" data-type="{{ $cp->type }}" data-min="{{ (float) $cp->min_amount }}" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 10px; background: #f8fafc; border: 1px dashed var(--border); border-radius: 8px; margin-bottom: 6px;">
                <div>
                  <div style="font-weight: 700; font-size: 12px; color: #0f172a;">{{ $label }} ({{ $cp->code }})</div>
                  <div style="font-size: 11px; color: var(--medium-gray);">{{ $minNote }}</div>
                </div>
                <button type="button" class="cpn-apply" onclick="applyCouponFromList('{{ $cp->code }}', this)" style="padding: 4px 10px; border-radius: 100px; font-size: 11px; font-weight: 700; background: #0f172a; color: #fff; border: none; cursor: pointer;">Apply</button>
              </div>
            @endforeach
          </div>
        @endif
      @endauth

      <!-- Calculations -->
      <div class="summary-line">
        <span>Cart Subtotal</span>
        <span>₹<span id="summary-subtotal">0</span></span>
      </div>

      <div class="summary-line" id="summary-shipping-line">
        <span>Shipping Estimate</span>
        <span id="summary-shipping-cost" style="color: #10b981; font-weight: 700;">FREE</span>
      </div>

      <div class="summary-line summary-discount" id="summary-discount-row" style="display:none;">
        <span>Discount <span id="applied-coupon-label" style="font-size:12px; font-weight:400; color:#64748b;"></span> <a href="javascript:void(0)" id="remove-coupon" style="font-size:11px; color:#ef4444; margin-left:4px;" onclick="removeAppliedCoupon();">Remove</a></span>
        <span id="summary-discount">-₹0</span>
      </div>

      @if($bonuses->count() > 0)
        @php $bestBonus = $bonuses->sortByDesc('bonus_percent')->first(); @endphp
        <div style="background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 10px 12px; font-size: 12px; color: #1e40af; margin: 12px 0;">
          <i class="fas fa-gift" style="margin-right: 4px;"></i> Orders over ₹{{ number_format($bestBonus->min_amount, 0) }} unlock a <strong>{{ rtrim(rtrim($bestBonus->bonus_percent, '0'), '.') }}% reward coupon</strong> for your next purchase!
        </div>
      @endif

      <div class="summary-line grand-total">
        <span>Total Amount</span>
        <strong>₹<span id="summary-total">0</span></strong>
      </div>

      <button type="button" class="btn-proceed-checkout" onclick="proceedToCheckout()">
        <i class="fas fa-lock"></i> Proceed to Checkout
      </button>

      <a href="{{ url('/') }}" class="btn-continue-shop">
        Explore More Products
      </a>

      <div class="cart-security-badge">
        <i class="fas fa-shield-check"></i>
        <span>Bank-grade 256-bit SSL encrypted checkout</span>
      </div>
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
const FREE_SHIPPING_THRESHOLD = {{ (float)\App\Models\StoreSetting::getValue('free_shipping_min_amount', 499) }};
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
    updateShippingMeter(0);
    return;
  }

  empty.style.display = 'none';
  withItems.style.display = 'grid';

  list.innerHTML = cart.map(item => `
    <div class="cart-item-row" data-id="${item.id}">
      <a href="/product/${item.slug || item.id}" class="cart-item-thumb">
        ${item.image ? `<img src="${item.image}" alt="${item.name || ''}">` : '<i class="fas fa-image" style="color:#cbd5e1; font-size:24px;"></i>'}
      </a>
      <div class="cart-item-info">
        <a href="/product/${item.slug || item.id}" class="cart-item-title">${item.name || 'Product #' + item.id}</a>
        ${item.variation_name ? `<div class="cart-item-meta">Variation: <strong>${item.variation_name}</strong></div>` : ''}
        ${item.color ? `<div class="cart-item-meta">Color: <strong>${item.color}</strong></div>` : ''}
        <div class="cart-item-price-unit">₹${Number(item.price || 0).toLocaleString('en-IN')}</div>
        <div class="cart-item-actions-row">
          <div class="stepper">
            <button type="button" onclick="changeCartQty(${item.id}, ${item.qty - 1})" aria-label="Decrease quantity">−</button>
            <input type="number" value="${item.qty}" min="1" onchange="changeCartQty(${item.id}, parseInt(this.value))" aria-label="Quantity">
            <button type="button" onclick="changeCartQty(${item.id}, ${item.qty + 1})" aria-label="Increase quantity">+</button>
          </div>
          <button type="button" class="item-action-link" onclick="saveForLater(${item.id})">
            <i class="far fa-heart"></i> Save for later
          </button>
          <button type="button" class="item-action-link remove" onclick="removeCartItem(${item.id})">
            <i class="fas fa-trash-alt"></i> Remove
          </button>
        </div>
      </div>
      <div class="cart-item-line-total">₹${(Number(item.price || 0) * item.qty).toLocaleString('en-IN')}</div>
    </div>
  `).join('');

  updateCartTotals();
  document.getElementById('cart-count-display').textContent = `(${cart.length} ${cart.length === 1 ? 'item' : 'items'})`;
}

function updateShippingMeter(subtotal) {
  const card = document.getElementById('shipping-meter-card');
  const msg = document.getElementById('meter-message');
  const fill = document.getElementById('meter-progress');
  const remEl = document.getElementById('meter-remaining');
  const shipCost = document.getElementById('summary-shipping-cost');

  if (subtotal <= 0) {
    if (fill) fill.style.width = '0%';
    return;
  }

  const pct = Math.min(100, Math.round((subtotal / FREE_SHIPPING_THRESHOLD) * 100));
  if (fill) {
    fill.style.width = pct + '%';
    fill.classList.toggle('qualified', subtotal >= FREE_SHIPPING_THRESHOLD);
  }

  if (subtotal >= FREE_SHIPPING_THRESHOLD) {
    if (msg) msg.innerHTML = `<i class="fas fa-check-circle" style="color:#10b981;"></i> <span><strong>Congratulations!</strong> You qualified for <strong>FREE Express Shipping!</strong></span>`;
    if (shipCost) { shipCost.textContent = 'FREE'; shipCost.style.color = '#10b981'; }
  } else {
    const diff = FREE_SHIPPING_THRESHOLD - subtotal;
    if (msg) msg.innerHTML = `<i class="fas fa-truck-fast"></i> <span>Add ₹<strong>${diff.toLocaleString('en-IN')}</strong> more to unlock <strong>FREE Express Shipping!</strong></span>`;
    if (shipCost) { shipCost.textContent = '₹49'; shipCost.style.color = '#64748b'; }
  }
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
  if (confirm('Are you sure you want to empty your shopping cart?')) {
    localStorage.setItem(CART_KEY, '[]');
    renderCart();
    updateCartCount();
    showToast('Your cart has been cleared', 'info');
  }
}

function saveForLater(id) {
  const cart = getCart();
  const item = cart.find(c => c.id === id);
  if (item) {
    const later = JSON.parse(localStorage.getItem('store_saved') || '[]');
    later.push(item);
    localStorage.setItem('store_saved', JSON.stringify(later));
    removeFromCart(id);
    renderCart();
    showToast('Saved for later!', 'info');
  }
}

function applyPromo() {
  const code = (document.getElementById('promo-input').value || '').trim().toUpperCase();
  if (!code) return;
  const row = document.querySelector(`.cpn-row[data-code="${code}"]`);
  if (row) {
    applyCouponFromList(code, row.querySelector('.cpn-apply'));
    return;
  }
  const bonus = BONUSES.find(b => (b.name || '').toLowerCase() === code.toLowerCase());
  if (bonus) {
    appliedBonusPct = parseFloat(bonus.bonus_percent || 0);
    showToast(`Promo applied: ${appliedBonusPct}% bonus tier active!`, 'success');
  } else {
    showToast('Invalid promo or coupon code', 'error');
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
  document.querySelectorAll('.cpn-apply').forEach(b => { b.textContent = 'Apply'; b.disabled = false; });
  btn.textContent = 'Applied ✓';
  btn.disabled = true;
  updateCartTotals();
  showToast('Coupon ' + code + ' applied successfully!', 'success');
}

function removeAppliedCoupon() {
  appliedDiscount = 0;
  document.getElementById('promo-input').value = '';
  document.getElementById('summary-discount-row').style.display = 'none';
  document.getElementById('summary-discount').textContent = '-₹0';
  document.getElementById('applied-coupon-label').textContent = '';
  document.querySelectorAll('.cpn-apply').forEach(b => { b.textContent = 'Apply'; b.disabled = false; });
  updateCartTotals();
}

function updateCartTotals() {
  const cart = getCart();
  const subtotal = cart.reduce((sum, item) => sum + Number(item.price || 0) * item.qty, 0);
  const itemCount = cart.reduce((sum, item) => sum + item.qty, 0);
  const shipping = subtotal >= FREE_SHIPPING_THRESHOLD || subtotal === 0 ? 0 : 49;
  const currentTotal = Math.max(0, subtotal - appliedDiscount + shipping);

  document.getElementById('cart-subtotal').textContent = subtotal.toLocaleString('en-IN');
  document.getElementById('cart-items-count').textContent = itemCount;
  document.getElementById('summary-subtotal').textContent = subtotal.toLocaleString('en-IN');
  document.getElementById('summary-total').textContent = currentTotal.toLocaleString('en-IN', {maximumFractionDigits: 0});

  updateShippingMeter(subtotal);
}

function proceedToCheckout() {
  if (getCart().length === 0) {
    showToast('Your cart is empty', 'warning');
    return;
  }
  const cp = document.getElementById('promo-input').value;
  const u = '{{ url("/buy-now") }}' + (cp ? '?coupon_code=' + encodeURIComponent(cp) : '');
  window.location = u;
}

document.addEventListener('DOMContentLoaded', renderCart);
</script>
@endpush

@endsection

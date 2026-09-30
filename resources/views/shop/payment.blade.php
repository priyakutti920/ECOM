@extends('layouts.shop')

@section('title', 'Pay for your order — ' . $storeName)

@push('styles')
<style>
.pay-wrap { max-width: 680px; margin: 30px auto 80px; padding: 0 20px; }
.pay-card { background: #ffffff; border: 1px solid var(--color-border, #e2e8f0); border-radius: var(--radius-lg, 12px); padding: 32px 30px; box-shadow: 0 4px 16px rgba(0,0,0,0.04); }
.pay-head { text-align: center; margin-bottom: 24px; }
.pay-head h1 { margin: 0 0 6px; font-size: 24px; font-weight: 800; color: var(--color-heading, #0f172a); display: flex; align-items: center; justify-content: center; gap: 8px; }
.pay-head h1 i { color: var(--color-primary, #0068e1); }
.pay-head p { margin: 0; color: var(--color-muted, #64748b); font-size: 14px; }
.amount-block { background: linear-gradient(135deg, rgba(0, 104, 225, 0.05) 0%, rgba(0, 104, 225, 0.12) 100%); border: 1px solid rgba(0, 104, 225, 0.2); border-radius: var(--radius-lg, 12px); padding: 24px; text-align: center; margin-bottom: 22px; }
.amount-block .lbl { font-size: 12px; color: var(--color-primary, #0068e1); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
.amount-block .val { font-size: 38px; font-weight: 800; color: var(--color-heading, #0f172a); margin-top: 4px; }
.amount-block .note { font-size: 12px; color: var(--color-muted, #64748b); margin-top: 6px; }
.pay-summary { background: #f8fafc; border: 1px solid var(--color-border, #e2e8f0); border-radius: var(--radius-md, 8px); padding: 16px 20px; margin-bottom: 20px; }
.pay-summary h3 { margin: 0 0 10px; font-size: 12.5px; color: var(--color-heading, #0f172a); font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; }
.pay-summary .row { display: flex; justify-content: space-between; padding: 5px 0; font-size: 13.5px; color: var(--color-text, #334155); }
.pay-summary .row.tot { font-weight: 800; font-size: 16px; color: var(--color-heading, #0f172a); border-top: 1px solid var(--color-border, #e2e8f0); margin-top: 8px; padding-top: 8px; }
.ship-block { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: var(--radius-md, 8px); padding: 12px 16px; font-size: 13px; color: #15803d; line-height: 1.55; margin-bottom: 20px; }
.btn-pay { width: 100%; padding: 14px; background: var(--color-primary, #0068e1); border: none; border-radius: var(--radius-md, 8px); font-size: 15px; font-weight: 700; color: #ffffff; cursor: pointer; transition: all 0.15s ease; display: inline-flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 2px 8px rgba(0, 104, 225, 0.3); }
.btn-pay:hover { background: var(--color-primary-hover, #0051b3); transform: translateY(-1px); }
.btn-pay:disabled { opacity: 0.5; cursor: not-allowed; }
.btn-cancel { display: block; text-align: center; margin-top: 14px; font-size: 13px; color: var(--color-muted, #64748b); text-decoration: none; font-weight: 500; transition: color 0.15s; }
.btn-cancel:hover { color: var(--color-primary, #0068e1); text-decoration: underline; }
.status-pill { display: none; align-items: center; justify-content: center; gap: 8px; padding: 14px; border-radius: var(--radius-md, 8px); margin-top: 18px; font-size: 14px; font-weight: 600; }
.status-pill.show { display: flex; }
.status-pending { background: #fef9c3; color: #854d0e; border: 1px solid #fef08a; }
.status-success { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.status-failed { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
.spinner { display: inline-block; width: 16px; height: 16px; border: 2px solid currentColor; border-right-color: transparent; border-radius: 50%; animation: spin 0.6s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }
.gateway-warning { background: #fff1f2; border: 1px solid #fecdd3; color: #9f1239; padding: 14px 16px; border-radius: var(--radius-md, 8px); font-size: 13px; margin-bottom: 18px; }
.secure-row { display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 12px; color: var(--color-muted, #64748b); margin-top: 16px; }
.secure-row i { color: #16a34a; }
@media (max-width: 600px) {
    .pay-card { padding: 24px 18px; }
    .amount-block .val { font-size: 32px; }
}
</style>
@endpush

@section('content')
<div class="pay-wrap">
 <div class="pay-card">
 <div class="pay-head">
 <h1><i class="fas fa-lock" style="color: var(--amazon-orange);"></i> Complete your payment</h1>
 <p>Order reference: <strong>{{ $token }}</strong></p>
 </div>

 @if(session('info'))
 <div class="ship-block">
 <i class="fas fa-info-circle"></i> {{ session('info') }}
 </div>
 @endif

 @if(!$gatewayConfigured)
 <div class="gateway-warning">
 <i class="fas fa-exclamation-triangle"></i> UPI payment is not configured yet. Please contact the store.
 </div>
 @endif

 @php
 $isCoupon = isset($pending['discount_source']) && str_starts_with((string) $pending['discount_source'], 'coupon:');
 $isBonus = isset($pending['discount_source']) && str_starts_with((string) $pending['discount_source'], 'bonus:');
 $bonusAmount = $pending['future_bonus_amount'] ?? ($isBonus ? ($pending['discount'] ?? 0) : 0);
 $amountToPay = $pending['total'];
 @endphp

 <div class="amount-block">
 <div class="lbl">Amount to pay</div>
 <div class="val">₹{{ number_format($amountToPay, 2) }}</div>
 @if($isCoupon)
 <div class="note" style="color:#007600;">Coupon <strong>{{ $pending['coupon_code'] }}</strong> applied — you saved ₹{{ number_format($pending['discount'], 2) }} on this order.</div>
 @elseif($isBonus && $bonusAmount > 0)
 <div class="note" style="color:#007600;">You'll save <strong>₹{{ number_format($bonusAmount, 2) }}</strong> as a coupon for your <strong>next</strong> order. Pay full amount today!</div>
 @endif
 </div>

 <div class="pay-summary">
 <h3>Order summary</h3>
 @foreach($pending['items'] as $it)
 <div class="row">
 <span>{{ $it['name'] }} × {{ $it['qty'] }}</span>
 <span>₹{{ number_format($it['price'] * $it['qty'], 2) }}</span>
 </div>
 @endforeach
 <div class="row"><span>Subtotal</span><span>₹{{ number_format($pending['subtotal'], 2) }}</span></div>
 @if($isCoupon && $pending['discount'] > 0)
 <div class="row" style="color:#007600;"><span>Coupon {{ $pending['coupon_code'] }}</span><span>−₹{{ number_format($pending['discount'], 2) }}</span></div>
 @elseif($isBonus && $bonusAmount > 0)
 <div class="row" style="color:#007600;"><span>Next-order bonus (you'll save)</span><span>₹{{ number_format($bonusAmount, 2) }}</span></div>
 @endif
 <div class="row"><span>Shipping</span><span style="color:#007600;">FREE</span></div>
 <div class="row tot"><span>Total to pay</span><span>₹{{ number_format($amountToPay, 2) }}</span></div>
 </div>

 <div class="pay-summary">
 <h3>Shipping to</h3>
 <div style="font-size: 13px; color: #444; line-height: 1.55;">
 <strong>{{ $pending['address']['full_name'] }}</strong>
 ({{ ucfirst($pending['address']['type']) }})<br>
 {{ $pending['address']['line1'] }}@if(!empty($pending['address']['line2'])), {{ $pending['address']['line2'] }}@endif<br>
 {{ $pending['address']['city'] }}, {{ $pending['address']['state'] }} — {{ $pending['address']['pincode'] }}<br>
 <i class="fas fa-phone"></i> {{ $pending['address']['mobile_primary'] }}
 </div>
 </div>

 <button type="button" class="btn-pay" id="btn-pay" {{ $gatewayConfigured ? '' : 'disabled' }}>
 <span id="btn-pay-text"><i class="fas fa-credit-card"></i> Pay ₹{{ number_format($pending['total'], 2) }}</span>
 </button>

 <div id="status-pill" class="status-pill"></div>

 <a href="{{ url('/') }}" class="btn-cancel"><i class="fas fa-times"></i> Cancel and continue shopping</a>

 <div class="secure-row">
 <i class="fas fa-shield-alt"></i> Powered by UPI — secure payments via your bank app.
 </div>
 </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
 const token = @json($token);
 const csrf  = document.querySelector('meta[name=csrf-token]')?.content || '';

 const btn       = document.getElementById('btn-pay');
 const btnText   = document.getElementById('btn-pay-text');
 const pill      = document.getElementById('status-pill');
 let pollTimer   = null;
 let paymentTab  = null;
 let pollAttempts = 0;
 const MAX_POLL_ATTEMPTS = 60;

 function setPill(state, msg) {
 pill.classList.remove('status-pending', 'status-success', 'status-failed', 'show');
 if (!state) return;
 pill.classList.add('show', 'status-' + state);
 if (state === 'pending') {
 pill.innerHTML = '<span class="spinner"></span> ' + msg;
 } else {
 const icon = state === 'success' ? 'check-circle' : 'times-circle';
 pill.innerHTML = '<i class="fas fa-' + icon + '"></i> ' + msg;
 }
 }

 async function initiate() {
 btn.disabled = true;
 btnText.innerHTML = '<span class="spinner"></span> Preparing payment…';
 try {
 const res = await fetch('/pay/' + encodeURIComponent(token) + '/initiate', {
 method: 'POST',
 headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
 });
 const data = await res.json();

 if (!data.success) {
 setPill('failed', data.message || 'Could not start payment.');
 btn.disabled = false;
 btnText.innerHTML = '<i class="fas fa-credit-card"></i> Try again';
 return;
 }

 paymentTab = window.open(data.payment_url, '_blank');
 if (!paymentTab) {
 // Popup blocked — fallback to same-tab redirect
 window.location.href = data.payment_url;
 return;
 }

 btnText.innerHTML = '<i class="fas fa-external-link-alt"></i> Payment opened — waiting for confirmation';
 setPill('pending', 'Waiting for payment confirmation…');
 startPolling();
 } catch (e) {
 console.error(e);
 setPill('failed', 'Network error. Please try again.');
 btn.disabled = false;
 btnText.innerHTML = '<i class="fas fa-credit-card"></i> Try again';
 }
 }

 function startPolling() {
 if (pollTimer) clearInterval(pollTimer);
 pollAttempts = 0;
 pollTimer = setInterval(checkStatus, 3000);
 // First check after 2s to be snappy
 setTimeout(checkStatus, 2000);
 }

 async function checkStatus() {
 try {
 pollAttempts++;
 if (pollAttempts >= MAX_POLL_ATTEMPTS) {
 clearInterval(pollTimer);
 setPill('failed', 'Payment confirmation timed out. If money was debited, please check My Orders or contact support.');
 btn.disabled = false;
 btnText.innerHTML = '<i class="fas fa-redo"></i> Check Status Again';
 return;
 }
 const res = await fetch('/pay/' + encodeURIComponent(token) + '/status', {
 method: 'GET',
 headers: { 'Accept': 'application/json' },
 });
 if (res.status === 410) {
 clearInterval(pollTimer);
 setPill('failed', 'This payment session has expired. Please place your order again.');
 return;
 }
 const data = await res.json();

 if (data.state === 'paid' && data.redirect) {
 clearInterval(pollTimer);
 setPill('success', 'Payment received — redirecting…');
 // If this was a single Buy-Now, only remove that product; otherwise clear cart.
 try {
 const single = JSON.parse(localStorage.getItem('buyNowItem') || 'null');
 if (single && single.id) {
 const list = JSON.parse(localStorage.getItem('store_cart') || localStorage.getItem('nellai_cart') || '[]');
 const filtered = JSON.stringify(list.filter(it => Number(it.id) !== Number(single.id)));
 localStorage.setItem('store_cart', filtered);
 localStorage.setItem('nellai_cart', filtered);
 localStorage.removeItem('buyNowItem');
 } else {
 localStorage.removeItem('store_cart');
 localStorage.removeItem('nellai_cart');
 }
 } catch (e) {}
 setTimeout(() => { window.location.href = data.redirect; }, 800);
 } else if (data.state === 'failed') {
 clearInterval(pollTimer);
 setPill('failed', data.message || 'Payment failed.');
 btn.disabled = false;
 btnText.innerHTML = '<i class="fas fa-credit-card"></i> Retry payment';
 }
 // otherwise still pending — keep polling
 } catch (e) {
 console.error('status check failed', e);
 }
 }

 btn.addEventListener('click', initiate);

 // If user comes back to this tab (e.g. after closing the UPI tab), force a fresh check
 document.addEventListener('visibilitychange', () => {
 if (!document.hidden && pollTimer) checkStatus();
 });
})();
</script>
@endpush

@php
 // Determine where user will be redirected after successful login
 $redirectTo = session('otp_redirect');
 @endphp

 @extends('layouts.shop')

@section('title', 'Verify OTP - ' . ($storeName ?? \App\Models\StoreSetting::getStoreName()))

@section('content')
<div class="login-page-wrapper">
 <div class="login-card">
 {{-- Header --}}
 <div class="login-header">
 <a href="{{ url('/') }}" class="login-logo">
 <span class="logo-text">{{ $storeName ?? \App\Models\StoreSetting::getStoreName() }}</span>
 </a>
 <h1>Verify OTP</h1>
 <p>
 OTP sent to <strong>{{ $maskedEmail ?? $email ?? '' }}</strong>
 </p>
 <a href="{{ route('shop.login.email') }}" class="change-number-link">
 <i class="fas fa-edit"></i> Change email
 </a>
 </div>

 {{-- Status / Error messages --}}
 @if(session('status'))
 <div class="alert alert-info">
 <i class="fas fa-info-circle"></i> {{ session('status') }}
 </div>
 @endif
 @if($errors->has('otp'))
 <div class="alert alert-error">
 <i class="fas fa-exclamation-circle"></i> {{ $errors->first('otp') }}
 </div>
 @endif

 {{-- OTP Form --}}
 <form method="POST" action="{{ route('shop.login.verify') }}" id="otp-form" autocomplete="off">
 @csrf

 <div class="otp-inputs-wrapper">
 <input
 type="text"
 class="otp-digit"
 id="otp1"
 maxlength="1"
 inputmode="numeric"
 pattern="[0-9]"
 autocomplete="one-time-code"
 aria-label="Digit 1"
 />
 <input
 type="text"
 class="otp-digit"
 id="otp2"
 maxlength="1"
 inputmode="numeric"
 pattern="[0-9]"
 aria-label="Digit 2"
 />
 <input
 type="text"
 class="otp-digit"
 id="otp3"
 maxlength="1"
 inputmode="numeric"
 pattern="[0-9]"
 aria-label="Digit 3"
 />
 <input
 type="text"
 class="otp-digit"
 id="otp4"
 maxlength="1"
 inputmode="numeric"
 pattern="[0-9]"
 aria-label="Digit 4"
 />
 <input
 type="text"
 class="otp-digit"
 id="otp5"
 maxlength="1"
 inputmode="numeric"
 pattern="[0-9]"
 aria-label="Digit 5"
 />
 <input
 type="text"
 class="otp-digit"
 id="otp6"
 maxlength="1"
 inputmode="numeric"
 pattern="[0-9]"
 aria-label="Digit 6"
 />
 </div>

 {{-- Hidden combined OTP field --}}
 <input type="hidden" name="otp" id="otp-combined" value="{{ old('otp') }}">

 @error('otp')
 <span class="field-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
 @enderror

 <button type="submit" class="btn-primary" id="verify-btn">
 <span class="btn-text">Verify &amp; Continue</span>
 <span class="btn-loading" style="display:none;">
 <i class="fas fa-spinner fa-spin"></i> Verifying...
 </span>
 </button>
 </form>

 <div class="otp-resend-row">
 <span id="resend-timer" class="resend-timer">Resend OTP in <strong id="countdown">60</strong>s</span>
 <a href="{{ route('shop.login.resend') }}" method="POST" id="resend-link" class="resend-link disabled" onclick="return false;">
 <i class="fas fa-redo"></i> Resend OTP
 </a>
 </div>
 </div>
</div>
@endsection

@push('styles')
<style>
.login-page-wrapper { min-height: calc(100vh - 80px); display: flex; align-items: center; justify-content: center; padding: 40px 20px; background: var(--color-bg, #f8fafc); }
.login-card { background: #ffffff; border-radius: var(--radius-lg, 12px); border: 1px solid var(--color-border, #e2e8f0); padding: 36px 32px; width: 100%; max-width: 440px; box-shadow: 0 4px 16px rgba(0,0,0,0.04); }
.login-header { text-align: center; margin-bottom: 24px; }
.login-logo { display: inline-block; margin-bottom: 12px; }
.login-logo .logo-text { font-size: 24px; font-weight: 800; color: var(--color-heading, #0f172a); }
.login-header h1 { font-size: 22px; font-weight: 800; color: var(--color-heading, #0f172a); margin: 0 0 6px; }
.login-header p { font-size: 13.5px; color: var(--color-muted, #64748b); margin: 0; }
.alert { padding: 10px 14px; border-radius: var(--radius-md, 8px); margin-bottom: 16px; font-size: 13px; display: flex; align-items: flex-start; gap: 8px; }
.alert-success { background: #dcfce7; color: #15803d; border-left: 3px solid #22c55e; }
.alert-error { background: #fee2e2; color: #991b1b; border-left: 3px solid #ef4444; }
.alert-info { background: #e0f2fe; color: #0369a1; border-left: 3px solid #0284c7; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 12px; font-weight: 700; color: var(--color-heading, #0f172a); text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 6px; }
.input-wrapper { display: flex; align-items: center; border: 1px solid var(--color-border, #e2e8f0); border-radius: var(--radius-md, 8px); overflow: hidden; transition: border-color 0.15s, box-shadow 0.15s; background: #ffffff; }
.input-wrapper:focus-within { border-color: var(--color-primary, #0068e1); box-shadow: 0 0 0 3px rgba(0, 104, 225, 0.15); }
.input-icon { background: #f8fafc; padding: 10px 14px; font-size: 14px; color: var(--color-muted, #64748b); border-right: 1px solid var(--color-border, #e2e8f0); }
.input-wrapper input { flex: 1; border: none; outline: none; padding: 10px 14px; font-size: 14px; color: var(--color-heading, #0f172a); background: transparent; min-width: 0; }
.btn-primary { width: 100%; padding: 11px; background: var(--color-primary, #0068e1); border: none; border-radius: var(--radius-md, 8px); font-size: 14px; font-weight: 600; color: #ffffff; cursor: pointer; transition: all 0.15s ease; text-align: center; box-shadow: 0 2px 6px rgba(0, 104, 225, 0.25); display: inline-flex; align-items: center; justify-content: center; gap: 6px; }
.btn-primary:hover { background: var(--color-primary-hover, #0051b3); transform: translateY(-1px); }
.btn-secondary { display: block; width: 100%; padding: 10px; background: #ffffff; border: 1px solid var(--color-border, #e2e8f0); border-radius: var(--radius-md, 8px); font-size: 13px; font-weight: 600; color: var(--color-heading, #0f172a); text-align: center; cursor: pointer; text-decoration: none; margin-bottom: 16px; transition: all 0.15s ease; }
.btn-secondary:hover { background: #f8fafc; border-color: #cbd5e1; }
.login-divider { display: flex; align-items: center; gap: 12px; margin: 20px 0; font-size: 12px; color: var(--color-muted, #64748b); }
.login-divider::before, .login-divider::after { content: ''; flex: 1; height: 1px; background: var(--color-border, #e2e8f0); }
.login-footer-text { text-align: center; font-size: 11.5px; color: var(--color-muted, #64748b); display: flex; align-items: center; justify-content: center; gap: 6px; margin-top: 16px; line-height: 1.4; }
.login-footer-text i { color: #16a34a; }
</style>
@endpush

@push('scripts')
<script>
 // ── OTP digit auto-advance ─────────────────────────
 const otpInputs = [
 document.getElementById('otp1'),
 document.getElementById('otp2'),
 document.getElementById('otp3'),
 document.getElementById('otp4'),
 document.getElementById('otp5'),
 document.getElementById('otp6'),
 ];
 const combinedInput = document.getElementById('otp-combined');

 function updateCombined() {
 combinedInput.value = otpInputs.map(i => i.value).join('');
 // Auto-submit when 6 digits entered
 if (combinedInput.value.length === 6) {
 document.getElementById('otp-form').requestSubmit();
 }
 }

 otpInputs.forEach((input, index) => {
 // Only allow digits
 input.addEventListener('input', function(e) {
 this.value = this.value.replace(/[^0-9]/g, '').slice(-1);
 this.classList.toggle('filled', this.value.length === 1);
 if (this.value && index < otpInputs.length - 1) {
 otpInputs[index + 1].focus();
 }
 updateCombined();
 });

 // Handle paste of full OTP (e.g. from clipboard)
 input.addEventListener('paste', function(e) {
 e.preventDefault();
 const pasted = (e.clipboardData || window.clipboardData).getData('text');
 const digits = pasted.replace(/[^0-9]/g, '').slice(0, 6);
 digits.split('').forEach((char, i) => {
 if (otpInputs[i]) {
 otpInputs[i].value = char;
 otpInputs[i].classList.toggle('filled', !!char);
 }
 });
 updateCombined();
 });

 // Handle backspace — go to previous field
 input.addEventListener('keydown', function(e) {
 if (e.key === 'Backspace' && !this.value && index > 0) {
 otpInputs[index - 1].focus();
 }
 });

 // Handle arrow keys
 input.addEventListener('keyup', function(e) {
 if (e.key === 'ArrowLeft' && index > 0) otpInputs[index - 1].focus();
 if (e.key === 'ArrowRight' && index < otpInputs.length - 1) otpInputs[index + 1].focus();
 });
 });

 // Pre-fill if old input value exists (e.g. validation error)
 @if(old('otp'))
 const oldOtp = "{{ old('otp') }}";
 oldOtp.split('').forEach((char, i) => {
 if (otpInputs[i]) {
 otpInputs[i].value = char;
 otpInputs[i].classList.add('filled');
 }
 });
 updateCombined();
 @endif

 // Focus first empty input on load
 const firstEmpty = otpInputs.find(i => !i.value);
 if (firstEmpty) firstEmpty.focus();

 // ── Loading state on submit ───────────────────────
 document.getElementById('otp-form').addEventListener('submit', function() {
 const btn = document.getElementById('verify-btn');
 btn.disabled = true;
 btn.querySelector('.btn-text').style.display = 'none';
 btn.querySelector('.btn-loading').style.display = 'inline-flex';
 btn.querySelector('.btn-loading').style.alignItems = 'center';
 btn.querySelector('.btn-loading').style.gap = '6px';
 });

 // ── Countdown timer ──────────────────────────────
 let countdown = 60;
 const countdownEl = document.getElementById('countdown');
 const resendLink = document.getElementById('resend-link');
 const timerText = document.getElementById('resend-timer');

 const timer = setInterval(() => {
 countdown--;
 countdownEl.textContent = countdown;
 if (countdown <= 0) {
 clearInterval(timer);
 timerText.style.display = 'none';
 resendLink.classList.remove('disabled');
 resendLink.removeAttribute('onclick');
 resendLink.style.pointerEvents = 'auto';
 resendLink.style.color = 'var(--amazon-blue)';
 } else {
 timerText.style.display = 'inline';
 }
 }, 1000);
</script>
@endpush
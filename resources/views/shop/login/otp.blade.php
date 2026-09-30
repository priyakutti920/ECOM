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
 /* Login page wrapper — same as mobile page */
 .login-page-wrapper {
 min-height: calc(100vh - 60px);
 display: flex;
 align-items: flex-start;
 justify-content: center;
 padding: 40px 16px;
 background: #f3f3f3;
 }

 .login-card {
 background: #fff;
 border-radius: 8px;
 padding: 32px 28px;
 width: 100%;
 max-width: 420px;
 box-shadow: 0 2px 8px rgba(0,0,0,0.08);
 }

 .login-header {
 text-align: center;
 margin-bottom: 28px;
 }

 .login-logo {
 display: inline-block;
 margin-bottom: 16px;
 }

 .login-logo .logo-text {
 font-size: 24px;
 font-weight: 700;
 font-style: italic;
 color: var(--amazon-dark);
 }

 .login-logo .in {
 color: var(--amazon-orange);
 }

 .login-header h1 {
 font-size: 26px;
 font-weight: 600;
 color: #111;
 margin-bottom: 8px;
 }

 .login-header p {
 font-size: 14px;
 color: var(--medium-gray);
 margin-top: 8px;
 }

 .change-number-link {
 display: inline-flex;
 align-items: center;
 gap: 4px;
 font-size: 13px;
 color: var(--amazon-blue);
 margin-top: 6px;
 text-decoration: none;
 }
 .change-number-link:hover {
 text-decoration: underline;
 color: #c45500;
 }

 /* Alert boxes */
 .alert {
 padding: 10px 14px;
 border-radius: 6px;
 margin-bottom: 16px;
 font-size: 13px;
 display: flex;
 align-items: center;
 gap: 8px;
 }
 .alert-success { background: #d4edda; color: #155724; border-left: 3px solid #28a745; }
 .alert-error { background: #f8d7da; color: #721c24; border-left: 3px solid #dc3545; }
 .alert-info { background: #d1ecf1; color: #0c5460; border-left: 3px solid #17a2b8; }

 /* OTP digit inputs */
 .otp-inputs-wrapper {
 display: flex;
 justify-content: center;
 gap: 8px;
 margin-bottom: 20px;
 }

 .otp-digit {
 width: 48px;
 height: 52px;
 text-align: center;
 font-size: 22px;
 font-weight: 700;
 border: 1px solid #a6a6a6;
 border-radius: 4px;
 outline: none;
 color: #0f1111;
 background: #fff;
 transition: border-color 0.15s, box-shadow 0.15s;
 }

 .otp-digit:focus {
 border-color: var(--amazon-orange);
 box-shadow: 0 0 3px 2px rgba(228, 121, 17, 0.2);
 }

 .otp-digit.filled {
 border-color: #28a745;
 background: #f8fff8;
 }

 /* Field errors */
 .field-error {
 display: flex;
 align-items: center;
 gap: 4px;
 font-size: 12px;
 color: #c7511f;
 margin-bottom: 12px;
 justify-content: center;
 }

 /* Primary button */
 .btn-primary {
 width: 100%;
 padding: 10px;
 background: #ffd814;
 border: 1px solid #fcd200;
 border-radius: 100px;
 font-size: 14px;
 font-weight: 700;
 color: #0f1111;
 cursor: pointer;
 transition: background 0.15s;
 text-align: center;
 }

 .btn-primary:hover {
 background: #f7ca00;
 }

 .btn-primary:active {
 background: #f0b800;
 }

 .btn-primary:disabled {
 opacity: 0.6;
 cursor: not-allowed;
 }

 /* Resend row */
 .otp-resend-row {
 display: flex;
 align-items: center;
 justify-content: space-between;
 margin-top: 20px;
 padding-top: 16px;
 border-top: 1px solid #e7e7e7;
 }

 .resend-timer {
 font-size: 13px;
 color: #767676;
 }

 .resend-timer strong {
 color: var(--amazon-charcoal);
 font-weight: 700;
 }

 .resend-link {
 font-size: 13px;
 color: var(--amazon-blue);
 text-decoration: none;
 display: flex;
 align-items: center;
 gap: 4px;
 transition: color 0.15s;
 }

 .resend-link:hover:not(.disabled) {
 color: #c45500;
 text-decoration: underline;
 }

 .resend-link.disabled {
 color: #ccc;
 cursor: not-allowed;
 pointer-events: none;
 }

 /* Responsive */
 @media (max-width: 480px) {
 .login-page-wrapper {
 padding: 20px 12px;
 }
 .login-card {
 padding: 24px 20px;
 }
 .login-header h1 {
 font-size: 22px;
 }
 .otp-digit {
 width: 40px;
 height: 46px;
 font-size: 18px;
 }
 .otp-inputs-wrapper {
 gap: 6px;
 }
 }
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
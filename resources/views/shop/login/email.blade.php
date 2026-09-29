@extends('layouts.shop')

@section('title', 'Login / Sign Up — ' . ($storeName ?? \App\Models\StoreSetting::getStoreName()))

@section('content')
<div class="login-page-wrapper">
    <div class="login-card">
        {{-- Header --}}
        <div class="login-header">
            <a href="{{ url('/') }}" class="login-logo">
                <span class="logo-text">{{ $storeName ?? \App\Models\StoreSetting::getStoreName() }}</span>
            </a>
            <h1>Sign In</h1>
            <p>Welcome back! Choose your preferred sign-in method</p>
        </div>

        {{-- Status / Error messages --}}
        @if(session('status'))
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> {{ session('status') }}
            </div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> {{ session('info') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    @foreach($errors->all() as $err)
                        <div>{{ $err }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Tabs --}}
        <div class="login-tabs">
            <button type="button" id="tab-otp" class="tab-btn active" onclick="switchTab('otp')">
                <i class="fas fa-key"></i> OTP Login
            </button>
            <button type="button" id="tab-pass" class="tab-btn" onclick="switchTab('pass')">
                <i class="fas fa-lock"></i> Password Login
            </button>
        </div>

        {{-- Form 1: OTP --}}
        <form method="POST" action="{{ route('shop.login.send') }}" id="email-form" autocomplete="off">
            @csrf
            @if(isset($redirectTo) && $redirectTo)
                <input type="hidden" name="redirect" value="{{ $redirectTo }}">
            @endif

            <div class="form-group">
                <label for="email">Email Address</label>
                <div class="input-wrapper">
                    <span class="input-icon"><i class="fas fa-envelope"></i></span>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="you@example.com"
                        value="{{ old('email') }}"
                        maxlength="191"
                        inputmode="email"
                        autocomplete="email"
                        required
                        autofocus
                    />
                </div>
            </div>

            <button type="submit" class="btn-primary" id="send-btn">
                <span class="btn-text">Send OTP</span>
                <span class="btn-loading" style="display:none;">
                    <i class="fas fa-spinner fa-spin"></i> Sending OTP...
                </span>
            </button>
        </form>

        {{-- Form 2: Password Login --}}
        <form method="POST" action="{{ route('shop.login.password') }}" id="password-form" style="display:none;" autocomplete="off">
            @csrf
            @if(isset($redirectTo) && $redirectTo)
                <input type="hidden" name="redirect" value="{{ $redirectTo }}">
            @endif

            <div class="form-group">
                <label for="pass-login">Email or Mobile Number</label>
                <div class="input-wrapper">
                    <span class="input-icon"><i class="fas fa-user"></i></span>
                    <input type="text" id="pass-login" name="login" placeholder="Email or 10-digit mobile" value="{{ old('login') }}" required />
                </div>
            </div>

            <div class="form-group">
                <label for="pass-password">Password</label>
                <div class="input-wrapper">
                    <span class="input-icon"><i class="fas fa-lock"></i></span>
                    <input type="password" id="pass-password" name="password" placeholder="Enter password" required />
                </div>
            </div>

            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; font-size:13px;">
                <label style="display:flex; align-items:center; gap:6px; cursor:pointer; color:#333; margin:0;">
                    <input type="checkbox" name="remember" value="1" checked style="accent-color:var(--amazon-orange);"> Remember me
                </label>
                <a href="{{ route('shop.password.forgot') }}" style="color:#0066c0; text-decoration:none; font-size:13px;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                    Forgot password?
                </a>
            </div>

            <button type="submit" class="btn-primary" id="pass-btn">
                Sign In
            </button>
        </form>

        <div class="login-divider">
            <span>New to {{ $storeName ?? 'our store' }}?</span>
        </div>

        <a href="{{ route('shop.register', isset($redirectTo) && $redirectTo ? ['redirect' => urlencode($redirectTo)] : []) }}" class="btn-secondary">
            Create your customer account
        </a>

        <div class="login-footer-text">
            <i class="fas fa-shield-alt"></i>
            <span>Your information is secure. We don't share your details.</span>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.login-page-wrapper { min-height: calc(100vh - 60px); display: flex; align-items: flex-start; justify-content: center; padding: 40px 16px; background: #f3f3f3; }
.login-card { background: #fff; border-radius: 8px; padding: 32px 28px; width: 100%; max-width: 440px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.login-header { text-align: center; margin-bottom: 24px; }
.login-logo { display: inline-block; margin-bottom: 12px; }
.login-logo .logo-text { font-size: 24px; font-weight: 700; font-style: italic; color: var(--amazon-dark); }
.login-header h1 { font-size: 24px; font-weight: 700; color: #111; margin: 0 0 6px; }
.login-header p { font-size: 13px; color: var(--medium-gray); margin: 0; }
.login-tabs { display: flex; border-bottom: 2px solid #e7e7e7; margin-bottom: 20px; }
.tab-btn { flex: 1; padding: 10px; background: none; border: none; border-bottom: 2px solid transparent; font-weight: 600; font-size: 13.5px; cursor: pointer; color: #666; margin-bottom: -2px; transition: all 0.2s; }
.tab-btn.active { color: #111; font-weight: 700; border-bottom-color: var(--amazon-orange); }
.alert { padding: 10px 14px; border-radius: 6px; margin-bottom: 16px; font-size: 13px; display: flex; align-items: flex-start; gap: 8px; }
.alert-success { background: #d4edda; color: #155724; border-left: 3px solid #28a745; }
.alert-error { background: #f8d7da; color: #721c24; border-left: 3px solid #dc3545; }
.alert-info { background: #d1ecf1; color: #0c5460; border-left: 3px solid #17a2b8; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 13px; font-weight: 600; color: #111; margin-bottom: 5px; }
.input-wrapper { display: flex; align-items: center; border: 1px solid #a6a6a6; border-radius: 4px; overflow: hidden; transition: border-color 0.15s; background: #fff; }
.input-wrapper:focus-within { border-color: var(--amazon-orange); box-shadow: 0 0 3px 2px rgba(228, 121, 17, 0.2); }
.input-icon { background: #f0f2f2; padding: 9px 12px; font-size: 13px; color: #555; border-right: 1px solid #a6a6a6; }
.input-wrapper input { flex: 1; border: none; outline: none; padding: 9px 12px; font-size: 14px; color: #0f1111; background: transparent; min-width: 0; }
.btn-primary { width: 100%; padding: 11px; background: #ffd814; border: 1px solid #fcd200; border-radius: 100px; font-size: 14px; font-weight: 700; color: #0f1111; cursor: pointer; transition: background 0.15s; text-align: center; }
.btn-primary:hover { background: #f7ca00; }
.btn-secondary { display: block; width: 100%; padding: 9px; background: #f0f2f2; border: 1px solid #d5d9d9; border-radius: 100px; font-size: 13px; font-weight: 600; color: #0f1111; text-align: center; cursor: pointer; text-decoration: none; margin-bottom: 16px; }
.btn-secondary:hover { background: #e7e9ec; }
.login-divider { display: flex; align-items: center; gap: 12px; margin: 20px 0; font-size: 12px; color: #767676; }
.login-divider::before, .login-divider::after { content: ''; flex: 1; height: 1px; background: #e7e7e7; }
.login-footer-text { text-align: center; font-size: 11.5px; color: #767676; display: flex; align-items: center; justify-content: center; gap: 6px; margin-top: 14px; line-height: 1.4; }
</style>
@endpush

@push('scripts')
<script>
function switchTab(mode) {
    const tabOtp = document.getElementById('tab-otp');
    const tabPass = document.getElementById('tab-pass');
    const formOtp = document.getElementById('email-form');
    const formPass = document.getElementById('password-form');

    if (mode === 'pass') {
        tabPass.classList.add('active');
        tabOtp.classList.remove('active');
        formPass.style.display = 'block';
        formOtp.style.display = 'none';
        document.getElementById('pass-login').focus();
    } else {
        tabOtp.classList.add('active');
        tabPass.classList.remove('active');
        formOtp.style.display = 'block';
        formPass.style.display = 'none';
        document.getElementById('email').focus();
    }
}

document.getElementById('email-form').addEventListener('submit', function() {
    const btn = document.getElementById('send-btn');
    btn.disabled = true;
    btn.querySelector('.btn-text').style.display = 'none';
    btn.querySelector('.btn-loading').style.display = 'inline-flex';
});
</script>
@endpush

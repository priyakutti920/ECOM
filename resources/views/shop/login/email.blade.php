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
.login-page-wrapper { min-height: calc(100vh - 80px); display: flex; align-items: center; justify-content: center; padding: 40px 20px; background: var(--color-bg, #f8fafc); }
.login-card { background: #ffffff; border-radius: var(--radius-lg, 12px); border: 1px solid var(--color-border, #e2e8f0); padding: 36px 32px; width: 100%; max-width: 440px; box-shadow: 0 4px 16px rgba(0,0,0,0.04); }
.login-header { text-align: center; margin-bottom: 24px; }
.login-logo { display: inline-block; margin-bottom: 12px; }
.login-logo .logo-text { font-size: 24px; font-weight: 800; color: var(--color-heading, #0f172a); }
.login-header h1 { font-size: 22px; font-weight: 800; color: var(--color-heading, #0f172a); margin: 0 0 6px; }
.login-header p { font-size: 13.5px; color: var(--color-muted, #64748b); margin: 0; }
.login-tabs { display: flex; border-bottom: 2px solid var(--color-border, #e2e8f0); margin-bottom: 24px; }
.tab-btn { flex: 1; padding: 10px; background: none; border: none; border-bottom: 2px solid transparent; font-weight: 600; font-size: 13.5px; cursor: pointer; color: var(--color-muted, #64748b); margin-bottom: -2px; transition: all 0.2s; display: inline-flex; align-items: center; justify-content: center; gap: 6px; }
.tab-btn.active { color: var(--color-primary, #0068e1); font-weight: 700; border-bottom-color: var(--color-primary, #0068e1); }
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

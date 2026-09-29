@extends('layouts.shop')

@section('title', 'Create Account — ' . ($storeName ?? \App\Models\StoreSetting::getStoreName()))

@section('content')
<div class="login-page-wrapper">
    <div class="login-card">
        <div class="login-header">
            <a href="{{ url('/') }}" class="login-logo">
                <span class="logo-text">{{ $storeName ?? \App\Models\StoreSetting::getStoreName() }}</span>
            </a>
            <h1>Create Account</h1>
            <p>Enter your details to create your customer account</p>
        </div>

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

        <form method="POST" action="{{ route('shop.register.post') }}" id="register-form" autocomplete="off">
            @csrf
            @if(isset($redirectTo) && $redirectTo)
                <input type="hidden" name="redirect" value="{{ $redirectTo }}">
            @endif

            <div class="form-group">
                <label for="reg-name">Full Name <span style="color:#c7511f;">*</span></label>
                <div class="input-wrapper">
                    <span class="input-icon"><i class="fas fa-user"></i></span>
                    <input type="text" id="reg-name" name="name" placeholder="First and last name" value="{{ old('name') }}" maxlength="120" required autofocus />
                </div>
            </div>

            <div class="form-group">
                <label for="reg-email">Email Address <span style="color:#c7511f;">*</span></label>
                <div class="input-wrapper">
                    <span class="input-icon"><i class="fas fa-envelope"></i></span>
                    <input type="email" id="reg-email" name="email" placeholder="you@example.com" value="{{ old('email') }}" maxlength="191" required />
                </div>
            </div>

            <div class="form-group">
                <label for="reg-mobile">Mobile Number <span style="color:#666; font-weight:normal;">(optional)</span></label>
                <div class="input-wrapper">
                    <span class="input-icon"><i class="fas fa-phone"></i></span>
                    <input type="tel" id="reg-mobile" name="mobile" placeholder="10-digit mobile number" value="{{ old('mobile') }}" pattern="[0-9]{10}" maxlength="10" />
                </div>
            </div>

            <div class="form-group">
                <label for="reg-pass">Password <span style="color:#c7511f;">*</span></label>
                <div class="input-wrapper">
                    <span class="input-icon"><i class="fas fa-lock"></i></span>
                    <input type="password" id="reg-pass" name="password" placeholder="At least 6 characters" minlength="6" required />
                </div>
            </div>

            <div class="form-group">
                <label for="reg-pass-conf">Confirm Password <span style="color:#c7511f;">*</span></label>
                <div class="input-wrapper">
                    <span class="input-icon"><i class="fas fa-lock"></i></span>
                    <input type="password" id="reg-pass-conf" name="password_confirmation" placeholder="Re-enter password" minlength="6" required />
                </div>
            </div>

            <button type="submit" class="btn-primary" id="reg-btn" style="margin-top:8px;">
                <span class="btn-text">Create your account</span>
            </button>
        </form>

        <div class="login-divider">
            <span>Already have an account?</span>
        </div>

        <a href="{{ route('shop.login.email') }}" class="btn-secondary">
            Sign in with email / OTP
        </a>

        <div class="login-footer-text">
            <i class="fas fa-shield-alt"></i>
            <span>By creating an account, you agree to our terms of service and privacy policy.</span>
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
.alert { padding: 10px 14px; border-radius: 6px; margin-bottom: 16px; font-size: 13px; display: flex; align-items: flex-start; gap: 8px; }
.alert-error { background: #f8d7da; color: #721c24; border-left: 3px solid #dc3545; }
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

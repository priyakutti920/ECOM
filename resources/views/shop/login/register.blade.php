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

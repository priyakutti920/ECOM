@extends('layouts.shop')

@section('title', 'Forgot Password — ' . ($storeName ?? \App\Models\StoreSetting::getStoreName()))

@section('content')
<div class="login-page-wrapper">
    <div class="login-card">
        <div class="login-header">
            <a href="{{ url('/') }}" class="login-logo">
                <span class="logo-text">{{ $storeName ?? \App\Models\StoreSetting::getStoreName() }}</span>
            </a>
            <h1>Password Assistance</h1>
            <p>Enter the email address associated with your account</p>
        </div>

        @if(session('status'))
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> {{ session('status') }}
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

        <form method="POST" action="{{ route('shop.password.email') }}" id="forgot-form" autocomplete="off">
            @csrf

            <div class="form-group">
                <label for="email">Email Address <span style="color:#c7511f;">*</span></label>
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

            <button type="submit" class="btn-primary" id="reset-btn" style="margin-top:8px;">
                <span class="btn-text">Send Password Reset Link</span>
            </button>
        </form>

        <div class="login-divider">
            <span>Remembered your password?</span>
        </div>

        <a href="{{ route('shop.login.email') }}" class="btn-secondary">
            Back to Sign In
        </a>

        <div class="login-footer-text">
            <i class="fas fa-shield-alt"></i>
            <span>Your information is secure. We never share your details.</span>
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
.login-logo .logo-text { font-size: 24px; font-weight: 700; font-style: italic; color: var(--amazon-dark, #131921); }
.login-header h1 { font-size: 24px; font-weight: 700; color: #111; margin: 0 0 6px; }
.login-header p { font-size: 13px; color: #555; margin: 0; }
.alert { padding: 10px 14px; border-radius: 6px; margin-bottom: 16px; font-size: 13px; display: flex; align-items: flex-start; gap: 8px; }
.alert-success { background: #d4edda; color: #155724; border-left: 3px solid #28a745; }
.alert-error { background: #f8d7da; color: #721c24; border-left: 3px solid #dc3545; }
.alert-info { background: #d1ecf1; color: #0c5460; border-left: 3px solid #17a2b8; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 13px; font-weight: 600; color: #111; margin-bottom: 5px; }
.input-wrapper { display: flex; align-items: center; border: 1px solid #a6a6a6; border-radius: 4px; overflow: hidden; transition: border-color 0.15s; background: #fff; }
.input-wrapper:focus-within { border-color: #e47911; box-shadow: 0 0 3px 2px rgba(228, 121, 17, 0.2); }
.input-icon { background: #f0f2f2; padding: 9px 12px; font-size: 13px; color: #555; border-right: 1px solid #a6a6a6; }
.input-wrapper input { flex: 1; border: none; outline: none; padding: 9px 12px; font-size: 14px; color: #0f1111; background: transparent; min-width: 0; }
.btn-primary { display: block; width: 100%; padding: 10px; background: linear-gradient(to bottom, #f7dfa5, #f0c14b); border: 1px solid #a88734; border-radius: 4px; font-size: 14px; font-weight: 600; color: #111; cursor: pointer; text-align: center; box-shadow: 0 1px 0 rgba(255,255,255,0.4) inset; }
.btn-primary:hover { background: linear-gradient(to bottom, #f5d78e, #eeb933); }
.btn-secondary { display: block; width: 100%; padding: 9px; background: linear-gradient(to bottom, #f7f8fa, #e7e9ec); border: 1px solid #adb1b8 #a2a6ac #8d9096; border-radius: 4px; font-size: 13.5px; font-weight: 500; color: #111; text-align: center; text-decoration: none; box-shadow: 0 1px 0 rgba(255,255,255,0.6) inset; }
.btn-secondary:hover { background: linear-gradient(to bottom, #e7eaf0, #d9dce1); color: #111; }
.login-divider { position: relative; text-align: center; margin: 24px 0 16px; }
.login-divider::before { content: ""; position: absolute; top: 50%; left: 0; right: 0; border-top: 1px solid #e7e7e7; z-index: 1; }
.login-divider span { position: relative; z-index: 2; background: #fff; padding: 0 12px; font-size: 12px; color: #767676; }
.login-footer-text { margin-top: 24px; padding-top: 16px; border-top: 1px solid #f0f0f0; text-align: center; font-size: 12px; color: #767676; display: flex; align-items: center; justify-content: center; gap: 6px; }
</style>
@endpush

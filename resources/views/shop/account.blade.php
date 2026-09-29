@extends('layouts.shop')

@section('title', 'Laravel')

@section('content')
<div class="account-page-wrapper">
    <div class="account-laravel-bar">
        <span class="account-laravel-brand">Laravel</span>
    </div>

 <div class="account-container">
 {{-- Left Sidebar / Quick Info --}}
 <div class="account-sidebar">
 <div class="profile-card">
 <div class="profile-avatar">
 <i class="fas fa-user-circle"></i>
 </div>
 <div class="profile-info">
 <h3>{{ $customer->name ?: 'Customer Account' }}</h3>
 <p class="mobile-badge"><i class="fas fa-envelope"></i> {{ $customer->email }}</p>
 </div>
 </div>

 <nav class="account-nav">
        <a href="{{ url('/') }}" class="nav-item"><i class="fas fa-home"></i> Home</a>
        <a href="#profile" class="nav-item active"><i class="fas fa-user"></i> My Profile</a>
        <a href="#change-password" class="nav-item"><i class="fas fa-key"></i> Change Password</a>
        <a href="#addresses" class="nav-item"><i class="fas fa-map-marker-alt"></i> Addresses</a>
        <a href="{{ route('shop.orders.index') }}" class="nav-item"><i class="fas fa-box"></i> My Orders</a>
        <a href="{{ route('shop.wishlist') }}" class="nav-item"><i class="fas fa-heart"></i> My Wishlist</a>
        <a href="{{ url('/track-order') }}" class="nav-item"><i class="fas fa-truck"></i> Track Order</a>
        <a href="{{ route('shop.support.index') }}" class="nav-item"><i class="fas fa-ticket-alt"></i> Support Tickets</a>
 <a href="{{ route('shop.help') }}" class="nav-item"><i class="fas fa-question-circle"></i> Help &amp; FAQ</a>
 <a href="{{ route('shop.logout') }}"
 onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
 class="nav-item logout-item">
 <i class="fas fa-sign-out-alt"></i> Logout
 </a>
 </nav>
 </div>

 {{-- Main Content Area --}}
 <div class="account-content">

 {{-- Flash messages --}}
 @if(session('success'))
 <div class="acct-alert acct-alert-success">
 <i class="fas fa-check-circle"></i> {{ session('success') }}
 </div>
 @endif
 @if(session('status'))
 <div class="acct-alert acct-alert-info">
 <i class="fas fa-info-circle"></i> {{ session('status') }}
 </div>
 @endif

 {{-- Profile card --}}
 <div class="content-card" id="profile">
 <h2>Setup your account</h2>
 <p class="card-sub">Add your details so checkout is one tap.</p>
 <hr class="divider">

 <form method="POST" action="{{ route('shop.account.profile') }}" class="profile-form">
 @csrf

 <div class="form-grid">
 <div class="form-field">
 <label>Email Address</label>
 <input type="email" value="{{ $customer->email }}" readonly>
 <small class="hint">Used for login &mdash; cannot be changed.</small>
 </div>
 <div class="form-field">
 <label>Full Name <span class="req">*</span></label>
 <input type="text" name="name" maxlength="120" required value="{{ old('name', $customer->name) }}">
 @error('name') <span class="err">{{ $message }}</span> @enderror
 </div>
 <div class="form-field">
 <label>Mobile Number</label>
 <input type="tel" name="mobile" maxlength="10" pattern="[0-9]{10}"
 placeholder="10 digit mobile" value="{{ old('mobile', $customer->mobile) }}">
 @error('mobile') <span class="err">{{ $message }}</span> @enderror
 </div>
 </div>

    <button type="submit" class="btn-save">
        <i class="fas fa-save"></i> Save Profile
    </button>
    </form>
    </div>

    {{-- Change Password card --}}
    <div class="content-card" id="change-password" style="margin-top: 20px;">
        <h2><i class="fas fa-key" style="color:var(--amazon-orange); margin-right:8px;"></i> Change Password</h2>
        <p class="card-sub">Ensure your account is using a long, random password to stay secure.</p>
        <hr class="divider">

        @if($errors->has('current_password') || $errors->has('new_password'))
            <div class="acct-alert acct-alert-danger" style="background:#fee2e2; color:#991b1b; padding:10px 14px; border-radius:6px; margin-bottom:16px;">
                <i class="fas fa-exclamation-circle"></i>
                @foreach($errors->get('current_password') as $msg) <div>{{ $msg }}</div> @endforeach
                @foreach($errors->get('password') as $msg) <div>{{ $msg }}</div> @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('shop.account.password') }}" class="profile-form">
            @csrf
            <div class="form-grid">
                <div class="form-field">
                    <label>Current Password <span class="req">*</span></label>
                    <input type="password" name="current_password" required placeholder="Enter current password">
                </div>
                <div class="form-field">
                    <label>New Password <span class="req">*</span></label>
                    <input type="password" name="password" minlength="6" required placeholder="At least 6 characters">
                </div>
                <div class="form-field">
                    <label>Confirm New Password <span class="req">*</span></label>
                    <input type="password" name="password_confirmation" minlength="6" required placeholder="Re-enter new password">
                </div>
            </div>

            <button type="submit" class="btn-save">
                <i class="fas fa-lock"></i> Update Password
            </button>
        </form>
    </div>

 {{-- Address book --}}
 <div class="content-card" id="addresses" style="margin-top: 20px;">
 <div class="card-head-row">
 <h2>Your addresses</h2>
 <button type="button" class="btn-add" onclick="toggleAddAddress()">
 <i class="fas fa-plus"></i> Add new address
 </button>
 </div>
 <p class="card-sub">Saved addresses appear at checkout for one-click selection.</p>
 <hr class="divider">

 {{-- Add / edit form (hidden by default) --}}
 <div id="address-form-wrap" class="address-form-wrap" style="display: none;">
 @include('shop.partials.address-form', ['address' => null, 'showCancel' => true])
 </div>

 {{-- Existing addresses --}}
 @if($addresses->isEmpty())
 <div class="empty-addresses">
 <i class="fas fa-map-marker-alt"></i>
 <p>No addresses saved yet. Click <strong>Add new address</strong> to add one.</p>
 </div>
 @else
 <div class="address-list">
 @foreach($addresses as $addr)
 <div class="address-card" data-id="{{ $addr->id }}">
 @if($addr->is_default)
 <span class="default-badge"><i class="fas fa-check-circle"></i> Default</span>
 @endif
 <div class="addr-head">
 <strong>{{ $addr->full_name }}</strong>
 <span class="type-badge type-{{ $addr->type }}">
 <i class="fas fa-{{ $addr->type === 'work' ? 'briefcase' : 'home' }}"></i>
 {{ ucfirst($addr->type) }}
 </span>
 </div>
 <div class="addr-body">
 {{ $addr->address_line_1 }}@if($addr->address_line_2), {{ $addr->address_line_2 }}@endif<br>
 {{ $addr->city }}, {{ $addr->state }} &mdash; {{ $addr->pincode }}<br>
 <i class="fas fa-phone"></i> {{ $addr->mobile_primary }}@if($addr->mobile_alternate), {{ $addr->mobile_alternate }}@endif
 </div>
 <div class="addr-actions">
 @if(!$addr->is_default)
 <form method="POST" action="{{ route('shop.addresses.default', $addr) }}" style="display:inline;">
 @csrf
 <button type="submit" class="link-btn"><i class="fas fa-star"></i> Set as default</button>
 </form>
 @endif
 <form method="POST" action="{{ route('shop.addresses.destroy', $addr) }}" style="display:inline;"
 onsubmit="return confirm('Delete this address?');">
 @csrf
 @method('DELETE')
 <button type="submit" class="link-btn link-btn-danger"><i class="fas fa-trash"></i> Delete</button>
 </form>
 </div>
 </div>
 @endforeach
 </div>
 @endif
 </div>

 </div>
 </div>
</div>

<form id="logout-form" action="{{ route('shop.logout') }}" method="POST" style="display: none;">
 @csrf
</form>
@endsection

@push('styles')
<style>
 .account-laravel-bar {
     background: #ff2d20;
     color: #fff;
     padding: 10px 16px;
     text-align: center;
     border-radius: 6px 6px 0 0;
     margin: -30px -16px 18px;
     box-shadow: 0 2px 4px rgba(255,45,32,0.18);
 }
 .account-laravel-brand {
     font-weight: 800;
     font-size: 18px;
     letter-spacing: 0.5px;
     text-transform: uppercase;
     font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
 }

 .account-page-wrapper {
 background: #f3f3f3;
 min-height: calc(100vh - 60px);
 padding: 30px 16px;
 }

 .account-container {
 max-width: 1100px;
 margin: 0 auto;
 display: grid;
 grid-template-columns: 280px 1fr;
 gap: 24px;
 }

 /* Left Sidebar styling */
 .account-sidebar {
 display: flex;
 flex-direction: column;
 gap: 16px;
 }

 .profile-card {
 background: #fff;
 border: 1px solid #ddd;
 border-radius: 8px;
 padding: 24px;
 text-align: center;
 box-shadow: 0 2px 4px rgba(0,0,0,0.04);
 }

 .profile-avatar i {
 font-size: 72px;
 color: var(--amazon-charcoal);
 margin-bottom: 12px;
 display: inline-block;
 }

 .profile-info h3 {
 font-size: 18px;
 font-weight: 600;
 color: #111;
 margin-bottom: 6px;
 word-break: break-word;
 }

 .mobile-badge {
 display: inline-flex;
 align-items: center;
 gap: 6px;
 background: #f0f2f2;
 border: 1px solid #d5d9d9;
 padding: 4px 10px;
 border-radius: 100px;
 font-size: 12px;
 font-weight: 600;
 color: var(--medium-gray);
 word-break: break-all;
 }

 .account-nav {
 background: #fff;
 border: 1px solid #ddd;
 border-radius: 8px;
 overflow: hidden;
 box-shadow: 0 2px 4px rgba(0,0,0,0.04);
 }

 .nav-item {
 display: flex;
 align-items: center;
 gap: 12px;
 padding: 14px 20px;
 color: #333;
 font-size: 14px;
 font-weight: 500;
 transition: all 0.2s;
 border-bottom: 1px solid #eee;
 text-decoration: none;
 }
 .nav-item:last-child { border-bottom: none; }

 .nav-item i {
 width: 16px;
 text-align: center;
 color: var(--medium-gray);
 }

 .nav-item:hover {
 background: #f7f7f7;
 color: var(--amazon-orange);
 }

 .nav-item.active {
 background: #fcfcfc;
 color: var(--amazon-orange);
 font-weight: 700;
 border-left: 4px solid var(--amazon-orange);
 }

 .nav-item.logout-item { color: #c7511f; }
 .nav-item.logout-item i { color: #c7511f; }
 .nav-item.logout-item:hover { background: #fff5f2; }

 /* Main Content Area */
 .account-content {
 display: flex;
 flex-direction: column;
 gap: 0;
 }

 .content-card {
 background: #fff;
 border: 1px solid #ddd;
 border-radius: 8px;
 padding: 28px 32px;
 box-shadow: 0 2px 4px rgba(0,0,0,0.04);
 }

 .content-card h2 {
 font-size: 20px;
 font-weight: 600;
 color: #111;
 margin: 0 0 4px;
 }
 .card-sub {
 font-size: 13px;
 color: var(--medium-gray);
 margin: 0 0 14px;
 }
 .card-head-row {
 display: flex;
 align-items: center;
 justify-content: space-between;
 gap: 12px;
 flex-wrap: wrap;
 }

 .divider {
 border: 0;
 border-top: 1px solid #eee;
 margin: 0 0 20px;
 }

 /* Alerts */
 .acct-alert {
 padding: 10px 14px;
 border-radius: 6px;
 margin-bottom: 14px;
 font-size: 13px;
 display: flex;
 align-items: center;
 gap: 8px;
 }
 .acct-alert-success { background: #d4edda; color: #155724; border-left: 3px solid #28a745; }
 .acct-alert-info { background: #d1ecf1; color: #0c5460; border-left: 3px solid #17a2b8; }

 /* Form */
 .form-grid {
 display: grid;
 grid-template-columns: 1fr 1fr;
 gap: 14px 18px;
 margin-bottom: 18px;
 }

 .form-field { display: flex; flex-direction: column; }
 .form-field label {
 font-size: 12px;
 font-weight: 700;
 color: #444;
 margin-bottom: 6px;
 text-transform: uppercase;
 letter-spacing: 0.3px;
 }
 .form-field .req { color: #c7511f; }
 .form-field input,
 .form-field select {
 padding: 9px 12px;
 border: 1px solid #d5d9d9;
 border-radius: 4px;
 font-size: 14px;
 outline: none;
 background: #fff;
 transition: border 0.15s, box-shadow 0.15s;
 }
 .form-field input:focus,
 .form-field select:focus {
 border-color: #007185;
 box-shadow: 0 0 0 3px rgba(0,113,133,0.15);
 }
 .form-field input[readonly] {
 background: #f5f5f5;
 color: #555;
 cursor: not-allowed;
 }
 .form-field .err {
 color: #c7511f;
 font-size: 12px;
 margin-top: 4px;
 }
 .form-field .hint {
 color: var(--medium-gray);
 font-size: 11px;
 margin-top: 4px;
 }

 .btn-save,
 .btn-add {
 background: #ffd814;
 border: 1px solid #fcd200;
 border-radius: 100px;
 padding: 9px 18px;
 font-size: 13px;
 font-weight: 700;
 color: #0f1111;
 cursor: pointer;
 display: inline-flex;
 align-items: center;
 gap: 6px;
 transition: background 0.15s;
 }
 .btn-save:hover, .btn-add:hover { background: #f7ca00; }

 .btn-add {
 background: #fff;
 border-color: #007185;
 color: #007185;
 padding: 7px 14px;
 font-size: 12px;
 }
 .btn-add:hover { background: #f0f8fa; }

 /* Address list */
 .address-list {
 display: grid;
 grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
 gap: 14px;
 }

 .address-card {
 position: relative;
 border: 1px solid #d5d9d9;
 border-radius: 6px;
 padding: 16px;
 background: #fafafa;
 transition: border 0.15s;
 }
 .address-card:hover { border-color: #007185; }

 .default-badge {
 position: absolute;
 top: -8px;
 right: 10px;
 background: #007600;
 color: #fff;
 padding: 2px 8px;
 border-radius: 4px;
 font-size: 11px;
 font-weight: 700;
 }

 .addr-head {
 display: flex;
 align-items: center;
 justify-content: space-between;
 gap: 8px;
 margin-bottom: 8px;
 font-size: 14px;
 color: #111;
 }

 .type-badge {
 background: #fef7e3;
 color: #946a00;
 padding: 2px 8px;
 border-radius: 100px;
 font-size: 11px;
 font-weight: 600;
 display: inline-flex;
 align-items: center;
 gap: 4px;
 }
 .type-badge.type-work { background: #e7f2fb; color: #1a5a8d; }

 .addr-body {
 font-size: 13px;
 color: #444;
 line-height: 1.6;
 }
 .addr-body i { color: var(--medium-gray); margin-right: 4px; }

 .addr-actions {
 display: flex;
 gap: 16px;
 margin-top: 10px;
 padding-top: 10px;
 border-top: 1px solid #eee;
 }
 .link-btn {
 background: none;
 border: none;
 color: #007185;
 cursor: pointer;
 font-size: 12px;
 padding: 0;
 display: inline-flex;
 align-items: center;
 gap: 4px;
 }
 .link-btn:hover { color: #c45500; text-decoration: underline; }
 .link-btn-danger { color: #c7511f; }
 .link-btn-danger:hover { color: #a04416; }

 /* Empty state */
 .empty-addresses {
 text-align: center;
 padding: 30px 20px;
 color: var(--medium-gray);
 background: #fafafa;
 border: 1px dashed #d5d9d9;
 border-radius: 6px;
 }
 .empty-addresses i {
 font-size: 32px;
 color: #ccc;
 margin-bottom: 10px;
 }
 .empty-addresses p { margin: 0; font-size: 13px; }

 /* Address form wrap */
 .address-form-wrap {
 background: #fffbe7;
 border: 1px solid #f0c14b;
 border-radius: 6px;
 padding: 18px;
 margin-bottom: 18px;
 }

 /* Responsive */
 @media (max-width: 768px) {
 .account-container { grid-template-columns: 1fr; }
 .content-card { padding: 22px 18px; }
 .form-grid { grid-template-columns: 1fr; }
 }
</style>
@endpush

@push('scripts')
<script>
 function toggleAddAddress() {
 const wrap = document.getElementById('address-form-wrap');
 wrap.style.display = (wrap.style.display === 'none' || !wrap.style.display) ? 'block' : 'none';
 if (wrap.style.display === 'block') {
 wrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
 const firstInput = wrap.querySelector('input[type=text]');
 if (firstInput) firstInput.focus();
 }
 }

 // If validation failed on the new-address form, surface it open
 @if($errors->any() && (old('address_line_1') || old('full_name')))
 document.addEventListener('DOMContentLoaded', () => {
 document.getElementById('address-form-wrap').style.display = 'block';
 });
 @endif
</script>
@endpush

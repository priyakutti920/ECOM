@extends('layouts.shop')

@section('title', 'My Account — ' . $storeName)

@section('content')
<div class="account-page-wrapper">
    <nav class="account-breadcrumbs" aria-label="Breadcrumb">
        <a href="{{ url('/') }}" class="acct-bc-link"><i class="las la-home"></i> Home</a>
        <span class="acct-bc-sep"><i class="las la-angle-right"></i></span>
        <span class="acct-bc-current">My Account</span>
    </nav>

 <div class="account-container">
 {{-- Left Sidebar / Quick Info --}}
 <div class="account-sidebar">
 <div class="profile-card">
 <div class="profile-avatar-wrap" style="position:relative; width:80px; height:80px; margin:0 auto 14px;">
   <img src="{{ $customer->avatar_url }}" alt="{{ $customer->name }}" class="profile-avatar-img" id="sidebar-avatar-preview" style="width:80px; height:80px; border-radius:50%; object-fit:cover; border:3px solid var(--color-primary, #0068e1); box-shadow:0 4px 14px rgba(0,104,225,0.18);">
   <label for="account-avatar-file" style="position:absolute; bottom:0; right:0; width:26px; height:26px; background:var(--color-primary, #0068e1); color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; cursor:pointer; font-size:12px; border:2px solid #fff; box-shadow:0 2px 6px rgba(0,0,0,0.15);" title="Change Avatar">
     <i class="fas fa-camera"></i>
   </label>
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

 <form method="POST" action="{{ route('shop.account.profile') }}" enctype="multipart/form-data" class="profile-form">
 @csrf

 {{-- Avatar Upload Section --}}
 <div class="avatar-upload-section" style="display:flex; align-items:center; gap:20px; background:#f8fafc; border:1px solid var(--color-border, #e2e8f0); border-radius:var(--radius-md, 8px); padding:16px 20px; margin-bottom:24px;">
     <div style="position:relative; width:72px; height:72px; flex-shrink:0;">
         <img src="{{ $customer->avatar_url }}" alt="{{ $customer->name }}" id="form-avatar-preview" style="width:72px; height:72px; border-radius:50%; object-fit:cover; border:2.5px solid var(--color-primary, #0068e1); box-shadow:0 2px 8px rgba(0,0,0,0.08);">
     </div>
     <div style="flex:1;">
         <div style="font-size:14px; font-weight:700; color:var(--color-heading, #0f172a); margin-bottom:4px;">Profile Avatar</div>
         <div style="font-size:12.5px; color:var(--color-muted, #64748b); margin-bottom:10px;">Upload a personal avatar (PNG, JPG, WEBP, max 4MB).</div>
         <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
             <label for="account-avatar-file" class="btn-avatar-upload" style="display:inline-flex; align-items:center; gap:6px; background:#fff; border:1px solid var(--color-border, #cbd5e1); padding:6px 14px; border-radius:6px; font-size:12.5px; font-weight:600; color:var(--color-heading, #0f172a); cursor:pointer; transition:all 0.15s ease;">
                 <i class="fas fa-upload" style="color:var(--color-primary, #0068e1);"></i> Choose Photo
             </label>
             <input type="file" id="account-avatar-file" name="avatar" accept="image/png,image/jpeg,image/jpg,image/webp" style="display:none;" onchange="previewAvatar(this);">
             @if($customer->avatar)
                 <button type="button" onclick="removeAvatar();" class="btn-avatar-remove" style="display:inline-flex; align-items:center; gap:6px; background:#fff; border:1px solid #fca5a5; padding:6px 12px; border-radius:6px; font-size:12.5px; font-weight:600; color:#dc2626; cursor:pointer;">
                     <i class="fas fa-trash"></i> Remove Photo
                 </button>
                 <input type="hidden" name="remove_avatar" id="remove-avatar-input" value="0">
             @endif
             <span id="avatar-filename" style="font-size:12px; color:var(--color-primary, #0068e1); font-weight:500;"></span>
         </div>
         @error('avatar') <span class="err" style="display:block; margin-top:6px; font-size:12px; color:#ef4444;">{{ $message }}</span> @enderror
     </div>
 </div>

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
/* ── Account Page Styles ── */
.account-breadcrumbs {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--color-muted, #64748b);
    margin-bottom: 20px;
}
.acct-bc-link {
    color: var(--color-muted, #64748b);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: color 0.15s ease;
}
.acct-bc-link:hover { color: var(--color-primary, #0068e1); }
.acct-bc-sep { font-size: 11px; color: #cbd5e1; }
.acct-bc-current { color: var(--color-heading, #0f172a); font-weight: 600; }

.account-page-wrapper {
    background: var(--color-bg, #f8fafc);
    min-height: calc(100vh - 60px);
    padding: 24px 20px 60px;
    max-width: 1440px;
    margin: 0 auto;
}

.account-container {
    max-width: 1200px;
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
    background: #ffffff;
    border: 1px solid var(--color-border, #e2e8f0);
    border-radius: var(--radius-lg, 12px);
    padding: 24px;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}

.profile-avatar i {
    font-size: 72px;
    color: var(--color-primary, #0068e1);
    margin-bottom: 12px;
    display: inline-block;
}

.profile-info h3 {
    font-size: 18px;
    font-weight: 700;
    color: var(--color-heading, #0f172a);
    margin-bottom: 6px;
    word-break: break-word;
}

.mobile-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #f1f5f9;
    border: 1px solid var(--color-border, #e2e8f0);
    padding: 4px 10px;
    border-radius: 100px;
    font-size: 12px;
    font-weight: 600;
    color: var(--color-muted, #64748b);
    word-break: break-all;
}

.account-nav {
    background: #ffffff;
    border: 1px solid var(--color-border, #e2e8f0);
    border-radius: var(--radius-lg, 12px);
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 20px;
    color: var(--color-text, #334155);
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
    border-bottom: 1px solid var(--color-border, #e2e8f0);
    text-decoration: none;
}
.nav-item:last-child { border-bottom: none; }

.nav-item i {
    width: 16px;
    text-align: center;
    color: var(--color-muted, #64748b);
    transition: color 0.2s ease;
}

.nav-item:hover {
    background: #f8fafc;
    color: var(--color-primary, #0068e1);
}
.nav-item:hover i {
    color: var(--color-primary, #0068e1);
}

.nav-item.active {
    background: rgba(0, 104, 225, 0.06);
    color: var(--color-primary, #0068e1);
    font-weight: 700;
    border-left: 4px solid var(--color-primary, #0068e1);
}
.nav-item.active i {
    color: var(--color-primary, #0068e1);
}

.nav-item.logout-item { color: #ef4444; }
.nav-item.logout-item i { color: #ef4444; }
.nav-item.logout-item:hover { background: #fee2e2; }

/* Main Content Area */
.account-content {
    display: flex;
    flex-direction: column;
    gap: 0;
}

.content-card {
    background: #ffffff;
    border: 1px solid var(--color-border, #e2e8f0);
    border-radius: var(--radius-lg, 12px);
    padding: 28px 32px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}

.content-card h2 {
    font-size: 20px;
    font-weight: 700;
    color: var(--color-heading, #0f172a);
    margin: 0 0 4px;
}
.card-sub {
    font-size: 13.5px;
    color: var(--color-muted, #64748b);
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
    border-top: 1px solid var(--color-border, #e2e8f0);
    margin: 0 0 20px;
}

/* Alerts */
.acct-alert {
    padding: 10px 14px;
    border-radius: var(--radius-md, 8px);
    margin-bottom: 14px;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.acct-alert-success { background: #dcfce7; color: #15803d; border-left: 3px solid #22c55e; }
.acct-alert-info { background: #e0f2fe; color: #0369a1; border-left: 3px solid #0284c7; }

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
    color: var(--color-heading, #0f172a);
    margin-bottom: 6px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}
.form-field .req { color: #ef4444; }
.form-field input,
.form-field select {
    padding: 10px 14px;
    border: 1px solid var(--color-border, #e2e8f0);
    border-radius: var(--radius-md, 8px);
    font-size: 14px;
    outline: none;
    background: #ffffff;
    transition: border 0.15s, box-shadow 0.15s;
}
.form-field input:focus,
.form-field select:focus {
    border-color: var(--color-primary, #0068e1);
    box-shadow: 0 0 0 3px rgba(0, 104, 225, 0.15);
}
.form-field input[readonly] {
    background: #f8fafc;
    color: #64748b;
    cursor: not-allowed;
}
.form-field .err {
    color: #ef4444;
    font-size: 12px;
    margin-top: 4px;
}
.form-field .hint {
    color: var(--color-muted, #64748b);
    font-size: 11px;
    margin-top: 4px;
}

.btn-save,
.btn-add {
    background: var(--color-primary, #0068e1);
    border: none;
    border-radius: var(--radius-md, 8px);
    padding: 10px 22px;
    font-size: 13.5px;
    font-weight: 600;
    color: #ffffff;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 2px 6px rgba(0, 104, 225, 0.25);
    transition: background 0.15s, transform 0.15s, box-shadow 0.15s;
}
.btn-save:hover {
    background: var(--color-primary-hover, #0051b3);
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(0, 104, 225, 0.35);
}

.btn-add {
    background: rgba(0, 104, 225, 0.08);
    border: 1px solid rgba(0, 104, 225, 0.2);
    color: var(--color-primary, #0068e1);
    box-shadow: none;
    padding: 7px 14px;
    font-size: 12px;
}
.btn-add:hover {
    background: var(--color-primary, #0068e1);
    color: #ffffff;
    box-shadow: 0 2px 6px rgba(0, 104, 225, 0.25);
}

/* Address list */
.address-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
    gap: 14px;
}

.address-card {
    position: relative;
    border: 1px solid var(--color-border, #e2e8f0);
    border-radius: var(--radius-md, 8px);
    padding: 16px;
    background: #ffffff;
    transition: border 0.15s ease, box-shadow 0.15s ease;
}
.address-card:hover {
    border-color: var(--color-primary, #0068e1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.04);
}

.default-badge {
    position: absolute;
    top: -8px;
    right: 10px;
    background: #16a34a;
    color: #ffffff;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    padding: 2px 8px;
    border-radius: 100px;
    letter-spacing: 0.5px;
}

.addr-head {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
}
.addr-name {
    font-weight: 700;
    color: var(--color-heading, #0f172a);
    font-size: 14px;
}
.type-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 100px;
    background: #f1f5f9;
    color: #475569;
}
.type-badge.type-work { background: #e0f2fe; color: #0369a1; }

.addr-body {
    font-size: 13px;
    color: var(--color-text, #334155);
    line-height: 1.6;
}
.addr-body i { color: var(--color-muted, #64748b); margin-right: 4px; }

.addr-actions {
    display: flex;
    gap: 16px;
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid var(--color-border, #e2e8f0);
}
.link-btn {
    background: none;
    border: none;
    color: var(--color-primary, #0068e1);
    cursor: pointer;
    font-size: 12.5px;
    font-weight: 600;
    padding: 0;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: color 0.15s ease;
}
.link-btn:hover { color: var(--color-primary-hover, #0051b3); text-decoration: underline; }
.link-btn-danger { color: #ef4444; }
.link-btn-danger:hover { color: #dc2626; }

/* Empty state */
.empty-addresses {
    text-align: center;
    padding: 36px 20px;
    color: var(--color-muted, #64748b);
    background: #f8fafc;
    border: 1px dashed var(--color-border, #cbd5e1);
    border-radius: var(--radius-md, 8px);
}
.empty-addresses i {
    font-size: 32px;
    color: #94a3b8;
    margin-bottom: 10px;
    display: block;
}
.empty-addresses p { margin: 0; font-size: 13.5px; }

/* Address form wrap */
.address-form-wrap {
    background: #f8fafc;
    border: 1px solid var(--color-border, #e2e8f0);
    border-radius: var(--radius-md, 8px);
    padding: 20px;
    margin-bottom: 20px;
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
 function previewAvatar(input) {
     if (input.files && input.files[0]) {
         const file = input.files[0];
         if (file.size > 4 * 1024 * 1024) {
             alert('File size exceeds 4MB. Please choose a smaller image.');
             input.value = '';
             return;
         }
         const reader = new FileReader();
         reader.onload = function(e) {
             const sidebarImg = document.getElementById('sidebar-avatar-preview');
             const formImg = document.getElementById('form-avatar-preview');
             if (sidebarImg) sidebarImg.src = e.target.result;
             if (formImg) formImg.src = e.target.result;
             const removeInput = document.getElementById('remove-avatar-input');
             if (removeInput) removeInput.value = '0';
         };
         reader.readAsDataURL(file);
         const nameSpan = document.getElementById('avatar-filename');
         if (nameSpan) nameSpan.textContent = file.name;
     }
 }

 function removeAvatar() {
     if (confirm('Remove your custom profile avatar?')) {
         const removeInput = document.getElementById('remove-avatar-input');
         if (removeInput) removeInput.value = '1';
         const fileInput = document.getElementById('account-avatar-file');
         if (fileInput) fileInput.value = '';
         const defaultUrl = 'https://ui-avatars.com/api/?name={{ urlencode($customer->name ?: "User") }}&background=0068e1&color=ffffff&size=128&bold=true';
         const sidebarImg = document.getElementById('sidebar-avatar-preview');
         const formImg = document.getElementById('form-avatar-preview');
         if (sidebarImg) sidebarImg.src = defaultUrl;
         if (formImg) formImg.src = defaultUrl;
         const nameSpan = document.getElementById('avatar-filename');
         if (nameSpan) nameSpan.textContent = 'Avatar will be reset on Save';
     }
 }

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

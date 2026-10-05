@extends('install.layout', ['step' => 5])

@section('title', 'Create Administrator Account')
@section('page_heading', 'Super Administrator Setup')
@section('page_subheading', 'Establish the master administrative account used to manage your store, inventory, and orders.')

@section('content')
<div class="card-header">
    <div>
        <h2 class="card-title">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
            </svg>
            <span>Primary Store Administrator</span>
        </h2>
        <p class="card-subtitle">This account will have unrestricted Super Admin privileges.</p>
    </div>
</div>

<form action="{{ route('install.admin.save') }}" method="POST" id="adminForm">
    @csrf

    <div class="form-grid" style="margin-bottom: 2rem;">
        <div class="form-group col-span-2">
            <label class="form-label" for="store_name">
                <span>Store Name</span>
            </label>
            <input type="text" name="store_name" id="store_name" class="form-input" 
                   value="{{ old('store_name', $storeName) }}" required placeholder="e.g. Nammah Oooru Store">
        </div>

        <div class="form-group">
            <label class="form-label" for="name">
                <span>Administrator Full Name</span>
            </label>
            <input type="text" name="name" id="name" class="form-input" 
                   value="{{ old('name', 'Admin') }}" required placeholder="e.g. John Doe">
        </div>

        <div class="form-group">
            <label class="form-label" for="email">
                <span>Admin Email Address</span>
            </label>
            <input type="email" name="email" id="email" class="form-input" 
                   value="{{ old('email', 'admin@example.com') }}" required placeholder="admin@domain.com">
            <span class="form-hint">Used for sign in and critical system alerts.</span>
        </div>

        <div class="form-group col-span-2">
            <label class="form-label" for="store_phone">
                <span>Store Contact / WhatsApp Number (Optional)</span>
            </label>
            <input type="text" name="store_phone" id="store_phone" class="form-input" 
                   value="{{ old('store_phone') }}" placeholder="+91 9876543210">
        </div>

        <div class="form-group">
            <label class="form-label" for="password">
                <span>Admin Password</span>
            </label>
            <input type="password" name="password" id="password" class="form-input" 
                   required placeholder="••••••••••••" onkeyup="checkPasswordStrength()">
            
            <!-- Password strength indicator -->
            <div style="margin-top: 0.35rem;">
                <div style="height: 4px; width: 100%; background: rgba(255, 255, 255, 0.1); border-radius: 2px; overflow: hidden;">
                    <div id="strengthBar" style="height: 100%; width: 0%; transition: all 0.3s ease;"></div>
                </div>
                <div id="strengthText" style="font-size: 0.72rem; color: var(--text-muted); margin-top: 0.2rem;">Minimum 8 characters</div>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="password_confirmation">
                <span>Confirm Password</span>
            </label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-input" 
                   required placeholder="••••••••••••" onkeyup="checkPasswordMatch()">
            <span id="matchText" class="form-hint">Must match password</span>
        </div>
    </div>

    <div class="btn-group">
        <a href="{{ route('install.migrations') }}" class="btn btn-secondary">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="19" y1="12" x2="5" y2="12"></line>
                <polyline points="12 19 5 12 12 5"></polyline>
            </svg>
            <span>Back</span>
        </a>

        <button type="submit" id="btnSubmitAdmin" class="btn btn-primary" style="padding: 0.85rem 2rem;">
            <span>Finalize Installation</span>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"></polyline>
            </svg>
        </button>
    </div>
</form>
@endsection

@section('scripts')
<script>
function checkPasswordStrength() {
    const pwd = document.getElementById('password').value;
    const bar = document.getElementById('strengthBar');
    const text = document.getElementById('strengthText');

    let strength = 0;
    if (pwd.length >= 8) strength += 25;
    if (/[A-Z]/.test(pwd)) strength += 25;
    if (/[0-9]/.test(pwd)) strength += 25;
    if (/[^A-Za-z0-9]/.test(pwd)) strength += 25;

    bar.style.width = strength + '%';

    if (strength <= 25) {
        bar.style.background = '#ef4444';
        text.innerText = 'Weak (add numbers & symbols)';
        text.style.color = '#fca5a5';
    } else if (strength <= 50) {
        bar.style.background = '#f59e0b';
        text.innerText = 'Moderate password';
        text.style.color = '#fcd34d';
    } else if (strength <= 75) {
        bar.style.background = '#3b82f6';
        text.innerText = 'Strong password';
        text.style.color = '#93c5fd';
    } else {
        bar.style.background = '#10b981';
        text.innerText = 'Very secure password';
        text.style.color = '#6ee7b7';
    }
}

function checkPasswordMatch() {
    const pwd = document.getElementById('password').value;
    const confirm = document.getElementById('password_confirmation').value;
    const matchText = document.getElementById('matchText');

    if (!confirm) {
        matchText.innerText = 'Must match password';
        matchText.style.color = 'var(--text-muted)';
        return;
    }

    if (pwd === confirm) {
        matchText.innerText = '✓ Passwords match';
        matchText.style.color = '#34d399';
    } else {
        matchText.innerText = '✗ Passwords do not match';
        matchText.style.color = '#f87171';
    }
}
</script>
@endsection

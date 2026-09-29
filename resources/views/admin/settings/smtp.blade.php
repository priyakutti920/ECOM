@extends('admin.settings.index')

@section('title', 'SMTP Settings')

@push('styles')
<style>
    #toast-wrap { position:fixed; top:18px; right:18px; z-index:99999; min-width:260px; }
    .toast-msg {
        display:flex; align-items:center; gap:8px;
        padding:10px 14px; border-radius:5px; margin-bottom:7px;
        font-size:13px; font-weight:500; box-shadow:0 2px 10px rgba(0,0,0,0.10);
    }
    .toast-msg.success { background:#f0fdf4; border-left:4px solid #27ae60; color:#1a7a42; }
    .toast-msg.error   { background:#fff0f0; border-left:4px solid #c0392b; color:#a93226; }
    .toast-msg.info    { background:#eff6ff; border-left:4px solid #2563eb; color:#1d4ed8; }

    .smtp-card { border: 1px solid #e0e0e0; border-radius: 6px; padding: 20px 24px; margin-bottom: 16px; }
    .smtp-card-title {
        font-size: 15px; font-weight: 700; color: #222;
        margin: 0 0 16px; padding-bottom: 12px; border-bottom: 1px solid #eee;
        display: flex; align-items: center; gap: 8px;
    }
    .smtp-card-title i { color: #7b1fa2; }

    .smtp-section-label {
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .06em; color: #888; margin: 0 0 12px;
    }

    .smtp-form-row { display: flex; gap: 16px; flex-wrap: wrap; }
    .smtp-form-group { flex: 1; min-width: 200px; margin-bottom: 14px; }
    .smtp-form-group.full { min-width: 100%; }
    .smtp-form-label { display: block; font-size: 12px; font-weight: 600; color: #444; margin-bottom: 5px; }
    .smtp-form-input {
        width: 100%; padding: 8px 12px; border: 1px solid #ccc;
        border-radius: 5px; font-size: 13px; box-sizing: border-box;
    }
    .smtp-form-input:focus { border-color: #7b1fa2; outline: none; box-shadow: 0 0 0 3px rgba(123,31,162,0.08); }
    .smtp-form-select {
        width: 100%; padding: 8px 12px; border: 1px solid #ccc;
        border-radius: 5px; font-size: 13px; box-sizing: border-box; background: #fff;
    }
    .smtp-form-select:focus { border-color: #7b1fa2; outline: none; box-shadow: 0 0 0 3px rgba(123,31,162,0.08); }

    .smtp-form-actions { display: flex; gap: 10px; margin-top: 8px; }

    .smtp-save-btn {
        padding: 8px 20px; background: #2e7d32; color: #fff;
        border: none; border-radius: 5px; font-size: 13px; font-weight: 600;
        cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
    }
    .smtp-save-btn:hover { background: #1b5e20; }
    .smtp-save-btn:disabled { opacity: 0.6; cursor: not-allowed; }

    .smtp-test-btn {
        padding: 8px 20px; background: #1565c0; color: #fff;
        border: none; border-radius: 5px; font-size: 13px; font-weight: 600;
        cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
    }
    .smtp-test-btn:hover { background: #0d47a1; }
    .smtp-test-btn:disabled { opacity: 0.6; cursor: not-allowed; }

    /* Test email panel */
    .smtp-test-panel {
        background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 6px;
        padding: 20px; margin-top: 16px;
    }
    .smtp-test-panel-title {
        font-size: 13px; font-weight: 700; color: #444; margin-bottom: 14px;
        display: flex; align-items: center; gap: 6px;
    }

    .smtp-connection-status {
        display: flex; align-items: center; gap: 8px;
        padding: 10px 14px; border-radius: 5px; font-size: 13px; font-weight: 500;
        margin-bottom: 16px;
    }
    .smtp-connection-status.connected { background: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
    .smtp-connection-status.disconnected { background: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
    .smtp-connection-status.testing { background: #e3f2fd; color: #1565c0; border: 1px solid #bbdefb; }

    .smtp-template-btn {
        padding: 8px 20px; background: #6c757d; color: #fff;
        border: none; border-radius: 5px; font-size: 13px; font-weight: 600;
        cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
    }
    .smtp-template-btn:hover { background: #5a6268; }
    .smtp-template-btn:disabled { opacity: 0.5; cursor: not-allowed; }

    @media (max-width: 768px) {
        .smtp-form-group { min-width: 100%; }
        .smtp-form-row { flex-direction: column; }
    }
</style>
@endpush

@section('settings_content')
<div id="toast-wrap"></div>

{{-- SMTP Configuration --}}
<div class="smtp-card">
    <h3 class="smtp-card-title">
        <i class="fas fa-server"></i> SMTP Configuration
    </h3>

    <div class="smtp-section-label">Server Details</div>
    <div class="smtp-form-row">
        <div class="smtp-form-group">
            <label class="smtp-form-label" for="smtp-host">SMTP Host</label>
            <input type="text" id="smtp-host" class="smtp-form-input" placeholder="e.g. smtp.gmail.com">
        </div>
        <div class="smtp-form-group">
            <label class="smtp-form-label" for="smtp-port">SMTP Port</label>
            <input type="number" id="smtp-port" class="smtp-form-input" placeholder="e.g. 587">
        </div>
        <div class="smtp-form-group">
            <label class="smtp-form-label" for="smtp-encryption">Encryption Type</label>
            <select id="smtp-encryption" class="smtp-form-select">
                <option value="">None</option>
                <option value="tls">TLS</option>
                <option value="ssl">SSL</option>
            </select>
        </div>
    </div>

    <div class="smtp-section-label" style="margin-top:8px;">Credentials</div>
    <div class="smtp-form-row">
        <div class="smtp-form-group">
            <label class="smtp-form-label" for="smtp-username">SMTP Username</label>
            <input type="text" id="smtp-username" class="smtp-form-input" placeholder="your@email.com" autocomplete="off">
        </div>
        <div class="smtp-form-group">
            <label class="smtp-form-label" for="smtp-password">Password</label>
            <div style="position:relative;">
                <input type="password" id="smtp-password" class="smtp-form-input" placeholder="Enter password" autocomplete="new-password" style="padding-right:36px;">
                <button type="button" id="toggle-password" style="position:absolute; right:8px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#888; font-size:14px; padding:4px;">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="smtp-section-label" style="margin-top:8px;">Sender Info</div>
    <div class="smtp-form-row">
        <div class="smtp-form-group">
            <label class="smtp-form-label" for="smtp-from-email">From Email</label>
            <input type="email" id="smtp-from-email" class="smtp-form-input" placeholder="noreply@yourstore.com">
        </div>
        <div class="smtp-form-group">
            <label class="smtp-form-label" for="smtp-from-name">From Name</label>
            <input type="text" id="smtp-from-name" class="smtp-form-input" placeholder="Your Store Name">
        </div>
    </div>

    <div class="smtp-form-actions">
        <button type="button" id="smtp-save-btn" class="smtp-save-btn">
            <i class="fas fa-save"></i> Save Configuration
        </button>
    </div>
</div>

{{-- Test Email Panel --}}
<div class="smtp-card">
    <h3 class="smtp-card-title">
        <i class="fas fa-paper-plane"></i> Test Connection
    </h3>

    <div id="smtp-status" class="smtp-connection-status disconnected">
        <i class="fas fa-circle"></i> Not tested yet
    </div>

    <div class="smtp-form-row">
        <div class="smtp-form-group full">
            <label class="smtp-form-label" for="test-email">Send Test Email To</label>
            <input type="email" id="test-email" class="smtp-form-input" placeholder="recipient@example.com">
        </div>
    </div>

    <div class="smtp-form-actions">
        <button type="button" id="smtp-test-btn" class="smtp-test-btn">
            <i class="fas fa-paper-plane"></i> Send Test Email
        </button>
        <button type="button" id="smtp-template-btn" class="smtp-template-btn" disabled>
            <i class="fas fa-file-alt"></i> Email Template
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    // Load saved settings
    function loadSmtpSettings() {
        $.ajax({
            url: '{{ route("admin.settings.smtp-data") }}',
            method: 'GET',
            success: function (res) {
                if (!res.data) return;

                $('#smtp-host').val(res.data.smtp_host || '');
                $('#smtp-port').val(res.data.smtp_port || '');
                $('#smtp-encryption').val(res.data.smtp_encryption || '');
                $('#smtp-username').val(res.data.smtp_username || '');
                $('#smtp-password').val(res.data.smtp_password || '');
                $('#smtp-from-email').val(res.data.smtp_from_email || '');
                $('#smtp-from-name').val(res.data.smtp_from_name || '');

                if (res.data.smtp_host && res.data.smtp_username) {
                    $('#smtp-status').removeClass('disconnected connected testing')
                        .addClass('connected').html('<i class="fas fa-check-circle"></i> Configuration saved — ready to test');
                }
            }
        });
    }

    loadSmtpSettings();

    // Toggle password visibility
    $(document).on('click', '#toggle-password', function () {
        var $input = $('#smtp-password');
        var $icon = $(this).find('i');
        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            $input.attr('type', 'password');
            $icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Save settings
    $(document).on('click', '#smtp-save-btn', function () {
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving…');

        $.ajax({
            url: '{{ route("admin.settings.smtp-save") }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                smtp_host: $('#smtp-host').val().trim(),
                smtp_port: $('#smtp-port').val().trim(),
                smtp_encryption: $('#smtp-encryption').val(),
                smtp_username: $('#smtp-username').val().trim(),
                smtp_password: $('#smtp-password').val(),
                smtp_from_email: $('#smtp-from-email').val().trim(),
                smtp_from_name: $('#smtp-from-name').val().trim(),
            },
            success: function (res) {
                toast(res.message || 'SMTP settings saved.', 'success');
                $('#smtp-status').removeClass('disconnected testing').addClass('connected')
                    .html('<i class="fas fa-check-circle"></i> Configuration saved — ready to test');
            },
            error: function (xhr) {
                toast(xhr.responseJSON?.message || 'Failed to save settings.', 'error');
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Configuration');
            }
        });
    });

    // Test email
    $(document).on('click', '#smtp-test-btn', function () {
        var $btn = $(this);
        var $testEmail = $('#test-email');
        var email = $testEmail.val().trim();

        if (!email || !email.includes('@')) {
            toast('Please enter a valid email address.', 'error');
            $testEmail.focus();
            return;
        }

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending…');
        $('#smtp-status').removeClass('disconnected connected').addClass('testing')
            .html('<i class="fas fa-sync fa-spin"></i> Sending test email…');

        $.ajax({
            url: '{{ route("admin.settings.smtp-test") }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                email: email,
            },
            success: function (res) {
                toast(res.message || 'Test email sent successfully!', 'success');
                $('#smtp-status').removeClass('testing').addClass('connected')
                    .html('<i class="fas fa-check-circle"></i> Test email sent to ' + email);
            },
            error: function (xhr) {
                var msg = xhr.responseJSON?.message || 'Failed to send test email.';
                toast(msg, 'error');
                $('#smtp-status').removeClass('testing').addClass('disconnected')
                    .html('<i class="fas fa-times-circle"></i> ' + msg);
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send Test Email');
            }
        });
    });

    // Template button (placeholder)
    $(document).on('click', '#smtp-template-btn', function () {
        toast('Email templates coming soon!', 'info');
    });

    function toast(msg, type) {
        var icon = type === 'error' ? 'fa-exclamation-circle' : type === 'info' ? 'fa-info-circle' : 'fa-check-circle';
        var el = $('<div class="toast-msg ' + (type || 'success') + '">' +
            '<i class="fas ' + icon + '"></i> ' + msg + '</div>');
        $('#toast-wrap').append(el);
        setTimeout(function () { el.fadeOut(300, function () { el.remove(); }); }, 4000);
    }
});
</script>
@endpush
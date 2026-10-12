@extends('admin.settings.index')

@section('title', 'WhatsApp Notification Settings')

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

    .settings-card { border: 1px solid #e0e0e0; border-radius: 8px; padding: 22px 26px; background:#fff; margin-bottom: 24px; }
    .settings-card-title {
        font-size: 16px; font-weight: 700; color: #222;
        margin: 0 0 16px; padding-bottom: 12px; border-bottom: 1px solid #eee;
        display: flex; align-items: center; justify-content: space-between;
    }
    .settings-card-title i { margin-right: 6px; color: #25D366; }

    .settings-form-group { margin-bottom: 18px; }
    .settings-form-label {
        display: block; font-size: 13px; font-weight: 600; color: #444; margin-bottom: 6px;
    }
    .settings-form-label .hint { font-weight: 400; color: #888; font-size: 12px; }

    .settings-input {
        width: 100%; max-width: 540px; padding: 9px 13px;
        border: 1px solid #ccc; border-radius: 6px; font-size: 14px;
        box-sizing: border-box; display: block; background: #fff;
    }
    .settings-input:focus {
        border-color: #25D366; outline: none;
        box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.15);
    }

    .settings-form-actions {
        display: flex; align-items: center; gap: 12px;
        margin-top: 24px; padding-top: 20px; border-top: 1px solid #eee;
    }

    .btn-save-settings {
        padding: 10px 24px; background: #25D366; color: #fff;
        border: none; border-radius: 6px; font-size: 14px; font-weight: 600;
        cursor: pointer; display: inline-flex; align-items: center; gap: 8px;
        box-shadow: 0 2px 6px rgba(37, 211, 102, 0.3);
        transition: all 0.2s ease;
    }
    .btn-save-settings:hover { background: #1ebc57; transform: translateY(-1px); }
    .btn-save-settings:disabled { opacity: 0.6; cursor: not-allowed; }

    .btn-test-wa {
        padding: 8px 16px; background: #f1f5f9; color: #1e293b;
        border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13px; font-weight: 600;
        cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-test-wa:hover { background: #e2e8f0; }

    /* Plugin card */
    .plugin-card {
        border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px 20px;
        margin-bottom: 18px; max-width: 100%; background: #fafafa;
    }
    .plugin-card-header {
        display: flex; align-items: center; justify-content: space-between;
        gap: 12px; margin-bottom: 12px;
    }
    .plugin-card-info { display: flex; align-items: center; gap: 12px; }
    .plugin-card-icon {
        width: 42px; height: 42px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; flex-shrink: 0;
    }
    .plugin-card-icon.wa { background: #e8f5e9; color: #2e7d32; }
    .plugin-card-name { font-size: 15px; font-weight: 700; color: #1e293b; }
    .plugin-card-status { font-size: 12px; color: #64748b; margin-top: 2px; }

    /* Toggle switch */
    .plugin-toggle {
        position: relative; width: 46px; height: 24px; flex-shrink: 0;
    }
    .plugin-toggle input { opacity: 0; width: 0; height: 0; }
    .plugin-toggle .slider {
        position: absolute; cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background: #cbd5e1; border-radius: 24px;
        transition: .2s;
    }
    .plugin-toggle .slider:before {
        position: absolute; content: ""; height: 18px; width: 18px;
        left: 3px; bottom: 3px;
        background: #fff; border-radius: 50%;
        transition: .2s;
    }
    .plugin-toggle input:checked + .slider { background: #25D366; }
    .plugin-toggle input:checked + .slider:before { transform: translateX(22px); }

    /* Plugin body */
    .plugin-body { display: none; margin-top: 14px; padding-top: 14px; border-top: 1px dashed #cbd5e1; }
    .plugin-body.open { display: block; }
    .plugin-body-row {
        display: flex; gap: 16px; flex-wrap: wrap;
        margin-bottom: 12px;
    }
    .plugin-body-row .settings-form-group { flex: 1; min-width: 240px; margin-bottom: 0; }

    /* Wallet Card */
    .wallet-stat-card {
        display: flex; align-items: center; justify-content: space-between;
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: #fff; border-radius: 8px; padding: 16px 20px; margin-bottom: 16px;
    }
    .wallet-stat-val { font-size: 24px; font-weight: 800; }
    .wallet-stat-lbl { font-size: 12px; opacity: 0.9; }

    /* Logs Table */
    .log-table { width: 100%; border-collapse: collapse; font-size: 13px; margin-top: 10px; }
    .log-table th { background: #f8fafc; padding: 8px 10px; text-align: left; font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0; }
    .log-table td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; color: #334155; }
    .badge-success { background: #dcfce7; color: #166534; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; }
    .badge-failed  { background: #fee2e2; color: #991b1b; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; }

    @media (max-width: 768px) {
        .settings-input { max-width: 100%; }
        .plugin-body-row .settings-form-group { min-width: 100%; }
    }
</style>
@endpush

@section('settings_content')
<div id="toast-wrap"></div>

<div class="settings-card">
    <h3 class="settings-card-title">
        <span><i class="fab fa-whatsapp"></i> WhatsApp Order Notifications (Fast2SMS API)</span>
        <button type="button" class="btn-test-wa" id="btn-test-notification">
            <i class="fas fa-paper-plane"></i> Send Test WhatsApp
        </button>
    </h3>

    {{-- Wallet Summary --}}
    <div class="wallet-stat-card">
        <div>
            <div class="wallet-stat-lbl">WhatsApp Notification Credit Balance</div>
            <div class="wallet-stat-val" id="wallet-balance-val">₹0.00</div>
            <div style="font-size: 11px; opacity: 0.9; margin-top: 3px; display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
                <span>Estimated Messages: <strong id="wallet-est-msgs">0</strong> (~₹0.50 / msg, 2 messages per ₹1)</span>
                <span id="wa-upi-badge" style="background:rgba(255,255,255,0.2); padding:2px 8px; border-radius:12px; font-size:10px; font-weight:700;">
                    <i class="fas fa-qrcode"></i> UPI Gateway Ready
                </span>
            </div>
        </div>
        <div style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            <button type="button" class="btn-test-wa" id="btn-add-amount-modal" style="background:#fff; color:#065f46; font-weight:700; box-shadow:0 2px 6px rgba(0,0,0,0.12);">
                <i class="fas fa-wallet"></i> Add Amount (UPI)
            </button>
        </div>
    </div>

    {{-- Add Amount (UPI Gateway) Modal --}}
    <div id="wa-add-amount-modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:99999; align-items:center; justify-content:center; padding:16px;">
        <div style="background:#fff; border-radius:12px; max-width:480px; width:100%; box-shadow:0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); overflow:hidden;">
            <div style="padding:16px 20px; border-bottom:1px solid #e2e8f0; display:flex; align-items:center; justify-content:space-between; background:#f8fafc;">
                <h4 style="margin:0; font-size:15px; font-weight:700; color:#0f172a; display:flex; align-items:center; gap:8px;">
                    <i class="fas fa-wallet" style="color:#10b981;"></i> Add WhatsApp Notification Credit
                </h4>
                <button type="button" id="btn-close-add-modal" style="background:none; border:none; font-size:20px; color:#64748b; cursor:pointer; line-height:1;">&times;</button>
            </div>
            <div style="padding:20px;">
                <p style="margin:0 0 14px; font-size:13px; color:#64748b;">
                    Recharge wallet using UPI payment gateway. Rate: <strong>2 messages = ₹1.00</strong> (₹0.50 per notification).
                </p>

                <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Select Recharge Amount:</label>
                <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:14px;">
                    <button type="button" class="btn-amt-preset" data-amt="50" style="padding:6px 12px; border:1px solid #cbd5e1; border-radius:6px; background:#f8fafc; font-weight:600; cursor:pointer; font-size:13px;">₹50</button>
                    <button type="button" class="btn-amt-preset" data-amt="100" style="padding:6px 12px; border:1px solid #cbd5e1; border-radius:6px; background:#10b981; color:#fff; font-weight:600; cursor:pointer; font-size:13px;">₹100</button>
                    <button type="button" class="btn-amt-preset" data-amt="200" style="padding:6px 12px; border:1px solid #cbd5e1; border-radius:6px; background:#f8fafc; font-weight:600; cursor:pointer; font-size:13px;">₹200</button>
                    <button type="button" class="btn-amt-preset" data-amt="500" style="padding:6px 12px; border:1px solid #cbd5e1; border-radius:6px; background:#f8fafc; font-weight:600; cursor:pointer; font-size:13px;">₹500</button>
                    <button type="button" class="btn-amt-preset" data-amt="1000" style="padding:6px 12px; border:1px solid #cbd5e1; border-radius:6px; background:#f8fafc; font-weight:600; cursor:pointer; font-size:13px;">₹1000</button>
                </div>

                <div style="margin-bottom:14px;">
                    <label for="wa-custom-amount" style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Custom Amount (₹):</label>
                    <input type="number" id="wa-custom-amount" class="settings-input" style="max-width:100%; font-size:15px; font-weight:700; padding:9px 12px;" value="100" min="1" step="1">
                </div>

                <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:10px 14px; margin-bottom:14px;">
                    <div style="display:flex; justify-content:space-between; font-size:13px; color:#166534; font-weight:600;">
                        <span>Estimated Messages Credited:</span>
                        <span id="wa-modal-est-msgs" style="font-size:14px; font-weight:800;">200 messages</span>
                    </div>
                </div>

                <div id="wa-upi-info-note" style="font-size:11px; color:#64748b; margin-bottom:14px; display:flex; align-items:center; gap:6px;">
                    <i class="fas fa-key" style="color:#0284c7;"></i>
                    <span>UPI gateway uses dedicated separate key configured in <code>.env</code> (<code>WA_UPI_KEY</code>).</span>
                </div>

                <div id="upi-pending-section" style="display:none; background:#fffbeb; border:1px solid #fef3c7; border-radius:8px; padding:12px; margin-bottom:14px;">
                    <div style="font-size:12px; font-weight:700; color:#b45309; margin-bottom:6px;">
                        <i class="fas fa-spinner fa-spin"></i> UPI Payment Order: <span id="upi-pending-order-id">-</span>
                    </div>
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <a href="#" id="upi-pay-link" target="_blank" class="btn-test-wa" style="background:#d97706; color:#fff; border:none; text-decoration:none; padding:6px 12px; font-size:12px;">
                            <i class="fas fa-external-link-alt"></i> Complete Payment
                        </a>
                        <button type="button" id="btn-verify-upi" class="btn-test-wa" style="background:#fff; color:#b45309; border:1px solid #d97706; padding:6px 12px; font-size:12px;">
                            <i class="fas fa-sync"></i> Verify Status
                        </button>
                    </div>
                </div>

                <div style="display:flex; gap:8px; justify-content:flex-end;">
                    <button type="button" id="btn-direct-add" class="btn-test-wa" style="background:#f1f5f9; color:#334155; font-size:13px;">
                        Direct Credit
                    </button>
                    <button type="button" id="btn-pay-upi" class="btn-save-settings" style="background:#10b981; padding:8px 16px; font-size:13px;">
                        <i class="fas fa-qrcode"></i> Pay via UPI
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Admin Mobile Alert Number Configuration Card --}}
    <div class="settings-card" style="border-left: 4px solid #25D366; background: #fafafa; padding: 18px 22px; margin-bottom: 20px;">
        <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 14px;">
            <div style="flex: 1; min-width: 280px;">
                <label class="settings-form-label" style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 4px;">
                    <i class="fab fa-whatsapp" style="color: #25D366; font-size: 16px; margin-right: 4px;"></i> Admin WhatsApp Mobile Number
                </label>
                <p style="font-size: 12px; color: #64748b; margin: 0 0 10px;">
                    Enter the admin mobile number to receive instant order summary alerts with attached invoice PDFs whenever a customer places an order.
                </p>
                <div style="display: flex; gap: 10px; align-items: center; max-width: 520px;">
                    <input type="text" id="admin-mobile-input" class="settings-input" placeholder="e.g. 919876543210 or 9876543210" maxlength="15" style="background:#fff; font-weight:600; font-size:14px;">
                    <button type="button" class="btn-save-settings" id="btn-save-admin-phone" style="white-space: nowrap; padding: 9px 18px;">
                        <i class="fas fa-save"></i> Save Number
                    </button>
                </div>
                <div style="font-size: 11px; color: #94a3b8; margin-top: 6px;">
                    <i class="fas fa-info-circle"></i> India numbers can be entered with or without <code>91</code> prefix (e.g. <code>9876543210</code> or <code>919876543210</code>).
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Message Logs --}}
    <div style="margin-top: 28px;">
        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom: 8px;">
            <h4 style="font-size: 14px; font-weight: 700; color: #334155; margin:0;">
                <i class="fas fa-history"></i> Recent WhatsApp Notification Logs
            </h4>
            <span style="font-size: 12px; color: #64748b;">Auto-logged on order dispatch</span>
        </div>
        <div style="overflow-x: auto;">
            <table class="log-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Phone Number</th>
                        <th>Template / Purpose</th>
                        <th>Status</th>
                        <th>Date &amp; Time</th>
                    </tr>
                </thead>
                <tbody id="logs-tbody">
                    <tr><td colspan="5" style="text-align:center; color:#94a3b8;">Loading logs…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    // Load data
    function loadWhatsAppSettings() {
        $.ajax({
            url: '{{ route("admin.settings.whatsapp-data") }}',
            method: 'GET',
            success: function (res) {
                if (!res.success || !res.data) return;
                var d = res.data;

                // Admin mobile input
                $('#admin-mobile-input').val(d.notif_admin_phone || '');

                // Wallet
                var bal = parseFloat(d.wallet_balance || 0);
                $('#wallet-balance-val').text('₹' + bal.toFixed(2));
                $('#wallet-est-msgs').text(Math.floor(bal / 0.50));

                if (d.wa_upi_configured) {
                    var keyText = d.wa_upi_has_separate_key ? ' (WA_UPI_KEY)' : '';
                    $('#wa-upi-badge').html('<i class="fas fa-qrcode"></i> UPI Gateway Active' + keyText).show();
                } else {
                    $('#wa-upi-badge').html('<i class="fas fa-info-circle"></i> UPI (Direct Topup)').show();
                }

                // Render Logs
                var logs = d.recent_logs || [];
                if (logs.length > 0) {
                    var html = '';
                    logs.forEach(function (log, idx) {
                        var statusBadge = log.status === 'success'
                            ? '<span class="badge-success"><i class="fas fa-check"></i> Sent</span>'
                            : '<span class="badge-failed"><i class="fas fa-times"></i> ' + (log.status || 'Failed') + '</span>';
                        var dateStr = log.created_at ? new Date(log.created_at).toLocaleString() : '-';

                        html += '<tr>' +
                            '<td>' + (idx + 1) + '</td>' +
                            '<td><strong>+' + (log.telephone || '-') + '</strong></td>' +
                            '<td>' + (log.template_name || 'order_notify') + ' (' + (log.source || 'checkout') + ')</td>' +
                            '<td>' + statusBadge + '</td>' +
                            '<td>' + dateStr + '</td>' +
                        '</tr>';
                    });
                    $('#logs-tbody').html(html);
                } else {
                    $('#logs-tbody').html('<tr><td colspan="5" style="text-align:center; color:#94a3b8;">No message logs recorded yet.</td></tr>');
                }
            }
        });
    }

    loadWhatsAppSettings();

    // Save Admin Mobile Number
    $(document).on('click', '#btn-save-admin-phone', function () {
        var $btn = $(this);
        var adminPhone = $('#admin-mobile-input').val().trim();

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving…');

        $.ajax({
            url: '{{ route("admin.settings.whatsapp-save") }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                notif_admin_phone: adminPhone
            },
            success: function (res) {
                toast(res.message || 'Admin WhatsApp mobile number saved successfully.', 'success');
                loadWhatsAppSettings();
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Could not save mobile number.';
                toast(msg, 'error');
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Number');
            }
        });
    });

    // Test Notification (Direct 1-Click to configured Admin Mobile Number)
    $(document).on('click', '#btn-test-notification', function () {
        var $btn = $(this);
        var targetPhone = $('#admin-mobile-input').val().trim();

        if (!targetPhone) {
            toast('Please enter or save an Admin WhatsApp mobile number first.', 'error');
            $('#admin-mobile-input').focus();
            return;
        }

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Sending…');

        $.ajax({
            url: '{{ route("admin.settings.plugin-test-wa") }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                admin_phone: targetPhone
            },
            success: function (res) {
                toast(res.message || 'Test message sent successfully!', 'success');
                loadWhatsAppSettings();
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to send test WhatsApp message.';
                toast(msg, 'error');
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fas fa-paper-plane"></i> Send Test WhatsApp');
            }
        });
    });

    // Add Amount Modal Open / Close
    $(document).on('click', '#btn-add-amount-modal', function () {
        $('#wa-add-amount-modal').css('display', 'flex');
        updateModalCalculations();
    });

    $(document).on('click', '#btn-close-add-modal', function () {
        $('#wa-add-amount-modal').hide();
        $('#upi-pending-section').hide();
    });

    function updateModalCalculations() {
        var amt = parseFloat($('#wa-custom-amount').val()) || 0;
        var msgs = Math.floor(amt / 0.50);
        $('#wa-modal-est-msgs').text(msgs + ' messages');
    }

    $(document).on('input', '#wa-custom-amount', updateModalCalculations);

    $(document).on('click', '.btn-amt-preset', function () {
        $('.btn-amt-preset').css({ 'background': '#f8fafc', 'color': '#334155' });
        $(this).css({ 'background': '#10b981', 'color': '#fff' });
        var val = $(this).data('amt');
        $('#wa-custom-amount').val(val);
        updateModalCalculations();
    });

    // Pay via UPI Gateway
    $(document).on('click', '#btn-pay-upi', function () {
        var $btn = $(this);
        var amt = parseFloat($('#wa-custom-amount').val());
        if (!amt || amt <= 0) {
            toast('Please enter a valid amount.', 'error');
            return;
        }

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing…');

        $.ajax({
            url: '{{ route("admin.settings.whatsapp-add-amount") }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                amount: amt,
                mode: 'upi'
            },
            success: function (res) {
                if (res.requires_upi && res.payment_url) {
                    $('#upi-pending-order-id').text(res.order_id);
                    $('#upi-pay-link').attr('href', res.payment_url);
                    $('#upi-pending-section').show();
                    window.open(res.payment_url, '_blank');
                    toast('UPI payment window opened. Please complete payment and click "Verify Status".', 'success');
                } else {
                    toast(res.message || 'Amount credited successfully!', 'success');
                    $('#wa-add-amount-modal').hide();
                    loadWhatsAppSettings();
                }
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to initiate UPI payment.';
                toast(msg, 'error');
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fas fa-qrcode"></i> Pay via UPI');
            }
        });
    });

    // Direct Add Amount
    $(document).on('click', '#btn-direct-add', function () {
        var $btn = $(this);
        var amt = parseFloat($('#wa-custom-amount').val());
        if (!amt || amt <= 0) {
            toast('Please enter a valid amount.', 'error');
            return;
        }

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Crediting…');

        $.ajax({
            url: '{{ route("admin.settings.whatsapp-add-amount") }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                amount: amt,
                mode: 'direct'
            },
            success: function (res) {
                toast(res.message || 'Amount added to WhatsApp wallet!', 'success');
                $('#wa-add-amount-modal').hide();
                loadWhatsAppSettings();
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to add amount.';
                toast(msg, 'error');
            },
            complete: function () {
                $btn.prop('disabled', false).html('Direct Credit');
            }
        });
    });

    // Verify UPI Payment
    $(document).on('click', '#btn-verify-upi', function () {
        var $btn = $(this);
        var orderId = $('#upi-pending-order-id').text();
        var amt = parseFloat($('#wa-custom-amount').val());

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Checking…');

        $.ajax({
            url: '{{ route("admin.settings.whatsapp-verify-upi") }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                order_id: orderId,
                amount: amt
            },
            success: function (res) {
                if (res.is_paid) {
                    toast(res.message || 'Payment confirmed! Wallet recharged.', 'success');
                    $('#wa-add-amount-modal').hide();
                    $('#upi-pending-section').hide();
                    loadWhatsAppSettings();
                } else {
                    toast(res.message || 'Payment is still pending. Please complete transaction.', 'error');
                }
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Status check failed.';
                toast(msg, 'error');
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fas fa-sync"></i> Verify Status');
            }
        });
    });

    function toast(msg, type) {
        var icon = (type === 'error') ? 'fa-exclamation-circle' : 'fa-check-circle';
        var el = $('<div class="toast-msg ' + (type || 'success') + '">' +
            '<i class="fas ' + icon + '"></i> ' + msg + '</div>');
        $('#toast-wrap').append(el);
        setTimeout(function () { el.fadeOut(300, function () { el.remove(); }); }, 3500);
    }
});
</script>
@endpush

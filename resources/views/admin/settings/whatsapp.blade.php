@extends('admin.settings.index')

@section('title', 'WhatsApp Notification Settings')

@push('styles')
<style>
    #toast-wrap { position:fixed; top:18px; right:18px; z-index:99999; min-width:260px; max-width: calc(100vw - 36px); }
    .toast-msg {
        display:flex; align-items:center; gap:8px;
        padding:10px 14px; border-radius:6px; margin-bottom:7px;
        font-size:13px; font-weight:500; box-shadow:0 4px 12px rgba(0,0,0,0.12);
        word-break: break-word;
    }
    .toast-msg.success { background:#f0fdf4; border-left:4px solid #16a34a; color:#166534; }
    .toast-msg.error   { background:#fff1f2; border-left:4px solid #e11d48; color:#9f1239; }

    .settings-card {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 24px;
        background: #fff;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        box-sizing: border-box;
        width: 100%;
        max-width: 100%;
        min-width: 0;
    }
    .settings-card-title {
        font-size: 16px; font-weight: 700; color: #1e293b;
        margin: 0 0 18px; padding-bottom: 14px; border-bottom: 1px solid #f1f5f9;
        display: flex; align-items: center; justify-content: space-between;
        flex-wrap: wrap; gap: 12px;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
    .settings-card-title span {
        display: inline-flex; align-items: center; gap: 8px; flex: 1 1 200px; min-width: 0;
        word-break: break-word;
    }
    .settings-card-title i { color: #25D366; font-size: 18px; }

    .settings-form-group { margin-bottom: 18px; width: 100%; box-sizing: border-box; }
    .settings-form-label {
        display: block; font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 6px;
    }
    .settings-form-label .hint { font-weight: 400; color: #64748b; font-size: 12px; }

    .settings-input {
        width: 100%; max-width: 540px; padding: 10px 14px;
        border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px;
        box-sizing: border-box; display: block; background: #fff;
        transition: border-color 0.15s, box-shadow 0.15s;
    }
    .settings-input:focus {
        border-color: #25D366; outline: none;
        box-shadow: 0 0 0 3px rgba(37, 211, 102, 0.18);
    }

    .btn-save-settings {
        padding: 10px 22px; background: #25D366; color: #fff;
        border: none; border-radius: 8px; font-size: 13.5px; font-weight: 700;
        cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        box-shadow: 0 2px 6px rgba(37, 211, 102, 0.3);
        transition: all 0.2s ease;
        white-space: nowrap;
        max-width: 100%;
        box-sizing: border-box;
    }
    .btn-save-settings:hover { background: #1ebc57; transform: translateY(-1px); }
    .btn-save-settings:disabled { opacity: 0.6; cursor: not-allowed; }

    .btn-test-wa {
        padding: 8px 16px; background: #f8fafc; color: #334155;
        border: 1px solid #cbd5e1; border-radius: 8px; font-size: 13px; font-weight: 600;
        cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        transition: all 0.15s ease;
        white-space: nowrap;
        max-width: 100%;
        box-sizing: border-box;
    }
    .btn-test-wa:hover { background: #f1f5f9; border-color: #94a3b8; color: #0f172a; }

    /* Wallet Card */
    .wallet-stat-card {
        display: flex; align-items: center; justify-content: space-between;
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: #fff; border-radius: 12px; padding: 20px 22px; margin-bottom: 20px;
        box-shadow: 0 4px 14px rgba(5, 150, 105, 0.18);
        gap: 16px; flex-wrap: wrap;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        min-width: 0;
    }
    .wallet-stat-info {
        flex: 1 1 220px; min-width: 0;
    }
    .wallet-stat-lbl {
        font-size: 12px; font-weight: 700; color: rgba(255, 255, 255, 0.88);
        text-transform: uppercase; letter-spacing: 0.5px;
    }
    .wallet-stat-val {
        font-size: 28px; font-weight: 800; letter-spacing: -0.5px;
        margin: 4px 0; line-height: 1.1;
    }
    .wallet-stat-meta {
        font-size: 12px; color: rgba(255, 255, 255, 0.92);
        margin-top: 4px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
    }
    .wallet-upi-badge {
        background: rgba(255, 255, 255, 0.22);
        padding: 3px 9px; border-radius: 20px;
        font-size: 10.5px; font-weight: 700;
        backdrop-filter: blur(4px);
        display: inline-flex; align-items: center; gap: 5px;
    }
    .wallet-stat-actions {
        max-width: 100%;
        box-sizing: border-box;
    }
    .btn-wallet-recharge {
        background: #ffffff !important;
        color: #065f46 !important;
        font-weight: 700 !important;
        border: none !important;
        border-radius: 8px !important;
        padding: 10px 18px !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12) !important;
        font-size: 13.5px !important;
        cursor: pointer;
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        transition: transform 0.15s, box-shadow 0.15s;
        white-space: nowrap;
        max-width: 100%;
        box-sizing: border-box;
    }
    .btn-wallet-recharge:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.20) !important;
    }

    /* Admin Mobile Alert Card */
    .admin-mobile-card {
        border-left: 4px solid #25D366 !important;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px 22px;
        margin-bottom: 20px;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
        min-width: 0;
    }
    .admin-phone-input-row {
        display: flex; gap: 10px; align-items: center;
        max-width: 520px; width: 100%;
        box-sizing: border-box;
    }
    .admin-phone-input-row .settings-input {
        flex: 1; min-width: 0; width: 100%;
        box-sizing: border-box;
    }

    /* Logs Table */
    .logs-header-row {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 10px; flex-wrap: wrap; gap: 8px;
        width: 100%; box-sizing: border-box;
    }
    .log-table-wrap {
        width: 100%; max-width: 100%; overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        border: 1px solid #e2e8f0; border-radius: 10px;
        background: #fff;
        box-sizing: border-box;
        min-width: 0;
    }
    .log-table {
        width: 100%; min-width: 580px;
        border-collapse: collapse; font-size: 13px; margin: 0;
    }
    .log-table th {
        background: #f8fafc; padding: 10px 14px; text-align: left;
        font-weight: 600; color: #475569; border-bottom: 1px solid #e2e8f0;
        white-space: nowrap;
    }
    .log-table td {
        padding: 10px 14px; border-bottom: 1px solid #f1f5f9; color: #334155;
        vertical-align: middle;
    }
    .badge-success {
        background: #dcfce7; color: #166534; padding: 3px 8px;
        border-radius: 12px; font-size: 11px; font-weight: 700;
        display: inline-flex; align-items: center; gap: 4px;
    }
    .badge-failed  {
        background: #fee2e2; color: #991b1b; padding: 3px 8px;
        border-radius: 12px; font-size: 11px; font-weight: 700;
        display: inline-flex; align-items: center; gap: 4px;
    }

    @media (max-width: 768px) {
        .settings-card { padding: 16px 12px; border-radius: 10px; margin-bottom: 16px; width: 100%; max-width: 100%; box-sizing: border-box; }
        .settings-card-title {
            flex-direction: column; align-items: stretch; gap: 10px;
        }
        .settings-card-title span {
            min-width: 0 !important; font-size: 14.5px; word-break: break-word;
        }
        .settings-card-title .btn-test-wa {
            width: 100%; justify-content: center;
        }
        .wallet-stat-card {
            flex-direction: column; align-items: stretch; padding: 16px 14px; gap: 14px; width: 100%; max-width: 100%; box-sizing: border-box;
        }
        .wallet-stat-info {
            min-width: 0 !important; width: 100%;
        }
        .wallet-stat-val { font-size: 24px; }
        .wallet-stat-meta { font-size: 12px; line-height: 1.4; }
        .wallet-stat-actions { width: 100%; }
        .btn-wallet-recharge {
            width: 100% !important; justify-content: center !important;
        }
        .admin-mobile-card { padding: 16px 14px !important; width: 100%; max-width: 100%; box-sizing: border-box; }
        .admin-phone-input-row {
            flex-direction: column; align-items: stretch; gap: 10px; width: 100%; max-width: 100%; box-sizing: border-box;
        }
        .admin-phone-input-row .settings-input {
            width: 100%; max-width: 100%;
        }
        .admin-phone-input-row .btn-save-settings {
            width: 100% !important; justify-content: center !important;
        }
        .settings-input { max-width: 100%; }
    }

    @media (max-width: 576px) {
        .settings-card { padding: 14px 10px; }
        .settings-card-title {
            flex-direction: column; align-items: stretch; gap: 10px;
        }
        .settings-card-title span {
            min-width: 0 !important; font-size: 14px; word-break: break-word;
        }
        .settings-card-title .btn-test-wa {
            width: 100%; justify-content: center;
        }
        .wallet-stat-card {
            flex-direction: column; align-items: stretch; padding: 16px 12px; gap: 14px;
        }
        .wallet-stat-info {
            min-width: 0 !important; width: 100%;
        }
        .wallet-stat-val { font-size: 24px; }
        .wallet-stat-meta { font-size: 11.5px; line-height: 1.4; }
        .btn-wallet-recharge {
            width: 100% !important; justify-content: center !important;
        }
        .admin-phone-input-row {
            flex-direction: column; align-items: stretch; gap: 10px; width: 100%; max-width: 100%;
        }
        .admin-phone-input-row .settings-input {
            width: 100%; max-width: 100%;
        }
        .admin-phone-input-row .btn-save-settings {
            width: 100% !important; justify-content: center !important;
        }
        #wa-add-amount-modal { padding: 8px !important; }
        #wa-add-amount-modal > div { max-width: 100% !important; border-radius: 10px !important; }
        .btn-amt-preset { font-size: 12px !important; padding: 7px 6px !important; }
        #upi-pending-section .btn-test-wa,
        #btn-pay-upi { width: 100% !important; justify-content: center !important; }
    }

    @media (max-width: 360px) {
        .settings-card { padding: 12px 8px; }
        .wallet-stat-card { padding: 12px 10px; }
        .wallet-stat-val { font-size: 21px; }
        .btn-wallet-recharge { font-size: 12px !important; padding: 8px 12px !important; }
        .btn-save-settings { font-size: 12px; padding: 8px 12px; }
        .toast-msg { font-size: 12px; padding: 8px 10px; }
        #wa-add-amount-modal > div > div:last-child { padding: 14px 10px !important; }
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
        <div class="wallet-stat-info">
            <div class="wallet-stat-lbl">WhatsApp Notification Credit Balance</div>
            <div class="wallet-stat-val" id="wallet-balance-val">₹0.00</div>
            <div class="wallet-stat-meta">
                <span>Estimated Messages: <strong id="wallet-est-msgs">0</strong> (~₹0.50 / msg, 2 messages per ₹1)</span>
                <span id="wa-upi-badge" class="wallet-upi-badge">
                    <i class="fas fa-qrcode"></i> UPI Gateway Ready
                </span>
            </div>
        </div>
        <div class="wallet-stat-actions">
            <button type="button" class="btn-wallet-recharge" id="btn-add-amount-modal">
                <i class="fas fa-wallet"></i> Add Amount (UPI)
            </button>
        </div>
    </div>

    {{-- Admin Mobile Alert Number Configuration Card --}}
    <div class="admin-mobile-card">
        <label class="settings-form-label" style="font-size: 14px; font-weight: 700; color: #1e293b; margin-bottom: 4px;">
            <i class="fab fa-whatsapp" style="color: #25D366; font-size: 16px; margin-right: 4px;"></i> Admin WhatsApp Mobile Number
        </label>
        <p style="font-size: 12px; color: #64748b; margin: 0 0 12px; line-height: 1.5;">
            Enter the admin mobile number to receive instant order summary alerts with attached invoice PDFs whenever a customer places an order.
        </p>
        <div class="admin-phone-input-row">
            <input type="text" id="admin-mobile-input" class="settings-input" placeholder="e.g. 919876543210 or 9876543210" maxlength="15" style="background:#fff; font-weight:600; font-size:14px;">
            <button type="button" class="btn-save-settings" id="btn-save-admin-phone">
                <i class="fas fa-save"></i> Save Number
            </button>
        </div>
        <div style="font-size: 11px; color: #94a3b8; margin-top: 8px;">
            <i class="fas fa-info-circle"></i> India numbers can be entered with or without <code>91</code> prefix (e.g. <code>9876543210</code> or <code>919876543210</code>).
        </div>
    </div>

    {{-- Recent Message Logs --}}
    <div style="margin-top: 28px;">
        <div class="logs-header-row">
            <h4 style="font-size: 14px; font-weight: 700; color: #334155; margin:0;">
                <i class="fas fa-history"></i> Recent WhatsApp Notification Logs
            </h4>
            <span style="font-size: 12px; color: #64748b;">Auto-logged on order dispatch</span>
        </div>
        <div class="log-table-wrap">
            <table class="log-table">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th>Phone Number</th>
                        <th>Template / Purpose</th>
                        <th>Status</th>
                        <th>Date &amp; Time</th>
                    </tr>
                </thead>
                <tbody id="logs-tbody">
                    <tr><td colspan="5" style="text-align:center; color:#94a3b8; padding:20px;">Loading logs…</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Add Amount (UPI Gateway) Modal --}}
<div id="wa-add-amount-modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.6); z-index:99999; align-items:center; justify-content:center; padding:16px; box-sizing:border-box;">
    <div style="background:#fff; border-radius:14px; max-width:480px; width:100%; box-shadow:0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); overflow:hidden; max-height:92vh; display:flex; flex-direction:column; margin:auto;">
        <div style="padding:16px 20px; border-bottom:1px solid #e2e8f0; display:flex; align-items:center; justify-content:space-between; background:#f8fafc; flex-shrink:0;">
            <h4 style="margin:0; font-size:15px; font-weight:700; color:#0f172a; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-wallet" style="color:#10b981;"></i> Add WhatsApp Notification Credit
            </h4>
            <button type="button" id="btn-close-add-modal" style="background:none; border:none; font-size:22px; color:#64748b; cursor:pointer; line-height:1; padding:0 4px;">&times;</button>
        </div>
        <div style="padding:20px; overflow-y:auto;">
            <p style="margin:0 0 14px; font-size:13px; color:#64748b; line-height:1.5;">
                Recharge wallet using secure UPI payment gateway. Rate: <strong>₹0.50 per notification</strong> (₹1.00 = 2 messages).
                Notification credits are added strictly after verified payment confirmation.
            </p>

            <label style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:8px;">Select Recharge Amount:</label>
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(54px, 1fr)); gap:8px; margin-bottom:14px;">
                <button type="button" class="btn-amt-preset" data-amt="50" style="padding:8px 10px; border:1px solid #cbd5e1; border-radius:6px; background:#f8fafc; font-weight:700; cursor:pointer; font-size:13px;">₹50</button>
                <button type="button" class="btn-amt-preset" data-amt="100" style="padding:8px 10px; border:1px solid #10b981; border-radius:6px; background:#10b981; color:#fff; font-weight:700; cursor:pointer; font-size:13px;">₹100</button>
                <button type="button" class="btn-amt-preset" data-amt="200" style="padding:8px 10px; border:1px solid #cbd5e1; border-radius:6px; background:#f8fafc; font-weight:700; cursor:pointer; font-size:13px;">₹200</button>
                <button type="button" class="btn-amt-preset" data-amt="500" style="padding:8px 10px; border:1px solid #cbd5e1; border-radius:6px; background:#f8fafc; font-weight:700; cursor:pointer; font-size:13px;">₹500</button>
                <button type="button" class="btn-amt-preset" data-amt="1000" style="padding:8px 10px; border:1px solid #cbd5e1; border-radius:6px; background:#f8fafc; font-weight:700; cursor:pointer; font-size:13px;">₹1000</button>
            </div>

            <div style="margin-bottom:14px;">
                <label for="wa-custom-amount" style="display:block; font-size:12px; font-weight:700; color:#334155; margin-bottom:6px;">Custom Amount (₹):</label>
                <input type="number" id="wa-custom-amount" class="settings-input" style="max-width:100%; font-size:15px; font-weight:700; padding:10px 14px;" value="100" min="1" step="1">
            </div>

            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:8px; padding:12px 14px; margin-bottom:14px;">
                <div style="display:flex; justify-content:space-between; align-items:center; font-size:13px; color:#166534; font-weight:600; flex-wrap:wrap; gap:6px;">
                    <span>Estimated Messages (₹0.50/msg):</span>
                    <span id="wa-modal-est-msgs" style="font-size:15px; font-weight:800;">200 messages</span>
                </div>
                <div style="font-size:11px; color:#15803d; margin-top:4px;">
                    <i class="fas fa-shield-alt"></i> Estimate only — credits are added to wallet balance strictly upon verified payment.
                </div>
            </div>

            <div id="upi-pending-section" style="display:none; background:#fffbeb; border:1px solid #fef3c7; border-radius:8px; padding:12px; margin-bottom:14px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; flex-wrap:wrap; gap:6px;">
                    <span style="font-size:12px; font-weight:700; color:#b45309;">
                        <i class="fas fa-spinner fa-spin"></i> UPI Order: <span id="upi-pending-order-id">-</span>
                    </span>
                    <span id="upi-status-badge" style="font-size:11px; font-weight:700; background:#fef3c7; color:#92400e; padding:2px 8px; border-radius:4px; border:1px solid #fde68a;">Pending</span>
                </div>
                <div style="font-size:12px; color:#78350f; margin-bottom:10px; line-height:1.4;">
                    Please complete the payment in the UPI app or gateway window, then click <strong>Verify Status</strong>. Status checks are safe to retry and protect against duplicate credits.
                </div>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <a href="#" id="upi-pay-link" target="_blank" class="btn-test-wa" style="background:#d97706; color:#fff; border:none; text-decoration:none; padding:8px 14px; font-size:12.5px; display:inline-flex; align-items:center; gap:6px;">
                        <i class="fas fa-external-link-alt"></i> Complete Payment
                    </a>
                    <button type="button" id="btn-verify-upi" class="btn-test-wa" style="background:#fff; color:#b45309; border:1px solid #d97706; padding:8px 14px; font-size:12.5px; display:inline-flex; align-items:center; gap:6px;">
                        <i class="fas fa-sync"></i> Verify Status
                    </button>
                </div>
            </div>

            <div style="display:flex; gap:8px; justify-content:flex-end; flex-wrap:wrap;">
                <button type="button" id="btn-pay-upi" class="btn-save-settings" style="background:#10b981; padding:9px 18px; font-size:13px;">
                    <i class="fas fa-qrcode"></i> Pay via UPI
                </button>
            </div>
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

    $(document).on('click', '#wa-add-amount-modal', function (e) {
        if (e.target === this) {
            $('#wa-add-amount-modal').hide();
            $('#upi-pending-section').hide();
        }
    });

    $(document).on('keydown', function (e) {
        if (e.key === 'Escape' && $('#wa-add-amount-modal').is(':visible')) {
            $('#wa-add-amount-modal').hide();
            $('#upi-pending-section').hide();
        }
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

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Initiating UPI Order…');

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
                    $('#upi-status-badge').text('Pending Payment').css({ 'background': '#fef3c7', 'color': '#92400e', 'border-color': '#fde68a' });
                    $('#upi-pending-section').slideDown(200);
                    window.open(res.payment_url, '_blank');
                    toast('UPI payment window opened. Complete payment and click "Verify Status".', 'success');
                } else {
                    toast(res.message || 'Payment processing initiated.', 'info');
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

    // Verify UPI Payment
    $(document).on('click', '#btn-verify-upi', function () {
        var $btn = $(this);
        var orderId = $('#upi-pending-order-id').text();
        var amt = parseFloat($('#wa-custom-amount').val());

        if (!orderId || orderId === '-') {
            toast('No pending UPI order found.', 'error');
            return;
        }

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Verifying…');

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
                    $('#upi-status-badge').text('Successful').css({ 'background': '#dcfce7', 'color': '#166534', 'border-color': '#bbf7d0' });
                    toast(res.message || 'Payment confirmed! Wallet recharged.', 'success');
                    setTimeout(function () {
                        $('#wa-add-amount-modal').hide();
                        $('#upi-pending-section').hide();
                        loadWhatsAppSettings();
                    }, 1000);
                } else if (res.is_failed) {
                    $('#upi-status-badge').text('Failed / Cancelled').css({ 'background': '#fee2e2', 'color': '#991b1b', 'border-color': '#fecaca' });
                    toast(res.message || 'UPI payment was not completed or failed at gateway.', 'error');
                } else {
                    $('#upi-status-badge').text('Pending').css({ 'background': '#fef3c7', 'color': '#92400e', 'border-color': '#fde68a' });
                    toast(res.message || 'Payment is still pending confirmation at gateway.', 'info');
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

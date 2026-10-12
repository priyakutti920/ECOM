@extends('admin.settings.index')

@section('title', 'Plugin Settings')

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
    .settings-card-title i { margin-right: 6px; color: #7b1fa2; }

    .settings-form-group { margin-bottom: 18px; }
    .settings-form-label {
        display: block; font-size: 13px; font-weight: 600; color: #444; margin-bottom: 6px;
    }
    .settings-form-label .hint { font-weight: 400; color: #888; font-size: 12px; }

    .settings-input {
        width: 100%; max-width: 520px; padding: 9px 13px;
        border: 1px solid #ccc; border-radius: 6px; font-size: 14px;
        box-sizing: border-box; display: block; background: #fff;
    }
    .settings-input:focus {
        border-color: #7b1fa2; outline: none;
        box-shadow: 0 0 0 3px rgba(123, 31, 162, 0.12);
    }

    .settings-form-actions {
        display: flex; align-items: center; gap: 12px;
        margin-top: 24px; padding-top: 20px; border-top: 1px solid #eee;
    }

    .btn-save-settings {
        padding: 10px 24px; background: #7b1fa2; color: #fff;
        border: none; border-radius: 6px; font-size: 14px; font-weight: 600;
        cursor: pointer; display: inline-flex; align-items: center; gap: 8px;
        box-shadow: 0 2px 6px rgba(123, 31, 162, 0.25);
        transition: all 0.2s ease;
    }
    .btn-save-settings:hover { background: #6a1b9a; transform: translateY(-1px); }
    .btn-save-settings:disabled { opacity: 0.6; cursor: not-allowed; }

    /* Plugin card */
    .plugin-card {
        border: 1px solid #e2e8f0; border-radius: 8px; padding: 18px 20px;
        margin-bottom: 18px; max-width: 600px; background: #fafafa;
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
    .plugin-toggle input:checked + .slider { background: #4caf50; }
    .plugin-toggle input:checked + .slider:before { transform: translateX(22px); }

    /* Plugin body */
    .plugin-body { display: none; margin-top: 14px; padding-top: 14px; border-top: 1px dashed #cbd5e1; }
    .plugin-body.open { display: block; }
    .plugin-body-row {
        display: flex; gap: 16px; flex-wrap: wrap;
        margin-bottom: 12px;
    }
    .plugin-body-row .settings-form-group { flex: 1; min-width: 220px; margin-bottom: 0; }

    @media (max-width: 768px) {
        .settings-input { max-width: 100%; }
        .plugin-body-row .settings-form-group { min-width: 100%; }
        .plugin-card { max-width: 100%; }
    }
</style>
@endpush

@section('settings_content')
<div id="toast-wrap"></div>

<div class="settings-card">
    <h3 class="settings-card-title">
        <span><i class="fas fa-puzzle-piece"></i> Store Plugins &amp; Integrations</span>
    </h3>

    {{-- Floating WhatsApp Chat Plugin --}}
    <div class="plugin-card">
        <div class="plugin-card-header">
            <div class="plugin-card-info">
                <div class="plugin-card-icon wa">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <div>
                    <div class="plugin-card-name">Floating WhatsApp Chat Button</div>
                    <div class="plugin-card-status" id="wa-status-label">Disabled</div>
                </div>
            </div>
            <label class="plugin-toggle">
                <input type="checkbox" id="wa-enable">
                <span class="slider"></span>
            </label>
        </div>

        <div class="plugin-body" id="wa-body">
            <div class="plugin-body-row">
                <div class="settings-form-group">
                    <label class="settings-form-label">WhatsApp Number <span class="hint">(with country code, e.g. 919876543210)</span></label>
                    <input type="text" id="wa-number" class="settings-input" placeholder="919876543210" maxlength="15">
                </div>
            </div>
            <div class="settings-form-group">
                <label class="settings-form-label">Pre-filled Chat Message</label>
                <input type="text" id="wa-message" class="settings-input" placeholder="Hi! I want to inquire about products..." maxlength="200">
            </div>
        </div>
    </div>

    <div class="settings-form-actions">
        <button type="button" class="btn-save-settings" id="plugin-btn-save">
            <i class="fas fa-save"></i> Save Plugin Settings
        </button>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    // Toggle WhatsApp body
    $(document).on('change', '#wa-enable', function () {
        if ($(this).prop('checked')) {
            $('#wa-body').addClass('open');
            $('#wa-status-label').text('Enabled');
        } else {
            $('#wa-body').removeClass('open');
            $('#wa-status-label').text('Disabled');
        }
    });

    // Load data
    function loadPluginSettings() {
        $.ajax({
            url: '{{ route("admin.settings.plugin-data") }}',
            method: 'GET',
            success: function (res) {
                if (!res.success || !res.data) return;
                var d = res.data;

                var waEnabled = d.wa_enabled == '1';
                $('#wa-enable').prop('checked', waEnabled);
                if (waEnabled) {
                    $('#wa-body').addClass('open');
                    $('#wa-status-label').text('Enabled');
                } else {
                    $('#wa-body').removeClass('open');
                    $('#wa-status-label').text('Disabled');
                }

                $('#wa-number').val(d.wa_number || '');
                $('#wa-message').val(d.wa_message || '');
            }
        });
    }

    loadPluginSettings();

    // Save
    $(document).on('click', '#plugin-btn-save', function () {
        var $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving…');

        $.ajax({
            url: '{{ route("admin.settings.plugin-save") }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                wa_enabled: $('#wa-enable').prop('checked') ? '1' : '0',
                wa_number: $('#wa-number').val().trim(),
                wa_message: $('#wa-message').val().trim(),
            },
            success: function (res) {
                toast(res.message || 'Plugin settings saved successfully.', 'success');
                loadPluginSettings();
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Could not save plugin settings.';
                toast(msg, 'error');
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Plugin Settings');
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
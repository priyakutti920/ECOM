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

    .settings-card { border: 1px solid #e0e0e0; border-radius: 6px; padding: 20px 24px; }
    .settings-card-title {
        font-size: 15px; font-weight: 700; color: #222;
        margin: 0 0 16px; padding-bottom: 12px; border-bottom: 1px solid #eee;
    }
    .settings-card-title i { margin-right: 6px; color: #7b1fa2; }

    .settings-form-group { margin-bottom: 18px; }
    .settings-form-label {
        display: block; font-size: 13px; font-weight: 600; color: #444; margin-bottom: 6px;
    }
    .settings-form-label .hint { font-weight: 400; color: #999; font-size: 12px; }

    .settings-input {
        width: 100%; max-width: 480px; padding: 8px 12px;
        border: 1px solid #ccc; border-radius: 5px; font-size: 14px;
        box-sizing: border-box; display: block;
    }
    .settings-input:focus {
        border-color: #7b1fa2; outline: none;
        box-shadow: 0 0 0 2px rgba(123, 31, 162, 0.12);
    }

    .settings-form-actions {
        display: flex; align-items: center; gap: 10px;
        margin-top: 24px; padding-top: 20px; border-top: 1px solid #eee;
    }

    .btn-save-settings {
        padding: 9px 24px; background: #7b1fa2; color: #fff;
        border: none; border-radius: 5px; font-size: 14px; font-weight: 600;
        cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-save-settings:hover { background: #6a1b9a; }
    .btn-save-settings:disabled { opacity: 0.6; cursor: not-allowed; }

    /* Plugin card */
    .plugin-card {
        border: 1px solid #e0e0e0; border-radius: 6px; padding: 16px 20px;
        margin-bottom: 14px; max-width: 520px;
    }
    .plugin-card-header {
        display: flex; align-items: center; justify-content: space-between;
        gap: 12px; margin-bottom: 12px;
    }
    .plugin-card-info { display: flex; align-items: center; gap: 10px; }
    .plugin-card-icon {
        width: 38px; height: 38px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; flex-shrink: 0;
    }
    .plugin-card-icon.wa { background: #e8f5e9; color: #2e7d32; }
    .plugin-card-name { font-size: 14px; font-weight: 600; color: #222; }
    .plugin-card-status {
        font-size: 12px; color: #888; margin-top: 1px;
    }

    /* Toggle switch */
    .plugin-toggle {
        position: relative; width: 44px; height: 24px; flex-shrink: 0;
    }
    .plugin-toggle input { opacity: 0; width: 0; height: 0; }
    .plugin-toggle .slider {
        position: absolute; cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background: #ccc; border-radius: 24px;
        transition: .2s;
    }
    .plugin-toggle .slider:before {
        position: absolute; content: ""; height: 18px; width: 18px;
        left: 3px; bottom: 3px;
        background: #fff; border-radius: 50%;
        transition: .2s;
    }
    .plugin-toggle input:checked + .slider { background: #4caf50; }
    .plugin-toggle input:checked + .slider:before { transform: translateX(20px); }

    /* Plugin body */
    .plugin-body { display: none; }
    .plugin-body.open { display: block; }
    .plugin-body-row {
        display: flex; gap: 16px; flex-wrap: wrap;
        margin-bottom: 10px;
    }
    .plugin-body-row .settings-form-group { flex: 1; min-width: 200px; margin-bottom: 0; }

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
        <i class="fas fa-puzzle-piece"></i> Plugin Settings
    </h3>

    {{-- WhatsApp Plugin --}}
    <div class="plugin-card">
        <div class="plugin-card-header">
            <div class="plugin-card-info">
                <div class="plugin-card-icon wa">
                    <i class="fab fa-whatsapp"></i>
                </div>
                <div>
                    <div class="plugin-card-name">WhatsApp Plugin</div>
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
                    <label class="settings-form-label">WhatsApp Number <span class="hint">(with country code)</span></label>
                    <input type="text" id="wa-number" class="settings-input" placeholder="919876543210" maxlength="15">
                </div>
            </div>
            <div class="settings-form-group">
                <label class="settings-form-label">Pre-filled Message</label>
                <input type="text" id="wa-message" class="settings-input" placeholder="Hi! I want to place an order..." maxlength="200">
            </div>
        </div>
    </div>

    <div class="settings-form-actions">
        <button type="submit" class="btn-save-settings" id="plugin-btn-save">
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
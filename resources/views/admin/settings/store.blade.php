@extends('admin.settings.index')

@section('title', 'Store Settings')

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
    .settings-form-label .text-danger { color: #c62828; }

    .settings-input {
        width: 100%; max-width: 480px; padding: 8px 12px;
        border: 1px solid #ccc; border-radius: 5px; font-size: 14px;
        box-sizing: border-box; display: block;
    }
    .settings-input:focus {
        border-color: #7b1fa2; outline: none;
        box-shadow: 0 0 0 2px rgba(123, 31, 162, 0.12);
    }

    .settings-row { display: flex; gap: 16px; flex-wrap: wrap; }
    .settings-row .settings-form-group { flex: 1; min-width: 200px; }

    .settings-btn-add {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 6px 12px; background: #f3e5f5; color: #7b1fa2;
        border: 1px solid #ce93d8; border-radius: 4px;
        font-size: 12px; font-weight: 600; cursor: pointer; margin-top: 4px;
    }
    .settings-btn-add:hover { background: #ede7f6; }

    .field-list { display: flex; flex-direction: column; gap: 6px; max-width: 480px; }
    .field-item { display: flex; align-items: center; gap: 6px; }
    .field-item .settings-input { max-width: 100%; }
    .field-item .btn-remove-field {
        flex-shrink: 0; width: 32px; height: 32px;
        border: 1px solid #ef9a9a; background: #ffebee; color: #c62828;
        border-radius: 4px; cursor: pointer;
        display: inline-flex; align-items: center; justify-content: center; font-size: 14px;
    }
    .field-item .btn-remove-field:hover { background: #ffd5d5; }

    .settings-logo-upload { display: flex; align-items: flex-start; gap: 16px; flex-wrap: wrap; }
    .settings-logo-preview {
        width: 80px; height: 80px; border: 2px dashed #ccc; border-radius: 6px;
        display: flex; align-items: center; justify-content: center;
        background: #f8f9fa; color: #ccc; font-size: 28px; overflow: hidden; flex-shrink: 0;
    }
    .settings-logo-preview img { width: 100%; height: 100%; object-fit: contain; }
    .settings-logo-actions { display: flex; flex-direction: column; gap: 6px; }
    .settings-logo-actions label {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 7px 14px; background: #e8f5e9; color: #2e7d32;
        border: 1px solid #a5d6a7; border-radius: 4px;
        font-size: 13px; font-weight: 600; cursor: pointer;
    }
    .settings-logo-actions label:hover { background: #d7f0db; }
    .settings-logo-actions input[type="file"] { display: none; }
    .settings-logo-actions .btn-remove-logo {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 7px 14px; background: #ffebee; color: #c62828;
        border: 1px solid #ef9a9a; border-radius: 4px;
        font-size: 13px; font-weight: 600; cursor: pointer;
    }
    .settings-logo-actions .btn-remove-logo:hover { background: #ffd5d5; }
    .settings-logo-actions .btn-remove-logo.disabled { opacity: 0.4; cursor: not-allowed; }

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

    .settings-checkbox-wrap { display: flex; align-items: center; gap: 8px; max-width: 480px; }
    .settings-checkbox-wrap input[type="checkbox"] { width: 16px; height: 16px; accent-color: #7b1fa2; }
    .settings-checkbox-wrap label { font-size: 13px; color: #555; cursor: pointer; }

    @media (max-width: 768px) {
        .settings-input { max-width: 100%; }
        .settings-row .settings-form-group { min-width: 100%; }
        .field-list { max-width: 100%; }
    }
</style>
@endpush

@section('settings_content')
<div id="toast-wrap"></div>

<div class="settings-card">
    <h3 class="settings-card-title">
        <i class="fas fa-store"></i> Store Settings
    </h3>

    <form id="store-settings-form" enctype="multipart/form-data">
        <div class="settings-row">
            <div class="settings-form-group">
                <label class="settings-form-label">Store Name <span class="text-danger">*</span></label>
                <input type="text" id="ss-name" class="settings-input" placeholder="Enter store name" required>
            </div>
            <div class="settings-form-group">
                <label class="settings-form-label">Store Tagline <span class="hint">optional</span></label>
                <input type="text" id="ss-tagline" class="settings-input" placeholder="Enter store tagline">
            </div>
        </div>

        <div class="settings-form-group">
            <label class="settings-form-label">Store Mobile Numbers</label>
            <div class="field-list" id="ss-phones-list">
                <div class="field-item">
                    <input type="tel" class="settings-input ss-phone" placeholder="+91 9876543210">
                    <button type="button" class="btn-remove-field" title="Remove">&times;</button>
                </div>
            </div>
            <button type="button" class="settings-btn-add" id="ss-add-phone">
                <i class="fas fa-plus"></i> Add Number
            </button>
        </div>

        <div class="settings-form-group">
            <label class="settings-form-label">Store Email Addresses</label>
            <div class="field-list" id="ss-emails-list">
                <div class="field-item">
                    <input type="email" class="settings-input ss-email" placeholder="admin@example.com">
                    <button type="button" class="btn-remove-field" title="Remove">&times;</button>
                </div>
            </div>
            <button type="button" class="settings-btn-add" id="ss-add-email">
                <i class="fas fa-plus"></i> Add Email
            </button>
        </div>

        <div class="settings-form-group">
            <label class="settings-form-label">Store Logo</label>
            <div class="settings-logo-upload">
                <div class="settings-logo-preview" id="ss-logo-preview">
                    <i class="fas fa-image"></i>
                </div>
                <div class="settings-logo-actions">
                    <label>
                        <input type="file" id="ss-logo-input" accept="image/*">
                        <i class="fas fa-upload"></i> Choose Logo
                    </label>
                    <button type="button" id="ss-remove-logo" class="btn-remove-logo disabled">
                        <i class="fas fa-trash"></i> Delete Logo
                    </button>
                </div>
            </div>
        </div>

        <div class="settings-form-group">
            <label class="settings-form-label">Store Favicon</label>
            <div class="settings-logo-upload">
                <div class="settings-logo-preview" id="ss-favicon-preview" style="width:48px; height:48px;">
                    <i class="fas fa-image" style="font-size:18px;"></i>
                </div>
                <div class="settings-logo-actions">
                    <label>
                        <input type="file" id="ss-favicon-input" accept="image/*">
                        <i class="fas fa-upload"></i> Choose Favicon
                    </label>
                    <button type="button" id="ss-remove-favicon" class="btn-remove-logo disabled">
                        <i class="fas fa-trash"></i> Delete Favicon
                    </button>
                </div>
            </div>
        </div>

        {{-- Theme & Appearance Customization --}}
        <div style="margin: 22px 0 18px; padding: 18px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee;">
            <label class="settings-form-label" style="font-size: 14px; font-weight: 700; color: #222; margin-bottom: 14px;">
                <i class="fas fa-palette" style="color: #7b1fa2; margin-right: 6px;"></i> Theme &amp; Appearance Customization
            </label>

            <div class="settings-row">
                <div class="settings-form-group">
                    <label class="settings-form-label">Primary Brand Color <span class="hint">Header, Main Elements</span></label>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <input type="color" id="ss-primary-color-picker" value="#131921" style="width:40px; height:38px; padding:0; border:1px solid #ccc; border-radius:4px; cursor:pointer;" onchange="$('#ss-primary-color').val(this.value)">
                        <input type="text" id="ss-primary-color" class="settings-input" style="max-width:140px; font-family:monospace;" placeholder="#131921" onchange="$('#ss-primary-color-picker').val(this.value)">
                    </div>
                </div>

                <div class="settings-form-group">
                    <label class="settings-form-label">Secondary / Accent Color <span class="hint">Buttons, Badges</span></label>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <input type="color" id="ss-secondary-color-picker" value="#febd69" style="width:40px; height:38px; padding:0; border:1px solid #ccc; border-radius:4px; cursor:pointer;" onchange="$('#ss-secondary-color').val(this.value)">
                        <input type="text" id="ss-secondary-color" class="settings-input" style="max-width:140px; font-family:monospace;" placeholder="#febd69" onchange="$('#ss-secondary-color-picker').val(this.value)">
                    </div>
                </div>

                <div class="settings-form-group">
                    <label class="settings-form-label">Currency Symbol</label>
                    <input type="text" id="ss-currency-symbol" class="settings-input" style="max-width:120px;" placeholder="₹">
                </div>
            </div>
        </div>

        <div class="settings-form-group">
            <label class="settings-form-label">Store Address</label>
            <textarea id="ss-address" class="settings-input" rows="3" placeholder="Enter full store address" style="resize:vertical;"></textarea>
        </div>

        {{-- Store Map Settings (User Side Footer Map) --}}
        <div style="margin: 22px 0 18px; padding: 18px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee;">
            <label class="settings-form-label" style="font-size: 14px; font-weight: 700; color: #222; margin-bottom: 14px;">
                <i class="fas fa-map-marked-alt" style="color: #7b1fa2; margin-right: 6px;"></i> Store Map Settings (User Side Footer Map)
            </label>

            <div class="settings-form-group">
                <div class="settings-checkbox-wrap">
                    <input type="checkbox" id="ss-show-map" checked>
                    <label for="ss-show-map">Display Google Map on user side (footer location)</label>
                </div>
            </div>

            <div class="settings-row">
                <div class="settings-form-group">
                    <label class="settings-form-label">
                        Map Location / Search Landmark <span class="hint">optional</span>
                    </label>
                    <input type="text" id="ss-map-location" class="settings-input" placeholder="e.g. Anna Nagar, Chennai or GPS coordinates">
                    <span class="settings-form-label hint" style="font-size: 11px; margin-top: 4px;">
                        Leave empty to automatically use the Store Address above.
                    </span>
                </div>

                <div class="settings-form-group">
                    <label class="settings-form-label">Map Zoom Level</label>
                    <select id="ss-map-zoom" class="settings-input" style="max-width: 220px;">
                        <option value="10">10 — Regional / District</option>
                        <option value="12">12 — City view</option>
                        <option value="14" selected>14 — Neighborhood (Default)</option>
                        <option value="16">16 — Street level</option>
                        <option value="18">18 — Detailed building</option>
                    </select>
                </div>
            </div>

            <div class="settings-form-group">
                <label class="settings-form-label">
                    Custom Google Maps Embed Code or URL <span class="hint">optional (recommended for exact store pin)</span>
                </label>
                <textarea id="ss-map-iframe" class="settings-input" rows="2" placeholder="Paste full Google Maps <iframe> code or direct embed URL" style="font-family:monospace; font-size:12px; resize:vertical;"></textarea>
                <span class="settings-form-label hint" style="font-size: 11px; margin-top: 4px;">
                    Tip: Search your store on Google Maps &rarr; Click <strong>Share</strong> &rarr; <strong>Embed a map</strong> &rarr; Copy and paste HTML here.
                </span>
            </div>

            <div class="settings-form-group" style="margin-bottom: 0;">
                <label class="settings-form-label">Live Map Preview</label>
                <div style="border-radius: 6px; overflow: hidden; border: 1px solid #ddd; max-width: 480px; height: 140px; background: #fafafa; position: relative;">
                    <iframe id="ss-map-preview-iframe" width="100%" height="140" style="border:0;" loading="lazy" src="about:blank"></iframe>
                    <div id="ss-map-disabled-msg" style="display:none; position:absolute; top:0; left:0; width:100%; height:100%; background:rgba(255,255,255,0.92); align-items:center; justify-content:center; color:#888; font-size:13px; font-weight:500;">
                        <i class="fas fa-eye-slash" style="margin-right:6px;"></i> Map is currently hidden on user side
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-form-group">
            <div class="settings-checkbox-wrap">
                <input type="checkbox" id="ss-order-close">
                <label for="ss-order-close">Close Orders (temporarily stop accepting new orders)</label>
            </div>
        </div>

        <div class="settings-form-actions">
            <button type="submit" class="btn-save-settings" id="ss-btn-save">
                <i class="fas fa-save"></i> Save Settings
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    // Add phone
    $(document).on('click', '#ss-add-phone', function () {
        $('#ss-phones-list').append(
            '<div class="field-item">' +
            '<input type="tel" class="settings-input ss-phone" placeholder="+91 9876543210">' +
            '<button type="button" class="btn-remove-field" title="Remove">&times;</button>' +
            '</div>'
        );
    });

    // Add email
    $(document).on('click', '#ss-add-email', function () {
        $('#ss-emails-list').append(
            '<div class="field-item">' +
            '<input type="email" class="settings-input ss-email" placeholder="admin@example.com">' +
            '<button type="button" class="btn-remove-field" title="Remove">&times;</button>' +
            '</div>'
        );
    });

    // Remove field
    $(document).on('click', '.btn-remove-field', function () {
        var $list = $(this).closest('.field-list');
        if ($list.find('.field-item').length <= 1) return;
        $(this).closest('.field-item').remove();
    });

    // Logo preview
    $(document).on('change', '#ss-logo-input', function () {
        var f = this.files[0];
        if (!f) return;
        var r = new FileReader();
        r.onload = function (e) {
            $('#ss-logo-preview').html('<img src="' + e.target.result + '" alt="Logo">');
            $('#ss-remove-logo').removeClass('disabled');
        };
        r.readAsDataURL(f);
    });

    // Remove logo
    $(document).on('click', '#ss-remove-logo', function () {
        if ($(this).hasClass('disabled')) return;
        $('#ss-logo-input').val('');
        $('#ss-logo-preview').html('<i class="fas fa-image"></i>');
        $(this).addClass('disabled').attr('data-pending-delete', '1');
    });

    // Favicon preview
    $(document).on('change', '#ss-favicon-input', function () {
        var f = this.files[0];
        if (!f) return;
        var r = new FileReader();
        r.onload = function (e) {
            $('#ss-favicon-preview').html('<img src="' + e.target.result + '" alt="Favicon">');
            $('#ss-remove-favicon').removeClass('disabled');
        };
        r.readAsDataURL(f);
    });

    // Remove favicon
    $(document).on('click', '#ss-remove-favicon', function () {
        if ($(this).hasClass('disabled')) return;
        $('#ss-favicon-input').val('');
        $('#ss-favicon-preview').html('<i class="fas fa-image" style="font-size:18px;"></i>');
        $(this).addClass('disabled').attr('data-pending-delete', '1');
    });

    // Load data
    function loadStoreSettings() {
        $.ajax({
            url: '{{ route("admin.settings.store-data") }}',
            method: 'GET',
            success: function (res) {
                if (!res.success || !res.data) return;
                var d = res.data;

                $('#ss-name').val(d.store_name || '');
                $('#ss-tagline').val(d.store_tagline || '');
                $('#ss-address').val(d.address || '');
                $('#ss-order-close').prop('checked', d.order_close == 1);

                // Phones
                var phones = d.phones || [];
                var $phoneList = $('#ss-phones-list').empty();
                if (phones.length) {
                    phones.forEach(function (p) {
                        $phoneList.append('<div class="field-item">' +
                            '<input type="tel" class="settings-input ss-phone" value="' + escHtml(p) + '" placeholder="+91 9876543210">' +
                            '<button type="button" class="btn-remove-field" title="Remove">&times;</button>' +
                            '</div>');
                    });
                } else {
                    $phoneList.append('<div class="field-item">' +
                        '<input type="tel" class="settings-input ss-phone" placeholder="+91 9876543210">' +
                        '<button type="button" class="btn-remove-field" title="Remove">&times;</button>' +
                        '</div>');
                }

                // Emails
                var emails = d.emails || [];
                var $emailList = $('#ss-emails-list').empty();
                if (emails.length) {
                    emails.forEach(function (e) {
                        $emailList.append('<div class="field-item">' +
                            '<input type="email" class="settings-input ss-email" value="' + escHtml(e) + '" placeholder="admin@example.com">' +
                            '<button type="button" class="btn-remove-field" title="Remove">&times;</button>' +
                            '</div>');
                    });
                } else {
                    $emailList.append('<div class="field-item">' +
                        '<input type="email" class="settings-input ss-email" placeholder="admin@example.com">' +
                        '<button type="button" class="btn-remove-field" title="Remove">&times;</button>' +
                        '</div>');
                }

                // Map settings
                $('#ss-show-map').prop('checked', d.show_map != '0');
                $('#ss-map-location').val(d.map_location || '');
                $('#ss-map-zoom').val(d.map_zoom || '14');
                $('#ss-map-iframe').val(d.map_iframe || '');
                renderMapPreview(d.map_embed_url);

                // Theme and Currency
                $('#ss-primary-color').val(d.primary_color || '#131921');
                $('#ss-primary-color-picker').val(d.primary_color || '#131921');
                $('#ss-secondary-color').val(d.secondary_color || '#febd69');
                $('#ss-secondary-color-picker').val(d.secondary_color || '#febd69');
                $('#ss-currency-symbol').val(d.currency_symbol || '₹');

                // Logo
                if (d.logo_url) {
                    $('#ss-logo-preview').html('<img src="' + escHtml(d.logo_url) + '" alt="Logo">');
                    $('#ss-remove-logo').removeClass('disabled').attr('data-existing', '1');
                }

                // Favicon
                if (d.favicon_url) {
                    $('#ss-favicon-preview').html('<img src="' + escHtml(d.favicon_url) + '" alt="Favicon">');
                    $('#ss-remove-favicon').removeClass('disabled').attr('data-existing', '1');
                }
            }
        });
    }

    function renderMapPreview(directUrl) {
        var isEnabled = $('#ss-show-map').is(':checked');
        if (!isEnabled) {
            $('#ss-map-disabled-msg').css('display', 'flex');
        } else {
            $('#ss-map-disabled-msg').hide();
        }

        var customEmbed = $('#ss-map-iframe').val().trim();
        var url = '';
        if (customEmbed) {
            var match = customEmbed.match(/src=[\"\']([^\"\']+)[\"\']/i);
            if (match) {
                url = match[1];
            } else if (customEmbed.indexOf('http') === 0) {
                url = customEmbed;
            }
        }

        if (!url) {
            if (directUrl) {
                url = directUrl;
            } else {
                var loc = $('#ss-map-location').val().trim() || $('#ss-address').val().trim() || 'Tamil Nadu, India';
                var zoom = $('#ss-map-zoom').val() || '14';
                url = 'https://maps.google.com/maps?q=' + encodeURIComponent(loc) + '&t=&z=' + zoom + '&ie=UTF8&iwloc=&output=embed';
            }
        }

        if ($('#ss-map-preview-iframe').attr('src') !== url) {
            $('#ss-map-preview-iframe').attr('src', url);
        }
    }

    $(document).on('input change', '#ss-map-location, #ss-address, #ss-map-zoom, #ss-map-iframe', function () {
        renderMapPreview();
    });

    $(document).on('change', '#ss-show-map', function () {
        renderMapPreview();
    });

    loadStoreSettings();

    // Submit
    $(document).on('submit', '#store-settings-form', function (e) {
        e.preventDefault();
        saveStoreSettings();
    });

    function saveStoreSettings() {
        var fd = new FormData();

        fd.append('store_name', $('#ss-name').val().trim());
        fd.append('store_tagline', $('#ss-tagline').val().trim());
        fd.append('address', $('#ss-address').val().trim());
        fd.append('order_close', $('#ss-order-close').prop('checked') ? '1' : '0');

        // Map settings
        fd.append('show_map', $('#ss-show-map').prop('checked') ? '1' : '0');
        fd.append('map_location', $('#ss-map-location').val().trim());
        fd.append('map_zoom', $('#ss-map-zoom').val());
        fd.append('map_iframe', $('#ss-map-iframe').val().trim());

        var phones = [];
        $('.ss-phone').each(function () { var v = $(this).val().trim(); if (v) phones.push(v); });
        fd.append('phones', JSON.stringify(phones));

        var emails = [];
        $('.ss-email').each(function () { var v = $(this).val().trim(); if (v) emails.push(v); });
        fd.append('emails', JSON.stringify(emails));

        fd.append('primary_color', $('#ss-primary-color').val().trim());
        fd.append('secondary_color', $('#ss-secondary-color').val().trim());
        fd.append('currency_symbol', $('#ss-currency-symbol').val().trim());

        var $logoInput = $('#ss-logo-input')[0];
        var $faviconInput = $('#ss-favicon-input')[0];

        if ($('#ss-remove-logo').attr('data-pending-delete') === '1') {
            fd.append('delete_logo', '1');
        } else if ($logoInput.files[0]) {
            fd.append('logo', $logoInput.files[0]);
        }

        if ($('#ss-remove-favicon').attr('data-pending-delete') === '1') {
            fd.append('delete_favicon', '1');
        } else if ($faviconInput.files[0]) {
            fd.append('favicon', $faviconInput.files[0]);
        }

        $('#ss-btn-save').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving…');

        $.ajax({
            url: '{{ route("admin.settings.store-save") }}',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function (res) {
                toast(res.message || 'Settings saved successfully.');
                loadStoreSettings();
                $('#ss-btn-save').prop('disabled', false).html('<i class="fas fa-save"></i> Save Settings');
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : (xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.store_name ? xhr.responseJSON.errors.store_name[0] : 'Could not save settings.');
                toast(msg, 'error');
                $('#ss-btn-save').prop('disabled', false).html('<i class="fas fa-save"></i> Save Settings');
            }
        });
    }

    function escHtml(str) {
        return $('<span>').text(str || '').html();
    }

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
@extends('admin.settings.index')

@section('title', 'Payment Settings')

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

    .pg-section-label {
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .06em; color: #888; margin: 0 0 10px;
    }

    .pg-gateway-card {
        border: 1px solid #e0e0e0; border-radius: 6px;
        display: flex; align-items: center; gap: 16px;
        padding: 16px 20px; margin-bottom: 0; max-width: 540px;
        background: #fff; transition: box-shadow .15s;
    }
    .pg-gateway-card:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.06); }
    .pg-gateway-card.expanded { border-bottom-left-radius: 0; border-bottom-right-radius: 0; }

    .pg-icon {
        width: 44px; height: 44px; border-radius: 8px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px;
    }
    .pg-icon.manual { background: #e8f5e9; color: #2e7d32; }
    .pg-icon.upi { background: #fff3e0; color: #e65100; }

    .pg-info { flex: 1; min-width: 0; }
    .pg-name { font-size: 14px; font-weight: 600; color: #222; }
    .pg-desc { font-size: 12px; color: #888; margin-top: 2px; }
    .pg-status-badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 2px 8px; border-radius: 3px;
        font-size: 11px; font-weight: 700; margin-top: 5px;
    }
    .pg-status-badge.active { background: #e8f5e9; color: #2e7d32; }
    .pg-status-badge.inactive { background: #f5f5f5; color: #999; }
    .pg-status-badge.locked { background: #fff3e0; color: #e65100; }

    .pg-toggle {
        position: relative; width: 44px; height: 24px; flex-shrink: 0;
    }
    .pg-toggle input { opacity: 0; width: 0; height: 0; }
    .pg-toggle .slider {
        position: absolute; cursor: pointer;
        top: 0; left: 0; right: 0; bottom: 0;
        background: #ccc; border-radius: 24px; transition: .2s;
    }
    .pg-toggle .slider:before {
        position: absolute; content: ""; height: 18px; width: 18px;
        left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: .2s;
    }
    .pg-toggle input:checked + .slider { background: #4caf50; }
    .pg-toggle input:checked + .slider:before { transform: translateX(20px); }
    .pg-toggle.disabled { opacity: 0.5; pointer-events: none; }

    .pg-locked-msg { font-size: 12px; color: #e65100; margin-top: 4px; display: flex; align-items: center; gap: 4px; }
    .pg-note { font-size: 12px; color: #999; margin-top: 16px; line-height: 1.5; }

    /* Config form — appears below the card */
    .pg-config-wrap {
        display: none; max-width: 540px;
    }
    .pg-config-wrap.open { display: block; }
    .pg-config-form {
        margin-bottom: 10px;
        border: 1px solid #e0e0e0; border-top: none;
        border-radius: 0 0 6px 6px;
        padding: 20px;
        background: #fafafa;
    }
    .pg-config-title {
        font-size: 13px; font-weight: 700; color: #555; margin-bottom: 14px;
        display: flex; align-items: center; gap: 6px;
    }
    .pg-config-row { display: flex; gap: 16px; flex-wrap: wrap; }
    .pg-config-row .pg-form-group { flex: 1; min-width: 200px; margin-bottom: 12px; }
    .pg-config-row .pg-form-group.full { min-width: 100%; }
    .pg-form-label { display: block; font-size: 12px; font-weight: 600; color: #444; margin-bottom: 5px; }
    .pg-form-input {
        width: 100%; padding: 7px 10px; border: 1px solid #ccc;
        border-radius: 5px; font-size: 13px; box-sizing: border-box;
    }
    .pg-form-input:focus { border-color: #7b1fa2; outline: none; }
    .pg-form-actions { display: flex; gap: 8px; margin-top: 14px; }

    .pg-save-btn {
        padding: 7px 18px; background: #2e7d32; color: #fff;
        border: none; border-radius: 5px; font-size: 13px; font-weight: 600;
        cursor: pointer; display: inline-flex; align-items: center; gap: 5px;
    }
    .pg-save-btn:hover { background: #1b5e20; }
    .pg-save-btn:disabled { opacity: 0.6; cursor: not-allowed; }

    .pg-cancel-btn {
        padding: 7px 18px; background: #f5f5f5; color: #666;
        border: 1px solid #ccc; border-radius: 5px; font-size: 13px; font-weight: 600;
        cursor: pointer;
    }
    .pg-cancel-btn:hover { background: #eee; }

    /* Summernote overrides */
    .note-editor { border: 1px solid #ccc !important; border-radius: 5px !important; }
    .note-editor.note-focus { border-color: #7b1fa2 !important; box-shadow: 0 0 0 2px rgba(123,31,162,0.12) !important; }
    .pg-form-group .note-editor { margin-top: 4px; }

    @media (max-width: 768px) {
        .pg-gateway-card { max-width: 100%; }
        .pg-config-wrap { max-width: 100%; }
    }
</style>
@endpush

@section('settings_content')
<div id="toast-wrap"></div>

<div class="settings-card">
    <h3 class="settings-card-title">
        <i class="fas fa-credit-card"></i> Payment Settings
    </h3>

    <div class="pg-section-label">Available Gateways</div>

    <div id="pg-list"></div>

    <!-- <p class="pg-note">
        <i class="fas fa-info-circle"></i>
        <strong>Available:</strong> Set <code>is_available=1</code> in database to allow activation of a gateway.<br>
        <strong>Active:</strong> Set <code>is_active=1</code> to enable the gateway for use on the store.
    </p> -->
</div>
@endsection

@push('scripts')
<script>
$(function () {
    var $list = $('#pg-list');
    var gatewayDescriptions = {};
    var gatewayShortDescriptions = {
        'manual': 'Accept payments manually via bank transfer, custom QR codes, or cash on delivery.',
        'upi': 'Accept payments via UPI apps like Google Pay, PhonePe, Paytm.'
    };

    function iconHtml(icon) {
        return '<i class="' + (icon || 'fa-credit-card') + '"></i>';
    }

    function iconClass(slug) {
        if (slug === 'manual') return 'manual';
        if (slug === 'upi') return 'upi';
        return '';
    }

    function iconBgClass(slug) {
        if (slug === 'manual') return 'fa-money-bill';
        if (slug === 'upi') return 'fa-mobile-alt';
        return 'fa-credit-card';
    }

    function loadGateways() {
        $.ajax({
            url: '{{ route("admin.settings.payment-data") }}',
            method: 'GET',
            success: function (res) {
                $list.empty();
                if (!res.data || !res.data.length) return;

                res.data.forEach(function (g) {
                    var available = parseInt(g.is_available);
                    var active = parseInt(g.is_active);

                    gatewayDescriptions[g.slug] = g.description || '';

                    var statusBadge = '';
                    var toggleDisabled = '';
                    var configForm = '';

                    if (!available) {
                        statusBadge = '<span class="pg-status-badge locked"><i class="fas fa-lock"></i> Not Available</span>';
                        toggleDisabled = 'disabled';
                    } else if (active) {
                        statusBadge = '<span class="pg-status-badge active"><i class="fas fa-check-circle"></i> Active</span>';
                    } else {
                        statusBadge = '<span class="pg-status-badge inactive"><i class="fas fa-ban"></i> Inactive</span>';
                    }

                    if (g.slug === 'manual' && available) {
                        configForm = '<div class="pg-config-wrap' + (active ? ' open' : '') + '" id="pg-wrap-' + escHtml(g.slug) + '"></div>';
                    }
                    if (g.slug === 'upi' && available) {
                        configForm = '<div class="pg-config-wrap' + (active ? ' open' : '') + '" id="pg-wrap-' + escHtml(g.slug) + '"></div>';
                    }

                    var card = $('<div class="pg-gateway-card' + (active && (g.slug === 'manual' || g.slug === 'upi') ? ' expanded' : '') + '" data-slug="' + escHtml(g.slug) + '"></div>');

                    card.append(
                        '<div class="pg-icon ' + iconClass(g.slug) + '">' +
                        '<i class="fas ' + iconBgClass(g.slug) + '"></i>' +
                        '</div>'
                    );

                    var shortDesc = gatewayShortDescriptions[g.slug] || g.description || '';
                    var tempDiv = document.createElement("div");
                    tempDiv.innerHTML = shortDesc;
                    var cleanDesc = tempDiv.textContent || tempDiv.innerText || "";
                    if (cleanDesc.length > 120) {
                        cleanDesc = cleanDesc.substring(0, 120) + '...';
                    }

                    card.append(
                        '<div class="pg-info">' +
                        '<div class="pg-name">' + escHtml(g.name) + '</div>' +
                        '<div class="pg-desc">' + escHtml(cleanDesc) + '</div>' +
                        statusBadge +
                        (!available ? '<div class="pg-locked-msg"><i class="fas fa-info-circle"></i> This gateway is not available for activation.</div>' : '') +
                        '</div>'
                    );

                    card.append(
                        '<label class="pg-toggle ' + (!available ? 'disabled' : '') + '">' +
                        '<input type="checkbox" data-slug="' + escHtml(g.slug) + '" ' + (active ? 'checked' : '') + ' ' + toggleDisabled + '>' +
                        '<span class="slider"></span>' +
                        '</label>'
                    );

                    $list.append(card);

                    // Append config form directly after the card, not at end of list
                    if (configForm) {
                        card.after(configForm);
                        if (active) {
                            renderConfigForm(g.slug, g);
                        }
                    }
                });
            }
        });
    }

    loadGateways();

    var toggleChangeHandler = function () {
        var slug = $(this).data('slug');
        var $card = $(this).closest('.pg-gateway-card');
        var $toggle = $(this).closest('.pg-toggle');
        var isChecked = $(this).prop('checked');

        // Disable toggle while processing
        $toggle.addClass('disabled');

        $.ajax({
            url: '{{ route("admin.settings.payment-toggle") }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                slug: slug,
            },
            success: function (res) {
                toast(res.message || 'Gateway updated.', 'success');

                // Update badge
                var badge = res.is_active
                    ? '<span class="pg-status-badge active"><i class="fas fa-check-circle"></i> Active</span>'
                    : '<span class="pg-status-badge inactive"><i class="fas fa-ban"></i> Inactive</span>';

                $card.find('.pg-status-badge').replaceWith(badge);
                $card.find('.pg-toggle input').prop('checked', res.is_active);

                // Show/hide config form for manual
                if (slug === 'manual' || slug === 'upi') {
                    if (res.is_active) {
                        $card.addClass('expanded');
                        // Create wrapper if it doesn't exist (was previously removed)
                        if (!$('#pg-wrap-' + slug).length) {
                            $card.after('<div class="pg-config-wrap open" id="pg-wrap-' + escHtml(slug) + '"></div>');
                        }
                        renderConfigForm(slug, res.gateway || {});
                    } else {
                        $card.removeClass('expanded');
                        $('#pg-wrap-' + slug).remove();
                    }
                }
            },
            error: function (xhr) {
                toast(xhr.responseJSON?.message || 'Failed to update gateway.', 'error');
                loadGateways(); // reload on error
            },
            complete: function () {
                $toggle.removeClass('disabled');
            }
        });
    };

    $(document).on('change', '.pg-toggle input', toggleChangeHandler);

    function renderConfigForm(slug, gateway) {
        var $wrap = $('#pg-wrap-' + slug);
        if (!$wrap.length) return;

        var content = '';

        // UPI gateway — show credentials fields
        if (slug === 'upi') {
            var creds = gateway.credentials || {};
            content = '<div class="pg-config-form">' +
                '<div class="pg-config-title"><i class="fas fa-cog"></i> Configure ' + escHtml(gateway.name || 'UPI') + '</div>' +
                '<div class="pg-config-row">' +
                '<div class="pg-form-group">' +
                '<label class="pg-form-label">Method Name</label>' +
                '<input type="text" class="pg-form-input" id="pg-mname-' + escHtml(slug) + '" placeholder="e.g. UPI Payment" value="' + escHtml(gateway.method_name || '') + '">' +
                '</div>' +
                '</div>' +
                '<div class="pg-config-row">' +
                '<div class="pg-form-group full">' +
                '<label class="pg-form-label">Create Order URL <span style="color:#c0392b;">*</span></label>' +
                '<input type="url" class="pg-form-input" id="pg-upi-create-url" placeholder="https://upicheckout.online/api/create-order" value="' + escHtml(creds.create_order_url || '') + '">' +
                '</div>' +
                '</div>' +
                '<div class="pg-config-row">' +
                '<div class="pg-form-group full">' +
                '<label class="pg-form-label">Status Check URL <span style="color:#c0392b;">*</span></label>' +
                '<input type="url" class="pg-form-input" id="pg-upi-status-url" placeholder="https://upicheckout.online/api/check-order-status" value="' + escHtml(creds.status_check_url || '') + '">' +
                '</div>' +
                '</div>' +
                '<div class="pg-config-row">' +
                '<div class="pg-form-group full">' +
                '<label class="pg-form-label">API Key / User Token <span style="color:#c0392b;">*</span></label>' +
                '<input type="text" class="pg-form-input" id="pg-upi-api-key" placeholder="Enter your API user token" value="' + escHtml(creds.api_key || '') + '">' +
                '</div>' +
                '</div>' +
                '<div class="pg-config-row">' +
                '<div class="pg-form-group full">' +
                '<label class="pg-form-label">Redirect URL (after payment)</label>' +
                '<input type="url" class="pg-form-input" id="pg-upi-redirect-url" placeholder="https://yourstore.com/payment/callback" value="' + escHtml(creds.redirect_url || '') + '">' +
                '</div>' +
                '</div>' +
                '<div class="pg-form-actions">' +
                '<button type="button" class="pg-save-btn" data-slug="' + escHtml(slug) + '">' +
                '<i class="fas fa-save"></i> Save' +
                '</button>' +
                '<button type="button" class="pg-cancel-btn" data-slug="' + escHtml(slug) + '">Cancel</button>' +
                '</div>' +
                '</div>';
        } else {
            // Manual gateway — method name + rich description
            content = '<div class="pg-config-form">' +
                '<div class="pg-config-title"><i class="fas fa-cog"></i> Configure ' + escHtml(gateway.name || slug) + '</div>' +
                '<div class="pg-config-row">' +
                '<div class="pg-form-group">' +
                '<label class="pg-form-label">Method Name</label>' +
                '<input type="text" class="pg-form-input" id="pg-mname-' + escHtml(slug) + '" placeholder="e.g. Cash on Delivery" value="' + escHtml(gateway.method_name || '') + '">' +
                '</div>' +
                '</div>' +
                '<div class="pg-config-row">' +
                '<div class="pg-form-group full">' +
                '<label class="pg-form-label">Description</label>' +
                '<textarea class="pg-form-input summernote-editor" id="pg-desc-' + escHtml(slug) + '" rows="4" placeholder="Enter payment instructions for customers..."></textarea>' +
                '</div>' +
                '</div>' +
                '<div class="pg-form-actions">' +
                '<button type="button" class="pg-save-btn" data-slug="' + escHtml(slug) + '">' +
                '<i class="fas fa-save"></i> Save' +
                '</button>' +
                '<button type="button" class="pg-cancel-btn" data-slug="' + escHtml(slug) + '">Cancel</button>' +
                '</div>' +
                '</div>';
        }

        $wrap.html(content).addClass('open');

        // Init Summernote only for manual (description field)
        if (slug === 'manual') {
            var $ed = $('#pg-desc-' + slug);
            if (!$ed.data('summernote')) {
                $ed.summernote({
                    height: 180,
                    minHeight: 150,
                    maxHeight: 300,
                    focus: false,
                    placeholder: 'Enter payment instructions for customers...',
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
                        ['fontname', ['fontname']],
                        ['fontsize', ['fontsize']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['insert', ['link', 'picture', 'video', 'hr']],
                        ['view', ['fullscreen', 'codeview', 'help']],
                        ['misc', ['undo', 'redo']]
                    ],
                    styleTags: ['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'pre', 'blockquote'],
                });
            }
            var stored = gateway.description || gatewayDescriptions[slug] || '';
            if (stored) { $ed.summernote('code', stored); }
        }
    }

    // Save config
    $(document).on('click', '.pg-save-btn', function () {
        var slug = $(this).data('slug');
        var $btn = $(this);

        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving…');

        var ajaxData = {
            _token: $('meta[name="csrf-token"]').attr('content'),
            slug: slug,
            method_name: $('#pg-mname-' + slug).val() || '',
        };

        // UPI credentials
        if (slug === 'upi') {
            ajaxData.credentials = {
                create_order_url: $('#pg-upi-create-url').val().trim(),
                status_check_url: $('#pg-upi-status-url').val().trim(),
                api_key: $('#pg-upi-api-key').val().trim(),
                redirect_url: $('#pg-upi-redirect-url').val().trim(),
            };
            ajaxData.description = '';
        } else {
            ajaxData.description = $('#pg-desc-' + slug).summernote('code');
        }

        $.ajax({
            url: '{{ route("admin.settings.payment-save") }}',
            method: 'POST',
            data: ajaxData,
            success: function (res) {
                toast(res.message || 'Settings saved.', 'success');
                loadGateways();
            },
            error: function (xhr) {
                toast(xhr.responseJSON?.message || 'Failed to save settings.', 'error');
            },
            complete: function () {
                $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save');
            }
        });
    });

    // Cancel config
    $(document).on('click', '.pg-cancel-btn', function () {
        var slug = $(this).data('slug');
        var $card = $('[data-slug="' + slug + '"]');

        // Remove config form
        $('#pg-wrap-' + slug).remove();
        $card.removeClass('expanded');

        // Revert toggle WITHOUT triggering change event
        var $toggleInput = $card.find('.pg-toggle input');
        $toggleInput.off('change'); // temporarily remove change handler
        $toggleInput.prop('checked', false);
        $toggleInput.on('change', toggleChangeHandler);

        // Send toggle API directly without change event
        $.ajax({
            url: '{{ route("admin.settings.payment-toggle") }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                slug: slug,
            },
            success: function () {
                var badge = '<span class="pg-status-badge inactive"><i class="fas fa-ban"></i> Inactive</span>';
                $card.find('.pg-status-badge').replaceWith(badge);
            }
        });
    });

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
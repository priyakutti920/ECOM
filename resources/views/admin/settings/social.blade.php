@extends('admin.settings.index')

@section('title', 'Social Links Settings')

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

    .settings-card { border: 1px solid #e0e0e0; border-radius: 6px; padding: 20px 24px; background: #fff; margin-bottom: 20px; }
    .settings-card-title {
        font-size: 15px; font-weight: 700; color: #222;
        margin: 0 0 16px; padding-bottom: 12px; border-bottom: 1px solid #eee;
        display: flex; align-items: center; justify-content: space-between;
    }
    .settings-card-title i { margin-right: 6px; color: #7b1fa2; }

    .social-row-item {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 14px;
        border: 1px solid #eee;
        border-radius: 6px;
        margin-bottom: 10px;
        background: #fafafa;
        transition: all 0.2s ease;
    }
    .social-row-item:hover {
        background: #fff;
        border-color: #d1c4e9;
        box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    }

    .social-badge {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 18px;
        flex-shrink: 0;
    }

    .social-platform-info {
        width: 140px;
        flex-shrink: 0;
    }
    .social-platform-name {
        font-weight: 600;
        font-size: 13.5px;
        color: #222;
    }
    .social-platform-sub {
        font-size: 11px;
        color: #888;
    }

    .social-input-wrap {
        flex: 1;
        position: relative;
    }
    .social-input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 13px;
        box-sizing: border-box;
    }
    .social-input:focus {
        border-color: #7b1fa2;
        outline: none;
        box-shadow: 0 0 0 2px rgba(123, 31, 162, 0.12);
    }

    .social-toggle-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
        font-size: 12px;
        font-weight: 500;
        color: #555;
    }

    .btn-test-link {
        padding: 6px 12px;
        background: #f5f5f5;
        border: 1px solid #ccc;
        border-radius: 4px;
        font-size: 12px;
        color: #555;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        flex-shrink: 0;
        cursor: pointer;
    }
    .btn-test-link:hover {
        background: #ede7f6;
        color: #7b1fa2;
        border-color: #ce93d8;
    }

    .custom-link-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        background: #fdfdfd;
        border: 1px solid #e0e0e0;
        border-radius: 5px;
        margin-bottom: 8px;
        flex-wrap: wrap;
    }

    .btn-remove-custom {
        width: 32px;
        height: 32px;
        border: 1px solid #ef9a9a;
        background: #ffebee;
        color: #c62828;
        border-radius: 4px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        flex-shrink: 0;
    }
    .btn-remove-custom:hover { background: #ffd5d5; }

    .btn-add-custom {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        background: #f3e5f5;
        color: #7b1fa2;
        border: 1px solid #ce93d8;
        border-radius: 4px;
        font-size: 12.5px;
        font-weight: 600;
        cursor: pointer;
        margin-top: 6px;
    }
    .btn-add-custom:hover { background: #ede7f6; }

    .preview-footer-social {
        padding: 16px;
        background: #232F3E;
        border-radius: 6px;
        color: #fff;
    }
    .preview-footer-social h5 {
        font-size: 13px;
        margin: 0 0 10px;
        font-weight: 600;
        letter-spacing: 0.5px;
        color: #ddd;
    }
    .preview-icon-list {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }
    .preview-icon-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 20px;
        font-size: 12.5px;
        color: #fff;
        text-decoration: none;
    }
    .preview-icon-chip:hover {
        background: rgba(255,255,255,0.22);
        color: #FEB64D;
    }

    .btn-save-settings {
        padding: 9px 26px;
        background: #7b1fa2;
        color: #fff;
        border: none;
        border-radius: 5px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-save-settings:hover { background: #6a1b9a; }
    .btn-save-settings:disabled { opacity: 0.6; cursor: not-allowed; }

    @media (max-width: 600px) {
        .social-row-item { flex-direction: column; align-items: flex-start; gap: 8px; }
        .social-platform-info { width: 100%; }
        .social-input-wrap { width: 100%; }
    }
</style>
@endpush

@section('settings_content')
<div id="toast-wrap"></div>

<form id="social-settings-form">
    @csrf

    {{-- 1. General Preferences --}}
    <div class="settings-card">
        <h4 class="settings-card-title">
            <span><i class="fas fa-sliders-h"></i> General Settings</span>
        </h4>
        <div style="display:flex; flex-direction:column; gap:12px;">
            <label style="display:flex; align-items:center; gap:8px; font-size:13.5px; cursor:pointer; font-weight:500; color:#333;">
                <input type="checkbox" id="social-open-new-tab" name="open_new_tab" value="1" style="width:16px; height:16px; accent-color:#7b1fa2;">
                <span>Open social links in a new browser tab (<code>target="_blank"</code>)</span>
            </label>
            <label style="display:flex; align-items:center; gap:8px; font-size:13.5px; cursor:pointer; font-weight:500; color:#333;">
                <input type="checkbox" id="social-show-in-footer" name="show_in_footer" value="1" checked style="width:16px; height:16px; accent-color:#7b1fa2;">
                <span>Display "Connect with Us" social links section in the store footer</span>
            </label>
        </div>
    </div>

    {{-- 2. Standard Social Media Platforms --}}
    <div class="settings-card">
        <h4 class="settings-card-title">
            <span><i class="fas fa-share-alt"></i> Official Channels</span>
            <span style="font-size:12px; font-weight:normal; color:#888;">Enable platforms and paste their full channel URLs</span>
        </h4>

        <div id="standard-social-list">
            {{-- Rendered dynamically by JS --}}
        </div>
    </div>

    {{-- 3. Custom Social Links --}}
    <div class="settings-card">
        <h4 class="settings-card-title">
            <span><i class="fas fa-link"></i> Additional / Custom Channels</span>
            <button type="button" class="btn-add-custom" id="btn-add-custom">
                <i class="fas fa-plus"></i> Add Channel
            </button>
        </h4>

        <p style="font-size:12px; color:#666; margin-top:-8px; margin-bottom:14px;">
            Add links to custom networks, forums, blogs, or messenger links (e.g. Discord, Threads, Snapchat).
        </p>

        <div id="custom-social-list">
            {{-- Custom items rendered by JS --}}
        </div>
    </div>

    {{-- 4. Live Store Preview --}}
    <div class="settings-card">
        <h4 class="settings-card-title">
            <span><i class="fas fa-eye"></i> Store Footer Preview</span>
            <span style="font-size:12px; font-weight:normal; color:#888;">Live preview of active links</span>
        </h4>
        <div class="preview-footer-social">
            <h5>CONNECT WITH US</h5>
            <div class="preview-icon-list" id="preview-list">
                <span style="color:#aaa; font-size:12px;">No active social links entered yet.</span>
            </div>
        </div>
    </div>

    {{-- Form Actions --}}
    <div style="margin-top:20px; display:flex; align-items:center; gap:12px;">
        <button type="submit" class="btn-save-settings" id="social-btn-save">
            <i class="fas fa-save"></i> Save Social Links
        </button>
        <span id="save-indicator" style="font-size:13px; color:#2e7d32; display:none;">
            <i class="fas fa-check-circle"></i> Saved!
        </span>
    </div>
</form>
@endsection

@push('scripts')
<script>
$(function () {
    var socialData = {};
    var customLinks = [];

    var defaultPlatformMeta = {
        'facebook':  { name: 'Facebook',     icon: 'fab fa-facebook-f',    color: '#1877f2', placeholder: 'https://facebook.com/yourbrand' },
        'instagram': { name: 'Instagram',    icon: 'fab fa-instagram',     color: '#e4405f', placeholder: 'https://instagram.com/yourbrand' },
        'twitter':   { name: 'X (Twitter)',  icon: 'fab fa-x-twitter',     color: '#111111', placeholder: 'https://x.com/yourbrand' },
        'youtube':   { name: 'YouTube',      icon: 'fab fa-youtube',       color: '#ff0000', placeholder: 'https://youtube.com/@yourchannel' },
        'whatsapp':  { name: 'WhatsApp',     icon: 'fab fa-whatsapp',      color: '#25d366', placeholder: 'https://wa.me/919876543210' },
        'linkedin':  { name: 'LinkedIn',     icon: 'fab fa-linkedin-in',   color: '#0a66c2', placeholder: 'https://linkedin.com/company/yourbrand' },
        'pinterest': { name: 'Pinterest',    icon: 'fab fa-pinterest-p',   color: '#bd081c', placeholder: 'https://pinterest.com/yourbrand' },
        'telegram':  { name: 'Telegram',     icon: 'fab fa-telegram-plane',color: '#229ed9', placeholder: 'https://t.me/yourchannel' }
    };

    function loadSocialData() {
        $.ajax({
            url: '{{ route("admin.settings.social-data") }}',
            method: 'GET',
            success: function (res) {
                if (!res.success || !res.data) return;
                var d = res.data;

                $('#social-open-new-tab').prop('checked', d.open_new_tab);
                $('#social-show-in-footer').prop('checked', d.show_in_footer);

                socialData = d.links || {};
                customLinks = Array.isArray(d.custom_links) ? d.custom_links : [];

                renderStandardPlatforms();
                renderCustomLinks();
                updateLivePreview();
            },
            error: function () {
                toast('Failed to load social settings.', 'error');
            }
        });
    }

    function renderStandardPlatforms() {
        var $list = $('#standard-social-list').empty();

        $.each(defaultPlatformMeta, function (key, meta) {
            var item = socialData[key] || {};
            var url = item.url || '';
            var isActive = item.active !== false;

            var $row = $(
                '<div class="social-row-item" data-platform="' + key + '">' +
                    '<div class="social-badge" style="background:' + meta.color + ';">' +
                        '<i class="' + meta.icon + '"></i>' +
                    '</div>' +
                    '<div class="social-platform-info">' +
                        '<div class="social-platform-name">' + meta.name + '</div>' +
                        '<div class="social-platform-sub">' + key + '</div>' +
                    '</div>' +
                    '<div class="social-input-wrap">' +
                        '<input type="url" class="social-input platform-url-input" ' +
                               'placeholder="' + meta.placeholder + '" ' +
                               'value="' + escHtml(url) + '">' +
                    '</div>' +
                    '<div class="social-toggle-wrap">' +
                        '<label style="display:flex; align-items:center; gap:5px; cursor:pointer;">' +
                            '<input type="checkbox" class="platform-active-check" ' + (isActive ? 'checked' : '') + ' style="accent-color:#7b1fa2;">' +
                            'Active' +
                        '</label>' +
                    '</div>' +
                    '<a href="' + (url || '#') + '" target="_blank" rel="noopener" class="btn-test-link ' + (url ? '' : 'disabled') + '" title="Test link">' +
                        '<i class="fas fa-external-link-alt"></i> Test' +
                    '</a>' +
                '</div>'
            );

            $list.append($row);
        });
    }

    function renderCustomLinks() {
        var $list = $('#custom-social-list').empty();

        if (customLinks.length === 0) {
            $list.html('<p style="font-size:12.5px; color:#888; margin:6px 0;">No custom links added yet. Click "+ Add Channel" to add one.</p>');
            return;
        }

        customLinks.forEach(function (item, idx) {
            var $item = $(
                '<div class="custom-link-item" data-index="' + idx + '">' +
                    '<div style="flex:1; min-width:130px;">' +
                        '<input type="text" class="social-input custom-name-input" placeholder="Channel Name (e.g. Discord)" value="' + escHtml(item.name || '') + '">' +
                    '</div>' +
                    '<div style="flex:2; min-width:200px;">' +
                        '<input type="url" class="social-input custom-url-input" placeholder="URL (https://...)" value="' + escHtml(item.url || '') + '">' +
                    '</div>' +
                    '<div style="width:140px;">' +
                        '<input type="text" class="social-input custom-icon-input" placeholder="Icon (fa-link)" value="' + escHtml(item.icon || 'fas fa-link') + '">' +
                    '</div>' +
                    '<label style="display:flex; align-items:center; gap:5px; font-size:12px; cursor:pointer;">' +
                        '<input type="checkbox" class="custom-active-check" ' + (item.active ? 'checked' : '') + ' style="accent-color:#7b1fa2;"> Active' +
                    '</label>' +
                    '<button type="button" class="btn-remove-custom" title="Remove channel">&times;</button>' +
                '</div>'
            );

            $list.append($item);
        });
    }

    function updateLivePreview() {
        var $prev = $('#preview-list').empty();
        var count = 0;

        // Standard
        $('.social-row-item').each(function () {
            var platform = $(this).data('platform');
            var url = $(this).find('.platform-url-input').val().trim();
            var active = $(this).find('.platform-active-check').is(':checked');
            var meta = defaultPlatformMeta[platform];

            if (url && active && meta) {
                count++;
                $prev.append(
                    '<a href="' + escHtml(url) + '" target="_blank" class="preview-icon-chip">' +
                        '<i class="' + meta.icon + '"></i> ' + meta.name +
                    '</a>'
                );
            }
        });

        // Custom
        $('.custom-link-item').each(function () {
            var name = $(this).find('.custom-name-input').val().trim();
            var url = $(this).find('.custom-url-input').val().trim();
            var icon = $(this).find('.custom-icon-input').val().trim() || 'fas fa-link';
            var active = $(this).find('.custom-active-check').is(':checked');

            if (name && url && active) {
                count++;
                $prev.append(
                    '<a href="' + escHtml(url) + '" target="_blank" class="preview-icon-chip">' +
                        '<i class="' + escHtml(icon) + '"></i> ' + escHtml(name) +
                    '</a>'
                );
            }
        });

        if (count === 0) {
            $prev.html('<span style="color:#aaa; font-size:12px;">No active social links entered yet. Fill in a URL above to see it appear.</span>');
        }
    }

    // Dynamic test link update on blur / input
    $(document).on('input', '.platform-url-input', function () {
        var val = $(this).val().trim();
        var $testBtn = $(this).closest('.social-row-item').find('.btn-test-link');
        if (val) {
            $testBtn.attr('href', val).removeClass('disabled');
        } else {
            $testBtn.attr('href', '#').addClass('disabled');
        }
        updateLivePreview();
    });

    $(document).on('change', '.platform-active-check, .custom-active-check', function () {
        updateLivePreview();
    });

    $(document).on('input', '.custom-name-input, .custom-url-input, .custom-icon-input', function () {
        updateLivePreview();
    });

    // Add custom channel
    $('#btn-add-custom').on('click', function () {
        customLinks.push({ name: '', url: '', icon: 'fas fa-link', active: true });
        renderCustomLinks();
    });

    // Remove custom channel
    $(document).on('click', '.btn-remove-custom', function () {
        var idx = $(this).closest('.custom-link-item').data('index');
        customLinks.splice(idx, 1);
        renderCustomLinks();
        updateLivePreview();
    });

    // Form submit
    $('#social-settings-form').on('submit', function (e) {
        e.preventDefault();

        var $btn = $('#social-btn-save');
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving…');

        // Harvest standard platforms
        var linksPayload = {};
        $('.social-row-item').each(function () {
            var platform = $(this).data('platform');
            var url = $(this).find('.platform-url-input').val().trim();
            var active = $(this).find('.platform-active-check').is(':checked') ? 1 : 0;
            linksPayload[platform] = {
                url: url,
                active: active
            };
        });

        // Harvest custom channels
        var customPayload = [];
        $('.custom-link-item').each(function () {
            var name = $(this).find('.custom-name-input').val().trim();
            var url = $(this).find('.custom-url-input').val().trim();
            var icon = $(this).find('.custom-icon-input').val().trim() || 'fas fa-link';
            var active = $(this).find('.custom-active-check').is(':checked') ? 1 : 0;
            if (name && url) {
                customPayload.push({
                    name: name,
                    url: url,
                    icon: icon,
                    active: active
                });
            }
        });

        $.ajax({
            url: '{{ route("admin.settings.social-save") }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                open_new_tab: $('#social-open-new-tab').is(':checked') ? 1 : 0,
                show_in_footer: $('#social-show-in-footer').is(':checked') ? 1 : 0,
                links: linksPayload,
                custom_links: customPayload
            },
            success: function (res) {
                toast(res.message || 'Social links saved successfully.');
                $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Social Links');
                loadSocialData();
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Could not save social settings.';
                toast(msg, 'error');
                $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Social Links');
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

    // Initialize
    loadSocialData();
});
</script>
@endpush

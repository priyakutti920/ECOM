@extends('admin.settings.index')

@section('title', 'SEO Settings')

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

    .settings-row { display: flex; gap: 16px; flex-wrap: wrap; }
    .settings-row .settings-form-group { flex: 1; min-width: 200px; }

    .settings-hint { display: block; font-size: 11px; color: #999; margin-top: 4px; }

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

    /* Keywords */
    .seo-keyword-wrap { max-width: 480px; }
    .seo-keyword-tags { display: flex; flex-wrap: wrap; gap: 6px; min-height: 28px; }
    .seo-keyword-tag {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 8px; background: #ede7f6; color: #7b1fa2;
        border-radius: 4px; font-size: 12px; font-weight: 500;
    }
    .seo-keyword-tag .kw-remove { cursor: pointer; font-size: 14px; line-height: 1; color: #9575cd; }
    .seo-keyword-tag .kw-remove:hover { color: #c62828; }

    /* Preview */
    .seo-preview-box {
        border: 1px solid #e0e0e0; border-radius: 6px; padding: 16px;
        background: #fafafa; max-width: 600px;
    }
    .seo-preview-label {
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .05em; color: #707070; margin-bottom: 8px;
    }
    .seo-preview-title { font-size: 18px; color: #1a0dab; cursor: pointer; margin-bottom: 2px; line-height: 1.3; }
    .seo-preview-title:hover { text-decoration: underline; }
    .seo-preview-url { font-size: 13px; color: #006621; margin-bottom: 4px; }
    .seo-preview-desc { font-size: 13px; color: #545454; line-height: 1.4; }

    /* Auto generation */
    .seo-gen-box { max-width: 520px; border: 1px solid #e0e0e0; border-radius: 6px; overflow: hidden; }
    .seo-gen-item {
        display: flex; align-items: center; justify-content: space-between;
        padding: 14px 16px; gap: 12px;
    }
    .seo-gen-item:not(:last-child) { border-bottom: 1px solid #eee; }
    .seo-gen-info { display: flex; flex-direction: column; gap: 2px; }
    .seo-gen-label {
        font-size: 13px; font-weight: 600; color: #333;
        display: flex; align-items: center; gap: 5px;
    }
    .seo-gen-label i { color: #7b1fa2; }
    .seo-gen-desc { font-size: 12px; color: #888; }
    .seo-gen-link { font-size: 12px; color: #7b1fa2; text-decoration: none; font-weight: 500; }
    .seo-gen-link:hover { text-decoration: underline; }
    .seo-gen-btn {
        flex-shrink: 0; padding: 7px 16px; background: #f3e5f5; color: #7b1fa2;
        border: 1px solid #ce93d8; border-radius: 4px;
        font-size: 12px; font-weight: 600; cursor: pointer;
        display: inline-flex; align-items: center; gap: 5px;
    }
    .seo-gen-btn:hover { background: #ede7f6; }
    .seo-gen-btn.spinning i { animation: seo-spin 0.6s linear infinite; }
    @keyframes seo-spin { to { transform: rotate(360deg); } }

    @media (max-width: 768px) {
        .settings-input { max-width: 100%; }
        .settings-row .settings-form-group { min-width: 100%; }
    }
</style>
@endpush

@section('settings_content')
<div id="toast-wrap"></div>

<div class="settings-card">
    <h3 class="settings-card-title">
        <i class="fas fa-search"></i> SEO Settings
    </h3>

    <form id="seo-settings-form">
        <div class="settings-row">
            <div class="settings-form-group">
                <label class="settings-form-label">Meta Title</label>
                <input type="text" id="seo-meta-title" class="settings-input" placeholder="e.g. My Store - Best Products Online" maxlength="70">
                <span class="settings-hint">Recommended: 50–60 characters</span>
            </div>
            <div class="settings-form-group">
                <label class="settings-form-label">Canonical URL</label>
                <input type="url" id="seo-canonical-url" class="settings-input" placeholder="https://www.mystore.com">
            </div>
        </div>

        <div class="settings-form-group">
            <label class="settings-form-label">Meta Description</label>
            <textarea id="seo-meta-desc" class="settings-input" rows="3" placeholder="Write a compelling description for search engines..." maxlength="160" style="resize:vertical;"></textarea>
            <span class="settings-hint">Recommended: 120–160 characters</span>
        </div>

        <div class="settings-form-group">
            <label class="settings-form-label">Meta Keywords</label>
            <div class="seo-keyword-wrap">
                <div id="seo-keywords-list" class="seo-keyword-tags"></div>
                <input type="text" id="seo-keyword-input" class="settings-input" placeholder="Type keyword &amp; press Enter" style="margin-top:6px;">
            </div>
            <span class="settings-hint">Press Enter or comma to add. Click &times; to remove.</span>
        </div>

        <div class="settings-form-group">
            <label class="settings-form-label">Google Tag Manager ID</label>
            <input type="text" id="seo-gtm-id" class="settings-input" placeholder="GTM-XXXXXXX">
            <span class="settings-hint">Paste your GTM container ID (found in GTM dashboard)</span>
        </div>

        <div class="settings-form-group">
            <label class="settings-form-label">robots.txt</label>
            <textarea id="seo-robots-txt" class="settings-input" rows="6" style="resize:vertical;font-family:monospace;font-size:12px;"></textarea>
        </div>

        <div class="settings-form-group">
            <label class="settings-form-label">Auto Generation</label>
            <div class="seo-gen-box">
                <div class="seo-gen-item">
                    <div class="seo-gen-info">
                        <span class="seo-gen-label"><i class="fas fa-globe"></i> Sitemap.xml</span>
                        <span class="seo-gen-desc">Auto-generated from your categories &amp; products</span>
                        <a href="/sitemap.xml" target="_blank" class="seo-gen-link">View live sitemap.xml</a>
                    </div>
                    <button type="button" class="seo-gen-btn" id="gen-sitemap">
                        <i class="fas fa-sync-alt"></i> Generate
                    </button>
                </div>
                <div class="seo-gen-item">
                    <div class="seo-gen-info">
                        <span class="seo-gen-label"><i class="fas fa-robot"></i> robots.txt</span>
                        <span class="seo-gen-desc">Auto-generated with sitemap reference</span>
                        <a href="/robots.txt" target="_blank" class="seo-gen-link">View live robots.txt</a>
                    </div>
                    <button type="button" class="seo-gen-btn" id="gen-robots">
                        <i class="fas fa-sync-alt"></i> Auto Fill
                    </button>
                </div>
            </div>
        </div>

        <div class="settings-form-group">
            <div class="seo-preview-box">
                <div class="seo-preview-label">Google Search Preview</div>
                <div class="seo-preview-title" id="seo-preview-title">Your Page Title</div>
                <div class="seo-preview-url" id="seo-preview-url">https://www.yoursite.com/page</div>
                <div class="seo-preview-desc" id="seo-preview-desc">This is how your page description will appear in search results.</div>
            </div>
        </div>

        <div class="settings-form-actions">
            <button type="submit" class="btn-save-settings" id="seo-btn-save">
                <i class="fas fa-save"></i> Save SEO
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    var seoKeywords = [];

    // Add keyword on Enter or comma
    $(document).on('keydown', '#seo-keyword-input', function (e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            var val = $(this).val().trim().replace(/,/g, '');
            if (val && seoKeywords.indexOf(val) === -1) {
                seoKeywords.push(val);
                renderSeoKeywords();
            }
            $(this).val('');
        }
    });

    // Remove keyword
    $(document).on('click', '.kw-remove', function () {
        var idx = $(this).closest('.seo-keyword-tag').data('kw-idx');
        seoKeywords.splice(idx, 1);
        renderSeoKeywords();
    });

    // Live preview + auto-update robots.txt sitemap reference
    $(document).on('input', '#seo-meta-title, #seo-meta-desc', function () {
        updateSeoPreview();
    });

    $(document).on('input', '#seo-canonical-url', function () {
        updateSeoPreview();
        // Auto-refresh robots.txt with new sitemap URL
        var canonical = $(this).val().trim() || window.location.origin;
        var sitemapRef = "Sitemap: " + canonical.replace(/\/$/, '') + "/sitemap.xml";
        var current = $('#seo-robots-txt').val();
        if (current.indexOf('Sitemap:') !== -1) {
            $('#seo-robots-txt').val(current.replace(/Sitemap: .+/, sitemapRef));
        }
    });

    function renderSeoKeywords() {
        var $list = $('#seo-keywords-list').empty();
        seoKeywords.forEach(function (kw, i) {
            $list.append('<span class="seo-keyword-tag" data-kw-idx="' + i + '">' +
                escHtml(kw) + '<span class="kw-remove" title="Remove">&times;</span></span>');
        });
    }

    function updateSeoPreview() {
        var title = $('#seo-meta-title').val().trim();
        var desc = $('#seo-meta-desc').val().trim();
        var url = $('#seo-canonical-url').val().trim() || 'https://www.yoursite.com/page';
        $('#seo-preview-title').text(title || 'Your Page Title');
        $('#seo-preview-url').text(url.replace(/\/$/, '') + '/page');
        $('#seo-preview-desc').text(desc || 'This is how your page description will appear in search results.');
    }

    // Generate Sitemap
    $(document).on('click', '#gen-sitemap', function () {
        var $btn = $(this);
        $btn.addClass('spinning').prop('disabled', true).html('<i class="fas fa-sync-alt"></i> Generating…');
        $.ajax({
            url: '{{ route("admin.settings.generate-sitemap") }}',
            method: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                toast('Sitemap generated successfully!', 'success');
            },
            error: function () {
                toast('Failed to generate sitemap.', 'error');
            },
            complete: function () {
                $btn.removeClass('spinning').prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Generate');
            }
        });
    });

    // Auto Fill robots.txt
    $(document).on('click', '#gen-robots', function () {
        var $btn = $(this);
        $btn.addClass('spinning').prop('disabled', true).html('<i class="fas fa-sync-alt"></i> Generating…');
        $.ajax({
            url: '{{ route("admin.settings.generate-robots") }}',
            method: 'POST',
            data: { _token: $('meta[name="csrf-token"]').attr('content') },
            success: function (res) {
                if (res.content) {
                    $('#seo-robots-txt').val(res.content);
                    toast('robots.txt auto-filled!', 'success');
                }
            },
            error: function () {
                toast('Failed to generate robots.txt.', 'error');
            },
            complete: function () {
                $btn.removeClass('spinning').prop('disabled', false).html('<i class="fas fa-sync-alt"></i> Auto Fill');
            }
        });
    });

    // Load data
    function loadSeoSettings() {
        $.ajax({
            url: '{{ route("admin.settings.seo-data") }}',
            method: 'GET',
            success: function (res) {
                if (!res.success || !res.data) return;
                var d = res.data;

                $('#seo-meta-title').val(d.meta_title || '');
                $('#seo-meta-desc').val(d.meta_description || '');
                $('#seo-canonical-url').val(d.canonical_url || '');
                $('#seo-robots-txt').val(d.robots_txt || "User-agent: *\nDisallow:");
                $('#seo-gtm-id').val(d.gtm_container_id || '');

                seoKeywords = Array.isArray(d.meta_keywords) ? d.meta_keywords : [];
                renderSeoKeywords();
                updateSeoPreview();
            }
        });
    }

    loadSeoSettings();

    // Submit
    $(document).on('submit', '#seo-settings-form', function (e) {
        e.preventDefault();
        saveSeoSettings();
    });

    function saveSeoSettings() {
        $('#seo-btn-save').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving…');

        $.ajax({
            url: '{{ route("admin.settings.seo-save") }}',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                meta_title: $('#seo-meta-title').val().trim(),
                meta_description: $('#seo-meta-desc').val().trim(),
                meta_keywords: JSON.stringify(seoKeywords),
                canonical_url: $('#seo-canonical-url').val().trim(),
                robots_txt: $('#seo-robots-txt').val(),
                gtm_container_id: $('#seo-gtm-id').val().trim(),
            },
            success: function (res) {
                toast(res.message || 'SEO settings saved successfully.');
                loadSeoSettings();
                $('#seo-btn-save').prop('disabled', false).html('<i class="fas fa-save"></i> Save SEO');
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Could not save SEO settings.';
                toast(msg, 'error');
                $('#seo-btn-save').prop('disabled', false).html('<i class="fas fa-save"></i> Save SEO');
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
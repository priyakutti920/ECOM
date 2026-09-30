@extends('admin.settings.index')

@section('title', 'Trust Features')

@push('styles')
<style>
    .feat-row {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 18px;
    }

    .feat-card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 18px;
        background: #fff;
        transition: box-shadow .15s, opacity .2s;
    }
    .feat-card:hover {
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
    }
    .feat-card.disabled {
        opacity: 0.55;
        background: #fafafa;
    }
    .feat-card-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 14px;
        padding-bottom: 10px;
        border-bottom: 1px solid #f0f0f0;
    }
    .feat-num {
        width: 28px; height: 28px;
        border-radius: 50%;
        background: #7b1fa2;
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .feat-card-header .feat-name {
        font-size: 14px;
        font-weight: 700;
        color: #333;
        flex: 1;
    }

    .toggle-switch {
        position: relative;
        width: 40px;
        height: 22px;
        flex-shrink: 0;
    }
    .toggle-switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    .toggle-slider {
        position: absolute;
        cursor: pointer;
        inset: 0;
        background: #ccc;
        border-radius: 22px;
        transition: .2s;
    }
    .toggle-slider::before {
        content: '';
        position: absolute;
        height: 16px;
        width: 16px;
        left: 3px;
        bottom: 3px;
        background: #fff;
        border-radius: 50%;
        transition: .2s;
    }
    .toggle-switch input:checked + .toggle-slider {
        background: #27ae60;
    }
    .toggle-switch input:checked + .toggle-slider::before {
        transform: translateX(18px);
    }

    .feat-field { margin-bottom: 12px; }
    .feat-field label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: #666;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: .03em;
    }
    .feat-field input,
    .feat-field textarea {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 13px;
        font-family: inherit;
        box-sizing: border-box;
    }
    .feat-field input:focus,
    .feat-field textarea:focus {
        border-color: #7b1fa2;
        outline: none;
        box-shadow: 0 0 0 2px rgba(123,31,162,.12);
    }
    .feat-field textarea {
        resize: vertical;
        min-height: 40px;
    }
    .feat-card.disabled .feat-field input,
    .feat-card.disabled .feat-field textarea {
        background: #f5f5f5;
        color: #999;
    }

    .icon-picker-wrap { position: relative; }
    .icon-picker-btn {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        background: #fafafa;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #333;
    }
    .icon-picker-btn:hover { border-color: #7b1fa2; }
    .icon-picker-btn .icon-preview-mini {
        font-size: 18px;
        width: 28px;
        text-align: center;
        color: #7b1fa2;
    }
    .icon-picker-btn .icon-class-display {
        flex: 1;
        font-family: monospace;
        font-size: 11px;
        color: #888;
        text-align: left;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .icon-picker-popup {
        display: none;
        position: absolute;
        z-index: 999;
        top: calc(100% + 4px);
        left: 0;
        width: 360px;
        max-height: 420px;
        background: #fff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 8px 30px rgba(0,0,0,.12);
        overflow: hidden;
    }
    .icon-picker-popup.open { display: flex; flex-direction: column; }

    .icon-picker-search {
        padding: 8px;
        border-bottom: 1px solid #f0f0f0;
    }
    .icon-picker-search input {
        width: 100%;
        padding: 7px 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 13px;
        box-sizing: border-box;
    }
    .icon-picker-search input:focus {
        border-color: #7b1fa2;
        outline: none;
    }

    .icon-picker-tabs {
        display: flex;
        border-bottom: 1px solid #f0f0f0;
        overflow-x: auto;
    }
    .icon-picker-tab {
        flex-shrink: 0;
        padding: 6px 12px;
        font-size: 11px;
        font-weight: 600;
        cursor: pointer;
        border-bottom: 2px solid transparent;
        color: #777;
        background: none;
        border-top: none;
        border-left: none;
        border-right: none;
    }
    .icon-picker-tab.active {
        color: #7b1fa2;
        border-bottom-color: #7b1fa2;
    }
    .icon-picker-tab:hover { color: #7b1fa2; }

    .icon-picker-grid {
        display: grid;
        grid-template-columns: repeat(8, 1fr);
        gap: 2px;
        padding: 6px;
        overflow-y: auto;
        max-height: 300px;
    }
    .icon-picker-item {
        width: 100%;
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        cursor: pointer;
        border-radius: 5px;
        color: #555;
        transition: all .1s;
        border: 2px solid transparent;
        background: none;
    }
    .icon-picker-item:hover {
        background: #f3e5f5;
        color: #7b1fa2;
    }
    .icon-picker-item.selected {
        background: #ede7f6;
        border-color: #7b1fa2;
        color: #7b1fa2;
    }

    .feat-form-actions {
        margin-top: 20px;
        padding-top: 16px;
        border-top: 1px solid #eee;
        display: flex;
        gap: 10px;
        align-items: center;
    }
    .feat-btn-save {
        padding: 9px 24px;
        background: #7b1fa2;
        color: #fff;
        border: none;
        border-radius: 5px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
    }
    .feat-btn-save:hover { background: #6a1b9a; }
    .feat-btn-save:disabled { opacity: .6; cursor: not-allowed; }

    .feat-preview-strip {
        display: flex;
        gap: 16px;
        flex-wrap: wrap;
        margin-top: 16px;
        padding: 16px;
        background: #fafafa;
        border-radius: 8px;
        border: 1px dashed #ddd;
    }
    .feat-preview-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 14px;
        background: #fff;
        border: 1px solid #eee;
        border-radius: 6px;
        min-width: 180px;
    }
    .feat-preview-item.hidden-preview {
        opacity: 0.35;
        text-decoration: line-through;
        text-decoration-color: #ccc;
    }
    .feat-preview-icon {
        width: 40px; height: 40px;
        border-radius: 50%;
        background: rgba(123,31,162,.1);
        color: #7b1fa2;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .feat-preview-text {
        font-size: 12px;
        line-height: 1.3;
    }
    .feat-preview-text strong {
        display: block;
        color: #333;
        font-size: 13px;
    }
    .feat-preview-text span {
        color: #999;
    }

    .toast-wrap { position: fixed; top: 18px; right: 18px; z-index: 99999; min-width: 260px; }
    .toast-msg {
        display: flex; align-items: center; gap: 8px;
        padding: 10px 14px; border-radius: 5px; margin-bottom: 7px;
        font-size: 13px; font-weight: 500; box-shadow: 0 2px 10px rgba(0,0,0,.10);
    }
    .toast-msg.success { background: #f0fdf4; border-left: 4px solid #27ae60; color: #1a7a42; }
    .toast-msg.error   { background: #fff0f0; border-left: 4px solid #c0392b; color: #a93226; }
</style>
@endpush

@section('settings_content')
<div id="toast-wrap"></div>

<div class="settings-card">
    <h3 class="settings-card-title">
        <i class="fas fa-th-large"></i> Trust Features (Home Page)
    </h3>
    <p style="font-size:12px; color:#888; margin: 0 0 18px;">
        Manage the 4 feature icons shown on the shop home page. Toggle each feature on/off, pick an icon, set title and description.
    </p>

    <form id="features-form" method="POST">
        @csrf
        <div class="feat-row" id="feat-rows">
            @for ($i = 1; $i <= 4; $i++)
                <?php
                    $f = $features[$i - 1] ?? null;
                    $iconVal  = $f['icon']    ?? 'las la-check-circle';
                    $titleVal = $f['title']   ?? '';
                    $descVal  = $f['desc']    ?? '';
                    $enabled  = $f['enabled'] ?? true;
                ?>
                <div class="feat-card{{ $enabled ? '' : ' disabled' }}" data-index="{{ $i }}" id="feat-card-{{ $i }}">
                    <div class="feat-card-header">
                        <span class="feat-num">{{ $i }}</span>
                        <span class="feat-name">{{ $titleVal ?: 'Feature ' . $i }}</span>
                        <label class="toggle-switch" title="{{ $enabled ? 'Click to disable' : 'Click to enable' }}">
                            <input type="checkbox" class="feat-toggle" data-idx="{{ $i }}" {{ $enabled ? 'checked' : '' }}>
                            <span class="toggle-slider"></span>
                        </label>
                    </div>

                    <input type="hidden" name="enabled[]" id="enabled-hidden-{{ $i }}" value="{{ $enabled ? '1' : '0' }}">

                    <div class="feat-field">
                        <label>Icon Class</label>
                        <div class="icon-picker-wrap">
                            <button type="button" class="icon-picker-btn" data-idx="{{ $i }}">
                                <i class="icon-preview-mini {{ $iconVal }}" id="icon-preview-mini-{{ $i }}"></i>
                                <span class="icon-class-display" id="icon-display-{{ $i }}">{{ $iconVal }}</span>
                            </button>
                            <div class="icon-picker-popup" id="icon-popup-{{ $i }}">
                                <div class="icon-picker-search">
                                    <input type="text" placeholder="Search icons..." id="icon-search-{{ $i }}">
                                </div>
                                <div class="icon-picker-tabs" id="icon-tabs-{{ $i }}"></div>
                                <div class="icon-picker-grid" id="icon-grid-{{ $i }}"></div>
                            </div>
                            <input type="hidden" name="icons[]" id="icon-hidden-{{ $i }}" value="{{ $iconVal }}">
                        </div>
                    </div>

                    <div class="feat-field">
                        <label>Title</label>
                        <input type="text" name="titles[]" value="{{ $titleVal }}" placeholder="e.g. Free Shipping">
                    </div>

                    <div class="feat-field">
                        <label>Description</label>
                        <textarea name="descs[]" rows="2" placeholder="e.g. On orders over ₹499">{{ $descVal }}</textarea>
                    </div>
                </div>
            @endfor
        </div>

        <div class="feat-form-actions">
            <button type="submit" class="feat-btn-save" id="feat-btn-save">
                <i class="fas fa-save"></i> Save All Features
            </button>
            <span id="feat-save-status" style="font-size:12px; color:#aaa;"></span>
        </div>
    </form>
</div>

<h4 style="margin: 20px 0 10px; font-size:14px; font-weight:700; color:#333;">
    <i class="fas fa-eye" style="color:#7b1fa2; margin-right:4px;"></i> Live Preview
</h4>
<div class="feat-preview-strip" id="feat-preview">
    @for ($i = 1; $i <= 4; $i++)
        <?php
            $f = $features[$i - 1] ?? null;
            $iconVal  = $f['icon']    ?? 'las la-check-circle';
            $titleVal = $f['title']   ?? '';
            $descVal  = $f['desc']    ?? '';
            $enabled  = $f['enabled'] ?? true;
        ?>
        <div class="feat-preview-item{{ $enabled ? '' : ' hidden-preview' }}" id="preview-item-{{ $i }}">
            <div class="feat-preview-icon">
                <i class="{{ $iconVal }}" id="preview-icon-{{ $i }}"></i>
            </div>
            <div class="feat-preview-text">
                <strong id="preview-title-{{ $i }}">{{ $titleVal ?: 'Feature ' . $i }}</strong>
                <span id="preview-desc-{{ $i }}">{{ $descVal }}</span>
            </div>
        </div>
    @endfor
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    var SAVE_URL = '{{ route("admin.settings.features-save") }}';
    var TOGGLE_URL = '{{ route("admin.settings.features-toggle") }}';

    // ── Icon catalog ──────────────────────────────────────
    var ICON_CATALOG = {
        'General': [
            'las la-check-circle','las la-times-circle','las la-info-circle','las la-exclamation-circle',
            'las la-thumbs-up','las la-thumbs-down','las la-star','las la-heart',
            'las la-smile','las la-frown','las la-trophy','las la-award',
            'las la-flag','las la-bookmark','las la-bell','las la-bolt',
            'las la-fire','las la-gem','las la-crown','las la-rocket',
            'las la-shield-alt','las la-shield-check','las la-lock','las la-unlock',
        ],
        'Shipping & Delivery': [
            'las la-shipping-fast','las la-truck','las la-box','las la-box-open',
            'las la-dolly','las la-parachute-box','las la-route','las la-map-marked-alt',
        ],
        'Support & Help': [
            'las la-headset','las la-phone','las la-comments','las la-comment-dots',
            'las la-life-ring','las la-handshake','las la-user-headset','las la-question-circle',
        ],
        'Returns & Refunds': [
            'las la-undo','las la-redo','las la-exchange-alt','las la-reply',
            'las la-sync','las la-spinner','las la-check-double',
        ],
        'Payment & Security': [
            'las la-credit-card','las la-money-bill','las la-wallet','las la-piggy-bank',
            'las la-lock','las la-key','las la-shield-alt','las la-fingerprint',
            'las la-check-circle','las la-check-double','las la-certificate',
        ],
        'Quality & Trust': [
            'las la-medal','las la-certificate','las la-star','las la-heart',
            'las la-thumbs-up','las la-gem','las la-crown','las la-award',
            'las la-shield-alt','las la-check-circle','las la-check',
        ],
        'Shopping': [
            'las la-shopping-bag','las la-shopping-cart','las la-tag','las la-percent',
            'las la-tags','las la-store','las la-receipt','las la-calculator',
        ],
        'Time & Speed': [
            'las la-clock','las la-hourglass-half','las la-bolt','las la-rocket',
            'las la-forward','las la-play','las la-tachometer-alt','las la-stopwatch',
        ],
    };

    var openPopup = null;

    // ── Toggle ────────────────────────────────────────────
    $(document).on('change', '.feat-toggle', function () {
        var idx = $(this).data('idx');
        var card = $('#feat-card-' + idx);
        var hidden = $('#enabled-hidden-' + idx);
        var newState = $(this).is(':checked');

        if (newState) {
            card.removeClass('disabled');
            hidden.val('1');
        } else {
            card.addClass('disabled');
            hidden.val('0');
        }

        $('#preview-item-' + idx).toggleClass('hidden-preview', !newState);

        // Persist toggle immediately
        $.post(TOGGLE_URL, {
            index: idx,
            _token: $('input[name="_token"]').val()
        }, function (res) {
            if (res.success) showToast(res.message);
        }).fail(function () {
            // Revert toggle
            var card = $('#feat-card-' + idx);
            var toggle = card.find('.feat-toggle');
            var hidden = $('#enabled-hidden-' + idx);
            var prevState = !newState;

            toggle.prop('checked', prevState);
            hidden.val(prevState ? '1' : '0');
            if (prevState) {
                card.removeClass('disabled');
            } else {
                card.addClass('disabled');
            }
            $('#preview-item-' + idx).toggleClass('hidden-preview', newState);
            showToast('Toggle failed.', 'error');
        });
    });

    // ── Icon Picker ───────────────────────────────────────
    function buildTabs(containerId) {
        var cats = Object.keys(ICON_CATALOG);
        var html = '';
        for (var c = 0; c < cats.length; c++) {
            html += '<button type="button" class="icon-picker-tab' + (c === 0 ? ' active' : '') + '" data-cat="' + c + '">' + cats[c] + '</button>';
        }
        $('#icon-tabs-' + containerId).html(html);

        $('#icon-tabs-' + containerId).on('click', '.icon-picker-tab', function () {
            $(this).addClass('active').siblings().removeClass('active');
            renderGrid(containerId, parseInt($(this).data('cat')));
        });
    }

    function renderGrid(containerId, catIdx) {
        var cats = Object.keys(ICON_CATALOG);
        var icons = ICON_CATALOG[cats[catIdx]] || [];
        var selected = $('#icon-hidden-' + containerId).val();
        var html = '';
        for (var i = 0; i < icons.length; i++) {
            html += '<button type="button" class="icon-picker-item' + (icons[i] === selected ? ' selected' : '') + '" data-icon="' + icons[i] + '"><i class="' + icons[i] + '"></i></button>';
        }
        $('#icon-grid-' + containerId).html(html);
    }

    $(document).on('click', '.icon-picker-item', function () {
        var grid = $(this).closest('.icon-picker-grid');
        var containerId = grid.attr('id').replace('icon-grid-', '');
        pickIcon(containerId, $(this).data('icon'));
    });

    function pickIcon(containerId, iconClass) {
        $('#icon-hidden-' + containerId).val(iconClass);
        $('#icon-preview-mini-' + containerId).attr('class', 'icon-preview-mini ' + iconClass);
        $('#icon-display-' + containerId).text(iconClass);
        $('#preview-icon-' + containerId).attr('class', iconClass);
        closePopups();
    }

    function openPopup(idx) {
        closePopups();
        $('#icon-popup-' + idx).addClass('open');
        openPopup = idx;
        buildTabs(idx);
        renderGrid(idx, 0);
    }

    function closePopups() {
        $('.icon-picker-popup.open').removeClass('open');
        openPopup = null;
    }

    for (var i = 1; i <= 4; i++) {
        (function (idx) {
            var $btn = $('.icon-picker-btn[data-idx="' + idx + '"]');
            $btn.on('click', function (e) {
                e.stopPropagation();
                if (openPopup === idx) { closePopups(); }
                else { openPopup(idx); }
            });

            $('#icon-search-' + idx).on('input', function () {
                var q = $(this).val().toLowerCase();
                $('#icon-grid-' + idx + ' .icon-picker-item').each(function () {
                    $(this).toggle($(this).data('icon').toLowerCase().indexOf(q) >= 0);
                });
            });
        })(i);
    }

    $(document).on('click', function (e) {
        if (!$(e.target).closest('.icon-picker-wrap').length) { closePopups(); }
    });

    // ── Live preview ──────────────────────────────────────
    $(document).on('input', '.feat-card input[name="titles[]"], .feat-card textarea[name="descs[]"]', function () {
        var card = $(this).closest('.feat-card');
        var idx = card.data('index');
        $('#preview-title-' + idx).text(card.find('input[name="titles[]"]').val() || 'Feature ' + idx);
        $('#preview-desc-' + idx).text(card.find('textarea[name="descs[]"]').val() || '');
    });

    // ── Save — standard form submit via AJAX ──────────────
    $('#features-form').on('submit', function (e) {
        e.preventDefault();

        var btn = $('#feat-btn-save');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving…');

        var fd = new FormData(this);

        $.ajax({
            url: SAVE_URL,
            type: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            },
            success: function (res) {
                showToast(res.message || 'Features saved successfully.');
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save All Features');
            },
            error: function (xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message)
                    ? xhr.responseJSON.message
                    : 'Save failed. Status: ' + xhr.status + '. Check F12 console.';
                showToast(msg, 'error');
                console.error('Save error:', xhr.status, xhr.responseText);
                btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save All Features');
            }
        });
    });

    function showToast(msg) {
        var el = $('<div class="toast-msg success"><i class="fas fa-check-circle"></i> ' + msg + '</div>');
        $('#toast-wrap').append(el);
        setTimeout(function () { el.fadeOut(300, function () { el.remove(); }); }, 3500);
    }
})();
</script>
@endpush

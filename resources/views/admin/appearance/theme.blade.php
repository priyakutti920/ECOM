@extends('layouts.admin')

@section('title', 'Theme & Appearance Customization')

@push('styles')
<style>
.theme-customizer-wrap {
    max-width: 1280px;
    margin: 0 auto;
    padding: 10px 0 40px;
}
.theme-header-bar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 24px;
    background: #ffffff;
    padding: 18px 24px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.theme-header-title h2 {
    margin: 0 0 4px;
    font-size: 20px;
    font-weight: 800;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 10px;
}
.theme-header-title p {
    margin: 0;
    font-size: 13px;
    color: #64748b;
}
.theme-header-actions {
    display: flex;
    gap: 10px;
    align-items: center;
}

/* Preset Cards Grid */
.presets-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.section-title {
    font-size: 15px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.preset-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
    gap: 14px;
}
.preset-card {
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 14px;
    cursor: pointer;
    background: #f8fafc;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.preset-card:hover {
    border-color: #0068e1;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    background: #ffffff;
}
.preset-card.is-active {
    border-color: #0068e1;
    background: #eff6ff;
    box-shadow: 0 0 0 1.5px #0068e1;
}
.preset-info h4 {
    margin: 0 0 3px;
    font-size: 13.5px;
    font-weight: 700;
    color: #1e293b;
}
.preset-info span {
    font-size: 11px;
    color: #64748b;
    display: block;
}
.preset-swatches {
    display: flex;
    align-items: center;
    gap: 5px;
}
.preset-dot {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 2px solid #ffffff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

/* Two Column Layout */
.theme-columns {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}
@media (max-width: 991px) {
    .theme-columns {
        grid-template-columns: 1fr;
    }
}

.panel-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    margin-bottom: 24px;
}
.panel-card-head {
    padding: 14px 20px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 8px;
}
.panel-card-body {
    padding: 20px;
}

/* Color Controls */
.color-field-row {
    margin-bottom: 20px;
}
.color-field-row:last-child {
    margin-bottom: 0;
}
.color-field-label {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 13px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 8px;
}
.color-field-label span.hint {
    font-size: 11.5px;
    font-weight: 400;
    color: #94a3b8;
}
.color-input-combo {
    display: flex;
    align-items: center;
    gap: 12px;
}
.color-picker-box {
    width: 44px;
    height: 44px;
    padding: 0;
    border: 2px solid #cbd5e1;
    border-radius: 8px;
    cursor: pointer;
    outline: none;
    background: transparent;
    transition: border-color 0.15s;
}
.color-picker-box:hover {
    border-color: #0068e1;
}
.color-hex-input {
    flex: 1;
    height: 42px;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    padding: 0 14px;
    font-size: 14px;
    font-family: monospace;
    font-weight: 600;
    color: #1e293b;
    text-transform: uppercase;
    transition: all 0.15s;
}
.color-hex-input:focus {
    border-color: #0068e1;
    box-shadow: 0 0 0 3px rgba(0, 104, 225, 0.12);
    outline: none;
}

/* Radio Option Pills */
.style-options-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
}
.style-option-label {
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 10px;
    text-align: center;
    cursor: pointer;
    background: #f8fafc;
    transition: all 0.15s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
}
.style-option-label:hover {
    border-color: #cbd5e1;
    background: #ffffff;
}
.style-option-label input[type="radio"] {
    display: none;
}
.style-option-label.is-selected {
    border-color: #0068e1;
    background: #eff6ff;
    color: #0068e1;
    font-weight: 700;
}
.style-option-label i {
    font-size: 18px;
}

/* Select Control */
.theme-select {
    width: 100%;
    height: 42px;
    border: 1.5px solid #e2e8f0;
    border-radius: 8px;
    padding: 0 12px;
    font-size: 13.5px;
    font-weight: 600;
    color: #1e293b;
    outline: none;
    background: #ffffff;
}
.theme-select:focus {
    border-color: #0068e1;
    box-shadow: 0 0 0 3px rgba(0, 104, 225, 0.12);
}

/* Live Preview Mockup Box */
.preview-sticky {
    position: sticky;
    top: 20px;
}
.live-mockup {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #f8fafc;
    overflow: hidden;
    box-shadow: 0 4px 20px -2px rgba(0,0,0,0.06);
    transition: all 0.2s ease;
}
.mockup-header-bar {
    padding: 12px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    border-bottom: 1px solid #e2e8f0;
    transition: background 0.2s ease, border-color 0.2s ease;
}
.mockup-logo {
    font-size: 17px;
    font-weight: 800;
    letter-spacing: -0.4px;
    white-space: nowrap;
}
.mockup-search-box {
    flex: 1;
    max-width: 260px;
    height: 32px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    background: #ffffff;
    border: 1.5px solid transparent;
    overflow: hidden;
    padding-left: 10px;
    font-size: 12px;
    color: #64748b;
    transition: border-color 0.2s;
}
.mockup-search-btn {
    height: 100%;
    padding: 0 12px;
    border: none;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}

.mockup-body {
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}
.mockup-card-container {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px;
    max-width: 240px;
    margin: 0 auto;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.mockup-card-img-placeholder {
    width: 100%;
    height: 120px;
    background: #f1f5f9;
    border-radius: 6px;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #94a3b8;
    font-size: 28px;
    margin-bottom: 10px;
}
.mockup-card-pill {
    position: absolute;
    top: 6px;
    left: 6px;
    font-size: 9.5px;
    font-weight: 700;
    color: #ffffff;
    padding: 2px 6px;
    border-radius: 4px;
}
.mockup-card-cat {
    font-size: 10px;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
}
.mockup-card-title {
    font-size: 12.5px;
    font-weight: 700;
    color: #0f172a;
    margin: 2px 0 6px;
    line-height: 1.3;
}
.mockup-card-price-row {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 10px;
}
.mockup-card-current-price {
    font-size: 14.5px;
    font-weight: 800;
    color: #0f172a;
}
.mockup-card-orig-price {
    font-size: 11px;
    color: #94a3b8;
    text-decoration: line-through;
}
.mockup-card-discount-tag {
    font-size: 9.5px;
    font-weight: 700;
    color: #16a34a;
    background: #dcfce7;
    padding: 1px 4px;
    border-radius: 3px;
}
.mockup-card-btns {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 6px;
}
.mockup-btn {
    height: 28px;
    border-radius: 5px;
    border: none;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 3px;
    transition: all 0.15s ease;
}
.mockup-btn-cart {
    background: #f1f5f9;
    color: #0f172a;
    border: 1px solid #e2e8f0;
}
.mockup-btn-buy {
    color: #ffffff;
}

/* Toast Container */
#toast-container {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 999999;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.toast-alert {
    padding: 12px 18px;
    border-radius: 8px;
    background: #0f172a;
    color: #ffffff;
    font-size: 13.5px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 10px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    animation: toastIn 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
.toast-alert.is-success {
    background: #059669;
}
.toast-alert.is-error {
    background: #dc2626;
}
@keyframes toastIn {
    from { opacity: 0; transform: translateY(12px) scale(0.96); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}
</style>
@endpush

@section('content')
<div class="theme-customizer-wrap">

    <!-- Header Toolbar -->
    <div class="theme-header-bar">
        <div class="theme-header-title">
            <h2><i class="fas fa-palette text-primary"></i> Theme &amp; Appearance Customization</h2>
            <p>Customize primary brand accents, secondary highlights, typography, and storefront header styling.</p>
        </div>
        <div class="theme-header-actions">
            <a href="{{ url('/') }}" target="_blank" class="btn btn-default" title="View Storefront in new tab">
                <i class="fas fa-external-link-alt"></i> View Storefront
            </a>
            <button type="button" class="btn btn-warning" id="btn-reset-theme">
                <i class="fas fa-undo"></i> Reset to Default
            </button>
            <button type="button" class="btn btn-primary" id="btn-save-theme">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>
    </div>

    <!-- 1-Click Curated Presets -->
    <div class="presets-card">
        <h3 class="section-title">
            <i class="fas fa-magic text-warning"></i> 1-Click Curated Theme Palettes
        </h3>
        <div class="preset-grid">
            @foreach($presets as $p)
                <div class="preset-card {{ $current['primary_color'] === $p['primary'] ? 'is-active' : '' }}"
                     data-id="{{ $p['id'] }}"
                     data-primary="{{ $p['primary'] }}"
                     data-secondary="{{ $p['secondary'] }}"
                     data-font="{{ $p['font'] }}"
                     data-header="{{ $p['header'] }}">
                    <div class="preset-info">
                        <h4>{{ $p['name'] }}</h4>
                        <span>{{ $p['font'] }} • {{ ucfirst($p['header']) }} Header</span>
                    </div>
                    <div class="preset-swatches">
                        <span class="preset-dot" style="background: {{ $p['primary'] }};" title="Primary: {{ $p['primary'] }}"></span>
                        <span class="preset-dot" style="background: {{ $p['secondary'] }};" title="Secondary: {{ $p['secondary'] }}"></span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Main Customization Form -->
    <form id="theme-customization-form" method="POST" action="{{ route('admin.appearance.theme.update') }}">
        @csrf
        <div class="theme-columns">

            <!-- Left: Settings Controls -->
            <div class="controls-column">

                <!-- Palette Colors Card -->
                <div class="panel-card">
                    <div class="panel-card-head">
                        <i class="fas fa-tint text-primary"></i> Brand &amp; Accent Colors
                    </div>
                    <div class="panel-card-body">
                        <!-- Primary Color -->
                        <div class="color-field-row">
                            <label class="color-field-label">
                                Primary Brand Color
                                <span class="hint">Buttons, Links, Active Badges, Focus Rings</span>
                            </label>
                            <div class="color-input-combo">
                                <input type="color" id="primary-picker" class="color-picker-box" value="{{ $current['primary_color'] }}">
                                <input type="text" id="primary-hex" name="primary_color" class="color-hex-input" value="{{ $current['primary_color'] }}" maxlength="7">
                            </div>
                        </div>

                        <!-- Secondary Color -->
                        <div class="color-field-row">
                            <label class="color-field-label">
                                Secondary / Accent Color
                                <span class="hint">Highlights, Action Callouts, Accent Bars</span>
                            </label>
                            <div class="color-input-combo">
                                <input type="color" id="secondary-picker" class="color-picker-box" value="{{ $current['secondary_color'] }}">
                                <input type="text" id="secondary-hex" name="secondary_color" class="color-hex-input" value="{{ $current['secondary_color'] }}" maxlength="7">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Header Style Card -->
                <div class="panel-card">
                    <div class="panel-card-head">
                        <i class="fas fa-window-maximize text-info"></i> Storefront Header Styling
                    </div>
                    <div class="panel-card-body">
                        <div class="style-options-grid">
                            <label class="style-option-label {{ $current['header_style'] === 'light' ? 'is-selected' : '' }}" data-value="light">
                                <input type="radio" name="header_style" value="light" {{ $current['header_style'] === 'light' ? 'checked' : '' }}>
                                <i class="fas fa-sun text-warning"></i>
                                <span>Crisp Light</span>
                            </label>
                            <label class="style-option-label {{ $current['header_style'] === 'dark' ? 'is-selected' : '' }}" data-value="dark">
                                <input type="radio" name="header_style" value="dark" {{ $current['header_style'] === 'dark' ? 'checked' : '' }}>
                                <i class="fas fa-moon text-dark"></i>
                                <span>Dark Charcoal</span>
                            </label>
                            <label class="style-option-label {{ $current['header_style'] === 'primary' ? 'is-selected' : '' }}" data-value="primary">
                                <input type="radio" name="header_style" value="primary" {{ $current['header_style'] === 'primary' ? 'checked' : '' }}>
                                <i class="fas fa-paint-brush text-primary"></i>
                                <span>Primary Solid</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Typography & Font Card -->
                <div class="panel-card">
                    <div class="panel-card-head">
                        <i class="fas fa-font text-success"></i> Typography / Font Family
                    </div>
                    <div class="panel-card-body">
                        <select name="theme_font" id="font-family-select" class="theme-select">
                            <option value="Rubik" {{ $current['theme_font'] === 'Rubik' ? 'selected' : '' }}>Rubik — Modern Geometric (Default)</option>
                            <option value="Inter" {{ $current['theme_font'] === 'Inter' ? 'selected' : '' }}>Inter — Clean Modern UI</option>
                            <option value="Plus Jakarta Sans" {{ $current['theme_font'] === 'Plus Jakarta Sans' ? 'selected' : '' }}>Plus Jakarta Sans — Editorial &amp; High-End</option>
                            <option value="Outfit" {{ $current['theme_font'] === 'Outfit' ? 'selected' : '' }}>Outfit — Trendy Lifestyle &amp; Apparel</option>
                            <option value="Poppins" {{ $current['theme_font'] === 'Poppins' ? 'selected' : '' }}>Poppins — Warm Geometric Rounded</option>
                        </select>
                        <p style="margin: 8px 0 0; font-size: 12px; color: #64748b;">
                            Fonts are loaded seamlessly via Google Fonts on the customer storefront.
                        </p>
                    </div>
                </div>

            </div>

            <!-- Right: Real-Time Live Preview -->
            <div class="preview-column">
                <div class="preview-sticky">
                    <div class="panel-card">
                        <div class="panel-card-head" style="justify-content: space-between;">
                            <span><i class="fas fa-eye text-primary"></i> Real-Time Storefront Preview</span>
                            <span class="badge" style="background:#e0f2fe; color:#0369a1; font-weight:700;">Live Simulation</span>
                        </div>
                        <div class="panel-card-body" style="background: #f1f5f9;">
                            
                            <div class="live-mockup" id="live-mockup">
                                <!-- Mockup Header -->
                                <div class="mockup-header-bar" id="mockup-header-bar">
                                    <div class="mockup-logo" id="mockup-logo">
                                        Nool &amp; Crop<span id="mockup-logo-dot" style="color: {{ $current['primary_color'] }};">.</span>
                                    </div>
                                    <div class="mockup-search-box" id="mockup-search-box" style="border-color: {{ $current['primary_color'] }};">
                                        <span>Search products…</span>
                                        <div class="mockup-search-btn" id="mockup-search-btn" style="background: {{ $current['primary_color'] }};">
                                            <i class="fas fa-search"></i>
                                        </div>
                                    </div>
                                </div>

                                <!-- Mockup Content Area -->
                                <div class="mockup-body">
                                    <div class="mockup-card-container">
                                        <div class="mockup-card-img-placeholder">
                                            <i class="fas fa-tshirt"></i>
                                            <span class="mockup-card-pill" id="mockup-card-pill" style="background: #ef4444;">-40%</span>
                                        </div>
                                        <div class="mockup-card-cat">Oversized T-Shirts</div>
                                        <div class="mockup-card-title">Acid Wash Vintage Tee</div>
                                        <div class="mockup-card-price-row">
                                            <span class="mockup-card-current-price">₹599.00</span>
                                            <span class="mockup-card-orig-price">₹999.00</span>
                                            <span class="mockup-card-discount-tag">40% OFF</span>
                                        </div>
                                        <div class="mockup-card-btns">
                                            <button type="button" class="mockup-btn mockup-btn-cart">
                                                <i class="fas fa-shopping-bag"></i> Add
                                            </button>
                                            <button type="button" class="mockup-btn mockup-btn-buy" id="mockup-btn-buy" style="background: {{ $current['primary_color'] }};">
                                                <i class="fas fa-bolt"></i> Buy
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <p style="margin: 14px 0 0; text-align: center; font-size: 12px; color: #64748b;">
                                <i class="fas fa-info-circle"></i> Changes reflect across the storefront header, product cards, buttons, badges, and search bar instantly.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<div id="toast-container"></div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    const $primaryPicker = $('#primary-picker');
    const $primaryHex = $('#primary-hex');
    const $secondaryPicker = $('#secondary-picker');
    const $secondaryHex = $('#secondary-hex');
    const $fontSelect = $('#font-family-select');
    const $headerOptions = $('.style-option-label');

    // Live Mockup Elements
    const $mockupHeader = $('#mockup-header-bar');
    const $mockupLogo = $('#mockup-logo');
    const $mockupLogoDot = $('#mockup-logo-dot');
    const $mockupSearchBox = $('#mockup-search-box');
    const $mockupSearchBtn = $('#mockup-search-btn');
    const $mockupBtnBuy = $('#mockup-btn-buy');

    // Update Mockup UI
    function updateMockup() {
        const primary = $primaryHex.val().trim();
        const secondary = $secondaryHex.val().trim();
        const headerStyle = $('input[name="header_style"]:checked').val() || 'light';
        const font = $fontSelect.val() || 'Rubik';

        // Apply Font
        $('#live-mockup').css('font-family', font + ', sans-serif');

        // Apply Primary to Buttons & Dots
        $mockupLogoDot.css('color', primary);
        $mockupSearchBtn.css('background', primary);
        $mockupSearchBox.css('border-color', primary);
        $mockupBtnBuy.css('background', primary);

        // Apply Header Style
        if (headerStyle === 'dark') {
            $mockupHeader.css({ 'background': '#0f172a', 'border-color': '#1e293b' });
            $mockupLogo.css('color', '#ffffff');
        } else if (headerStyle === 'primary') {
            $mockupHeader.css({ 'background': primary, 'border-color': primary });
            $mockupLogo.css('color', '#ffffff');
        } else {
            $mockupHeader.css({ 'background': '#ffffff', 'border-color': '#e2e8f0' });
            $mockupLogo.css('color', '#0f172a');
        }
    }

    // Synchronize Primary Color
    $primaryPicker.on('input change', function () {
        $primaryHex.val(this.value.toUpperCase());
        updateMockup();
    });
    $primaryHex.on('input change', function () {
        let val = this.value.trim();
        if (val && !val.startsWith('#')) val = '#' + val;
        if (/^#[0-9A-Fa-f]{6}$/i.test(val)) {
            $primaryPicker.val(val);
            updateMockup();
        }
    });

    // Synchronize Secondary Color
    $secondaryPicker.on('input change', function () {
        $secondaryHex.val(this.value.toUpperCase());
        updateMockup();
    });
    $secondaryHex.on('input change', function () {
        let val = this.value.trim();
        if (val && !val.startsWith('#')) val = '#' + val;
        if (/^#[0-9A-Fa-f]{6}$/i.test(val)) {
            $secondaryPicker.val(val);
            updateMockup();
        }
    });

    // Header Style Selection
    $headerOptions.on('click', function () {
        $headerOptions.removeClass('is-selected');
        $(this).addClass('is-selected');
        $(this).find('input[type="radio"]').prop('checked', true);
        updateMockup();
    });

    // Font Selection
    $fontSelect.on('change', function () {
        updateMockup();
    });

    // Preset Selection
    $('.preset-card').on('click', function () {
        $('.preset-card').removeClass('is-active');
        $(this).addClass('is-active');

        const primary = $(this).data('primary');
        const secondary = $(this).data('secondary');
        const font = $(this).data('font');
        const header = $(this).data('header');

        $primaryPicker.val(primary);
        $primaryHex.val(primary);
        $secondaryPicker.val(secondary);
        $secondaryHex.val(secondary);
        $fontSelect.val(font);

        $headerOptions.removeClass('is-selected');
        const $targetOption = $headerOptions.filter(`[data-value="${header}"]`);
        $targetOption.addClass('is-selected');
        $targetOption.find('input[type="radio"]').prop('checked', true);

        updateMockup();
        showToast('Preset loaded! Click "Save Changes" to apply.', 'info');
    });

    // Save Theme Form
    $('#btn-save-theme').on('click', function () {
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving…');

        const formData = $('#theme-customization-form').serialize();

        $.ajax({
            url: '{{ route("admin.appearance.theme.update") }}',
            method: 'POST',
            data: formData,
            success: function (res) {
                showToast(res.message || 'Theme updated successfully!', 'success');
                $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Changes');
            },
            error: function (xhr) {
                const msg = xhr.responseJSON?.message || 'Failed to save theme settings.';
                showToast(msg, 'error');
                $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Changes');
            }
        });
    });

    // Reset Theme
    $('#btn-reset-theme').on('click', function () {
        if (!confirm('Are you sure you want to reset Theme & Appearance to default settings?')) {
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Resetting…');

        $.ajax({
            url: '{{ route("admin.appearance.theme.reset") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function (res) {
                showToast(res.message || 'Theme reset to default!', 'success');
                setTimeout(() => window.location.reload(), 800);
            },
            error: function (xhr) {
                showToast('Failed to reset theme.', 'error');
                $btn.prop('disabled', false).html('<i class="fas fa-undo"></i> Reset to Default');
            }
        });
    });

    function showToast(msg, type = 'success') {
        const icon = type === 'error' ? 'fa-exclamation-triangle' : (type === 'info' ? 'fa-info-circle' : 'fa-check-circle');
        const alertClass = type === 'error' ? 'is-error' : 'is-success';
        const $toast = $(`
            <div class="toast-alert ${alertClass}">
                <i class="fas ${icon}"></i>
                <span>${msg}</span>
            </div>
        `);
        $('#toast-container').append($toast);
        setTimeout(() => {
            $toast.fadeOut(300, function () { $(this).remove(); });
        }, 3500);
    }

    // Initial render
    updateMockup();
});
</script>
@endpush

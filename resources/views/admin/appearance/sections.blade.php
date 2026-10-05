@extends('layouts.admin')

@section('title', 'Homepage Product Sections')

@push('styles')
<style>
.sec-manager-wrap {
    max-width: 1200px;
    margin: 0 auto;
    padding: 10px 0 40px;
}
.page-title-box {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 15px;
    margin-bottom: 22px;
}
.page-title-box h2 {
    margin: 0;
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 10px;
}
.stats-banner {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 24px;
}
.stat-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    display: flex;
    align-items: center;
    gap: 16px;
}
.stat-icon-wrap {
    width: 44px;
    height: 44px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}
.stat-icon-featured { background: #fef3c7; color: #d97706; }
.stat-icon-deals    { background: #ffe4e6; color: #e11d48; }
.stat-icon-bestsellers { background: #dbeafe; color: #2563eb; }
.stat-icon-latest   { background: #dcfce7; color: #16a34a; }

.stat-info .num {
    font-size: 20px;
    font-weight: 700;
    color: #0f172a;
    line-height: 1.2;
}
.stat-info .lbl {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
}

/* ── Section Cards ── */
.sections-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.sec-card {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 10px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    overflow: hidden;
    transition: box-shadow .2s, border-color .2s;
}
.sec-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.07);
}
.sec-card.disabled {
    border-color: #e2e8f0;
    opacity: 0.85;
}
.sec-card-header {
    padding: 14px 20px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}
.sec-header-left {
    display: flex;
    align-items: center;
    gap: 14px;
}
.sec-drag-handle {
    cursor: grab;
    color: #94a3b8;
    font-size: 16px;
    padding: 4px;
}
.sec-drag-handle:active { cursor: grabbing; }
.sec-icon-badge {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: #fff;
    font-weight: bold;
}
.sec-icon-featured    { background: linear-gradient(135deg, #f59e0b, #d97706); }
.sec-icon-deals       { background: linear-gradient(135deg, #f43f5e, #be123c); }
.sec-icon-bestsellers { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
.sec-icon-latest      { background: linear-gradient(135deg, #10b981, #047857); }

.sec-title-wrap h4 {
    margin: 0;
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
    display: flex;
    align-items: center;
    gap: 8px;
}
.sec-title-wrap small {
    color: #64748b;
    font-size: 12px;
}

.sec-header-right {
    display: flex;
    align-items: center;
    gap: 16px;
}

/* Switch toggle */
.switch {
    position: relative;
    display: inline-block;
    width: 44px;
    height: 24px;
    margin: 0;
}
.switch input { opacity: 0; width: 0; height: 0; }
.slider {
    position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0;
    background-color: #cbd5e1; transition: .2s; border-radius: 24px;
}
.slider:before {
    position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px;
    background-color: white; transition: .2s; border-radius: 50%;
}
input:checked + .slider { background-color: #10b981; }
input:checked + .slider:before { transform: translateX(20px); }

.status-label {
    font-size: 12px;
    font-weight: 600;
    min-width: 60px;
}
.status-label.active { color: #16a34a; }
.status-label.inactive { color: #94a3b8; }

.sec-card-body {
    padding: 20px;
}
.form-grid-3 {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
    margin-bottom: 16px;
}
.form-grid-2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
}
@media (max-width: 768px) {
    .form-grid-2 { grid-template-columns: 1fr; }
}
.form-group-sec {
    margin-bottom: 12px;
}
.form-group-sec label {
    font-size: 12px;
    font-weight: 600;
    color: #334155;
    margin-bottom: 5px;
    display: block;
}
.form-control-sec {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 13px;
    color: #1e293b;
    background: #fff;
    transition: border-color .15s, box-shadow .15s;
}
.form-control-sec:focus {
    border-color: #3b82f6;
    outline: none;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
}

/* Mode radio pill */
.mode-selector {
    display: flex;
    gap: 10px;
    margin-top: 6px;
}
.mode-pill {
    flex: 1;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    padding: 10px 14px;
    cursor: pointer;
    background: #f8fafc;
    transition: all .15s;
    display: flex;
    align-items: flex-start;
    gap: 10px;
}
.mode-pill.selected {
    border-color: #3b82f6;
    background: #eff6ff;
}
.mode-pill input[type="radio"] {
    margin-top: 3px;
}
.mode-pill-content strong {
    display: block;
    font-size: 13px;
    color: #1e293b;
}
.mode-pill-content small {
    display: block;
    font-size: 11px;
    color: #64748b;
    margin-top: 2px;
}

/* Manual Product Picker */
.manual-picker-box {
    margin-top: 14px;
    padding: 16px;
    background: #f8fafc;
    border: 1px dashed #cbd5e1;
    border-radius: 8px;
}
.manual-picker-box.hidden { display: none; }
.product-search-wrap {
    position: relative;
    margin-bottom: 12px;
}
.product-search-input {
    width: 100%;
    padding: 8px 14px 8px 36px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    font-size: 13px;
}
.product-search-wrap i.search-icon {
    position: absolute;
    left: 12px;
    top: 11px;
    color: #94a3b8;
    font-size: 14px;
}
.search-results-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.1);
    z-index: 99;
    max-height: 240px;
    overflow-y: auto;
    display: none;
}
.search-result-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f1f5f9;
    font-size: 13px;
}
.search-result-row:hover { background: #f0f7ff; }
.search-result-row:last-child { border-bottom: none; }
.search-prod-info {
    display: flex;
    align-items: center;
    gap: 10px;
}
.search-prod-thumb {
    width: 34px;
    height: 34px;
    border-radius: 4px;
    object-fit: cover;
    background: #e2e8f0;
}
.search-prod-title {
    font-weight: 600;
    color: #1e293b;
}
.search-prod-price {
    font-size: 12px;
    color: #16a34a;
    font-weight: 600;
}

/* Selected products chips */
.selected-products-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    min-height: 40px;
}
.prod-chip {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 4px 8px;
    background: #fff;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    font-size: 12px;
}
.prod-chip img {
    width: 26px;
    height: 26px;
    border-radius: 4px;
    object-fit: cover;
}
.prod-chip .chip-name {
    font-weight: 600;
    color: #334155;
    max-width: 180px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.prod-chip .chip-remove {
    cursor: pointer;
    color: #ef4444;
    font-weight: bold;
    padding: 0 4px;
    font-size: 14px;
}
.prod-chip .chip-remove:hover { color: #b91c1c; }

/* Preview strip */
.current-preview-strip {
    margin-top: 14px;
    padding-top: 14px;
    border-top: 1px solid #f1f5f9;
}
.preview-title {
    font-size: 12px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.preview-thumbs-row {
    display: flex;
    align-items: center;
    gap: 8px;
    overflow-x: auto;
    padding-bottom: 4px;
}
.preview-thumb-item {
    position: relative;
    width: 52px;
    height: 52px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
    flex-shrink: 0;
    background: #f8fafc;
}
.preview-thumb-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.preview-thumb-item .thumb-price {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(15, 23, 42, 0.75);
    color: #fff;
    font-size: 9px;
    text-align: center;
    padding: 1px 0;
}

/* Save Bar */
.save-bar-bottom {
    position: sticky;
    bottom: 15px;
    background: #1e293b;
    border-radius: 8px;
    padding: 14px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 8px 24px rgba(0,0,0,0.2);
    z-index: 999;
    margin-top: 24px;
    color: #fff;
}
.save-bar-bottom p {
    margin: 0;
    font-size: 13px;
    color: #cbd5e1;
}
.btn-save-sections {
    background: #10b981;
    color: #fff;
    border: none;
    padding: 9px 24px;
    border-radius: 6px;
    font-weight: 700;
    font-size: 14px;
    cursor: pointer;
    transition: background .15s;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-save-sections:hover { background: #059669; }
</style>
@endpush

@section('content')
<div class="sec-manager-wrap">

    {{-- Breadcrumb & Title --}}
    <div class="page-title-box">
        <div>
            <div style="font-size: 12px; color: #64748b; margin-bottom: 4px;">
                <a href="{{ route('admin.dashboard') }}" style="color:#64748b;">Dashboard</a> / 
                <span>Appearance</span> / 
                <span style="color:#0f172a; font-weight:600;">Home Product Sections</span>
            </div>
            <h2><i class="fas fa-layer-group text-primary"></i> Home Product Sections Manager</h2>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="{{ url('/') }}" target="_blank" class="btn btn-default" style="font-size:13px; font-weight:600;">
                <i class="fas fa-external-link-alt"></i> View Storefront
            </a>
            <button type="button" onclick="document.getElementById('sections-form').submit();" class="btn btn-primary" style="font-size:13px; font-weight:600;">
                <i class="fas fa-save"></i> Save All Sections
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="border-radius:6px; font-size:13px; display:flex; align-items:center; gap:8px;">
            <i class="fas fa-check-circle" style="font-size:16px;"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Quick Overview Stats --}}
    <div class="stats-banner">
        <div class="stat-card">
            <div class="stat-icon-wrap stat-icon-deals">
                <i class="fas fa-fire"></i>
            </div>
            <div class="stat-info">
                <div class="num">{{ $stats['total_deals'] }}</div>
                <div class="lbl">Products on Hot Deals</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap stat-icon-latest">
                <i class="fas fa-tshirt"></i>
            </div>
            <div class="stat-info">
                <div class="num">{{ $stats['total_active'] }}</div>
                <div class="lbl">Active Catalog Products</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap stat-icon-bestsellers">
                <i class="fas fa-award"></i>
            </div>
            <div class="stat-info">
                <div class="num">{{ $stats['total_bestsellers'] }}</div>
                <div class="lbl">Best Selling Ranked</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon-wrap stat-icon-featured">
                <i class="fas fa-star"></i>
            </div>
            <div class="stat-info">
                <div class="num">{{ $stats['total_featured'] }}</div>
                <div class="lbl">Featured Products Marked</div>
            </div>
        </div>
    </div>

    {{-- Form --}}
    <form id="sections-form" action="{{ route('admin.appearance.sections.update') }}" method="POST">
        @csrf

        <div class="sections-list" id="sections-sortable-list">
            @foreach($sections as $key => $sec)
                @php
                    $previewData = $sectionPreviews[$key] ?? ['preview_products' => collect(), 'manual_products' => collect()];
                    $previewProds = $previewData['preview_products'];
                    $manualProds = $previewData['manual_products'];
                    $isEnabled = !empty($sec['enabled']);
                    $mode = $sec['mode'] ?? 'auto';
                    $manualIds = $sec['product_ids'] ?? [];
                    $limit = $sec['limit'] ?? 10;
                @endphp

                <div class="sec-card {{ !$isEnabled ? 'disabled' : '' }}" id="card-{{ $key }}">
                    <input type="hidden" name="section_order[]" value="{{ $key }}">
                    <input type="hidden" name="sections[{{ $key }}][key]" value="{{ $key }}">

                    {{-- Card Header --}}
                    <div class="sec-card-header">
                        <div class="sec-header-left">
                            <i class="fas fa-grip-vertical sec-drag-handle" title="Drag to reorder section sequence"></i>
                            <div class="sec-icon-badge sec-icon-{{ $key }}">
                                @if($key === 'featured') <i class="fas fa-star"></i>
                                @elseif($key === 'deals') <i class="fas fa-fire"></i>
                                @elseif($key === 'bestsellers') <i class="fas fa-award"></i>
                                @else <i class="fas fa-tshirt"></i>
                                @endif
                            </div>
                            <div class="sec-title-wrap">
                                <h4>
                                    <span id="title-preview-{{ $key }}">{{ $sec['title'] }}</span>
                                    <span class="badge" style="font-size:10px; background:#e2e8f0; color:#475569;">{{ strtoupper($key) }}</span>
                                </h4>
                                <small id="sub-preview-{{ $key }}">{{ $sec['subtitle'] ?: 'Configure visibility and layout' }}</small>
                            </div>
                        </div>

                        <div class="sec-header-right">
                            <span class="status-label {{ $isEnabled ? 'active' : 'inactive' }}" id="status-text-{{ $key }}">
                                {{ $isEnabled ? 'Visible on Home' : 'Hidden' }}
                            </span>
                            <label class="switch" title="Toggle section visibility on storefront">
                                <input type="checkbox" name="sections[{{ $key }}][enabled]" value="1" {{ $isEnabled ? 'checked' : '' }} onchange="toggleSectionState('{{ $key }}', this.checked)">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="sec-card-body">
                        {{-- Row 1: Section Title, Subtitle, Display Limit --}}
                        <div class="form-grid-3">
                            <div class="form-group-sec">
                                <label><i class="fas fa-heading text-muted"></i> Display Title</label>
                                <input type="text" class="form-control-sec" name="sections[{{ $key }}][title]" value="{{ $sec['title'] }}" oninput="updateTitlePreview('{{ $key }}', this.value)" required>
                            </div>
                            <div class="form-group-sec">
                                <label><i class="fas fa-quote-left text-muted"></i> Subtitle / Tagline (Optional)</label>
                                <input type="text" class="form-control-sec" name="sections[{{ $key }}][subtitle]" value="{{ $sec['subtitle'] ?? '' }}" oninput="updateSubPreview('{{ $key }}', this.value)" placeholder="e.g. Handpicked deals for you">
                            </div>
                            <div class="form-group-sec">
                                <label><i class="fas fa-th text-muted"></i> Product Limit to Show</label>
                                <select class="form-control-sec" name="sections[{{ $key }}][limit]">
                                    <option value="4"  {{ $limit == 4 ? 'selected' : '' }}>4 Products</option>
                                    <option value="6"  {{ $limit == 6 ? 'selected' : '' }}>6 Products</option>
                                    <option value="8"  {{ $limit == 8 ? 'selected' : '' }}>8 Products</option>
                                    <option value="10" {{ $limit == 10 ? 'selected' : '' }}>10 Products (Standard)</option>
                                    <option value="12" {{ $limit == 12 ? 'selected' : '' }}>12 Products</option>
                                    <option value="16" {{ $limit == 16 ? 'selected' : '' }}>16 Products</option>
                                    <option value="20" {{ $limit == 20 ? 'selected' : '' }}>20 Products</option>
                                </select>
                            </div>
                        </div>

                        {{-- Row 2: "View All" Link Settings --}}
                        <div class="form-grid-2">
                            <div class="form-group-sec">
                                <label><i class="fas fa-link text-muted"></i> "View All" Button Target URL</label>
                                <input type="text" class="form-control-sec" name="sections[{{ $key }}][view_all_url]" value="{{ $sec['view_all_url'] ?? '/products' }}" placeholder="/products or /deals">
                            </div>
                            <div class="form-group-sec">
                                <label><i class="fas fa-mouse-pointer text-muted"></i> "View All" Button Text</label>
                                <input type="text" class="form-control-sec" name="sections[{{ $key }}][view_all_text]" value="{{ $sec['view_all_text'] ?? 'View All' }}" placeholder="View All">
                            </div>
                        </div>

                        {{-- Row 3: Selection Mode (Auto vs Manual) --}}
                        <div class="form-group-sec">
                            <label><i class="fas fa-sliders-h text-muted"></i> Product Selection Mode</label>
                            <div class="mode-selector">
                                <label class="mode-pill {{ $mode === 'auto' ? 'selected' : '' }}" onclick="selectMode('{{ $key }}', 'auto')">
                                    <input type="radio" name="sections[{{ $key }}][mode]" value="auto" {{ $mode === 'auto' ? 'checked' : '' }}>
                                    <div class="mode-pill-content">
                                        <strong><i class="fas fa-magic text-primary"></i> Automatic Dynamic</strong>
                                        <small>
                                            @if($key === 'featured') Automatically displays products flagged as "is_featured"
                                            @elseif($key === 'deals') Automatically displays active products having a discounted Special Price
                                            @elseif($key === 'bestsellers') Automatically calculates top sold items based on order volume
                                            @else Automatically selects the newest active additions to the catalog
                                            @endif
                                        </small>
                                    </div>
                                </label>

                                <label class="mode-pill {{ $mode === 'manual' ? 'selected' : '' }}" onclick="selectMode('{{ $key }}', 'manual')">
                                    <input type="radio" name="sections[{{ $key }}][mode]" value="manual" {{ $mode === 'manual' ? 'checked' : '' }}>
                                    <div class="mode-pill-content">
                                        <strong><i class="fas fa-hand-pointer text-warning"></i> Manual Selection</strong>
                                        <small>Pick exact specific products by searching and assigning them here</small>
                                    </div>
                                </label>
                            </div>
                        </div>

                        {{-- Manual Product Picker Container --}}
                        <div class="manual-picker-box {{ $mode !== 'manual' ? 'hidden' : '' }}" id="manual-box-{{ $key }}">
                            <div style="font-size:12px; font-weight:700; color:#334155; margin-bottom:8px;">
                                <i class="fas fa-plus-circle text-primary"></i> Search &amp; Add Products to this Section:
                            </div>

                            <div class="product-search-wrap">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" class="product-search-input" placeholder="Type product name, SKU or code to search…" oninput="handleProductSearch('{{ $key }}', this.value)" autocomplete="off">
                                <div class="search-results-dropdown" id="search-dropdown-{{ $key }}"></div>
                            </div>

                            {{-- Selected Products IDs (Hidden field) --}}
                            <input type="hidden" name="sections[{{ $key }}][product_ids]" id="input-prod-ids-{{ $key }}" value="{{ implode(',', $manualIds) }}">

                            {{-- Selected Products Chip List --}}
                            <div style="font-size:11px; font-weight:600; color:#64748b; margin-bottom:6px;">
                                Currently Selected (<span id="count-selected-{{ $key }}">{{ count($manualIds) }}</span> products):
                            </div>
                            <div class="selected-products-grid" id="selected-grid-{{ $key }}">
                                @foreach($manualIds as $pid)
                                    @if(isset($manualProds[$pid]))
                                        @php
                                            $mProd = $manualProds[$pid];
                                            $mImg = $mProd->primaryImage?->image ?: $mProd->image;
                                            $mImgUrl = $mImg ? (str_starts_with($mImg, 'http') ? $mImg : asset(str_starts_with($mImg, 'storage/') || str_starts_with($mImg, 'product/') ? $mImg : 'storage/' . $mImg)) : asset('images/placeholder.png');
                                        @endphp
                                        <div class="prod-chip" id="chip-{{ $key }}-{{ $pid }}">
                                            <img src="{{ $mImgUrl }}" alt="">
                                            <span class="chip-name" title="{{ $mProd->name }}">{{ $mProd->name }}</span>
                                            <span class="chip-remove" onclick="removeManualProduct('{{ $key }}', {{ $pid }})" title="Remove">&times;</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        {{-- Current Preview Products Strip --}}
                        <div class="current-preview-strip">
                            <div class="preview-title">
                                <span><i class="fas fa-eye"></i> Current Items in this Section ({{ $previewProds->count() }} items)</span>
                                @if($key === 'featured')
                                    <span style="text-transform:none; font-weight:normal; font-size:11px;">
                                        💡 You can also toggle "Featured" status inside any Product's edit page
                                    </span>
                                @endif
                            </div>

                            @if($previewProds->count() > 0)
                                <div class="preview-thumbs-row">
                                    @foreach($previewProds as $p)
                                        @php
                                            $img = $p->primaryImage?->image ?: $p->image;
                                            $imgUrl = $img ? (str_starts_with($img, 'http') ? $img : asset(str_starts_with($img, 'storage/') || str_starts_with($img, 'product/') ? $img : 'storage/' . $img)) : asset('images/placeholder.png');
                                        @endphp
                                        <div class="preview-thumb-item" title="{{ $p->name }} — ₹{{ number_format((float)($p->special_price ?: $p->price), 2) }}">
                                            <img src="{{ $imgUrl }}" alt="{{ $p->name }}">
                                            <div class="thumb-price">₹{{ number_format((float)($p->special_price ?: $p->price), 0) }}</div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div style="font-size:12px; color:#94a3b8; font-style:italic;">
                                    No products currently meet this criteria or catalog is being populated.
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            @endforeach
        </div>

        {{-- Floating Save Bar --}}
        <div class="save-bar-bottom">
            <div>
                <strong><i class="fas fa-info-circle text-primary"></i> Changes apply immediately to Storefront</strong>
                <p>Hot Deals, New Arrivals, Best Sellers, and Featured Products can be customized anytime.</p>
            </div>
            <div>
                <button type="submit" class="btn-save-sections">
                    <i class="fas fa-check"></i> Save Changes
                </button>
            </div>
        </div>

    </form>

</div>
@endsection

@push('scripts')
<script>
// Live update title preview
function updateTitlePreview(key, val) {
    const el = document.getElementById('title-preview-' + key);
    if (el) el.textContent = val.trim() || 'Untitled Section';
}

function updateSubPreview(key, val) {
    const el = document.getElementById('sub-preview-' + key);
    if (el) el.textContent = val.trim() || '';
}

// Toggle section enabled state
function toggleSectionState(key, isChecked) {
    const card = document.getElementById('card-' + key);
    const statusText = document.getElementById('status-text-' + key);
    if (card) {
        if (isChecked) {
            card.classList.remove('disabled');
            if (statusText) {
                statusText.textContent = 'Visible on Home';
                statusText.className = 'status-label active';
            }
        } else {
            card.classList.add('disabled');
            if (statusText) {
                statusText.textContent = 'Hidden';
                statusText.className = 'status-label inactive';
            }
        }
    }
}

// Select mode (Auto / Manual)
function selectMode(key, mode) {
    const box = document.getElementById('manual-box-' + key);
    const card = document.getElementById('card-' + key);
    if (!card) return;

    const pills = card.querySelectorAll('.mode-pill');
    pills.forEach(p => {
        const rad = p.querySelector('input[type="radio"]');
        if (rad && rad.value === mode) {
            p.classList.add('selected');
            rad.checked = true;
        } else {
            p.classList.remove('selected');
        }
    });

    if (box) {
        if (mode === 'manual') {
            box.classList.remove('hidden');
        } else {
            box.classList.add('hidden');
        }
    }
}

// Live Product Search
let searchTimers = {};
function handleProductSearch(key, query) {
    clearTimeout(searchTimers[key]);
    const dropdown = document.getElementById('search-dropdown-' + key);
    if (!query || query.trim().length < 2) {
        if (dropdown) dropdown.style.display = 'none';
        return;
    }

    searchTimers[key] = setTimeout(() => {
        fetch(`{{ route('admin.appearance.sections.search-products') }}?q=` + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                if (!dropdown) return;
                if (!data.products || !data.products.length) {
                    dropdown.innerHTML = '<div style="padding:10px; font-size:12px; color:#94a3b8; text-align:center;">No matching products found</div>';
                    dropdown.style.display = 'block';
                    return;
                }

                let html = '';
                data.products.forEach(p => {
                    html += `
                        <div class="search-result-row" onclick='addManualProduct("${key}", ${p.id}, "${escapeHtml(p.name)}", "${p.image}")'>
                            <div class="search-prod-info">
                                <img src="${p.image}" class="search-prod-thumb" alt="">
                                <div>
                                    <div class="search-prod-title">${escapeHtml(p.name)}</div>
                                    <div style="font-size:11px; color:#64748b;">Code: ${p.code || 'N/A'}</div>
                                </div>
                            </div>
                            <div class="search-prod-price">₹${p.special_price || p.price}</div>
                        </div>
                    `;
                });
                dropdown.innerHTML = html;
                dropdown.style.display = 'block';
            })
            .catch(err => console.error(err));
    }, 250);
}

function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/[&<>"']/g, function(m) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m];
    });
}

// Add manual product to section
function addManualProduct(key, id, name, img) {
    const input = document.getElementById('input-prod-ids-' + key);
    const grid = document.getElementById('selected-grid-' + key);
    const countEl = document.getElementById('count-selected-' + key);
    const dropdown = document.getElementById('search-dropdown-' + key);

    if (dropdown) dropdown.style.display = 'none';

    let ids = input.value ? input.value.split(',').map(Number) : [];
    if (ids.includes(id)) {
        alert('This product is already selected.');
        return;
    }

    ids.push(id);
    input.value = ids.join(',');
    if (countEl) countEl.textContent = ids.length;

    const chip = document.createElement('div');
    chip.className = 'prod-chip';
    chip.id = `chip-${key}-${id}`;
    chip.innerHTML = `
        <img src="${img}" alt="">
        <span class="chip-name" title="${name}">${name}</span>
        <span class="chip-remove" onclick="removeManualProduct('${key}', ${id})" title="Remove">&times;</span>
    `;
    grid.appendChild(chip);
}

// Remove manual product from section
function removeManualProduct(key, id) {
    const input = document.getElementById('input-prod-ids-' + key);
    const chip = document.getElementById(`chip-${key}-${id}`);
    const countEl = document.getElementById('count-selected-' + key);

    if (chip) chip.remove();

    let ids = input.value ? input.value.split(',').map(Number) : [];
    ids = ids.filter(i => i !== id);
    input.value = ids.join(',');
    if (countEl) countEl.textContent = ids.length;
}

// Hide dropdowns when clicked outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.product-search-wrap')) {
        document.querySelectorAll('.search-results-dropdown').forEach(d => d.style.display = 'none');
    }
});
</script>
@endpush

@extends('layouts.admin')

@section('title', isset($product) ? 'Edit Product' : 'Add Product')

@push('styles')
<style>
/* ── Toast ── */
#toast-wrap { position:fixed; top:18px; right:18px; z-index:99999; min-width:260px; }
.toast-msg { display:flex; align-items:center; gap:8px; padding:10px 14px; border-radius:5px; margin-bottom:7px; font-size:13px; font-weight:500; box-shadow:0 2px 10px rgba(0,0,0,0.10); }
.toast-msg.success { background:#f0fdf4; border-left:4px solid #27ae60; color:#1a7a42; }
.toast-msg.error   { background:#fff0f0; border-left:4px solid #c0392b; color:#a93226; }
.toast-msg.info    { background:#f0f7ff; border-left:4px solid #2980b9; color:#1a5276; }

/* ── Page layout ── */
.page-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; flex-wrap:wrap; gap:10px; }
.page-header h2 { margin:0; font-weight:700; font-size:22px; }
.back-link { font-size:13px; color:#2980b9; text-decoration:none; display:inline-flex; align-items:center; gap:5px; }
.back-link:hover { text-decoration:underline; }

/* ── Form sections ── */
.form-section { background:#fff; border:1px solid #ddd; border-radius:6px; margin-bottom:18px; }
.section-header {
    padding:13px 18px; border-bottom:1px solid #eee; display:flex; align-items:center; justify-content:space-between;
    cursor:pointer; user-select:none;
}
.section-header h3 { margin:0; font-size:15px; font-weight:700; color:#222; }
.section-header .toggle-icon { font-size:14px; color:#888; transition:transform .2s; }
.section-header.collapsed .toggle-icon { transform:rotate(-90deg); }
.section-body { padding:20px 18px; }
.section-body.collapsed { display:none; }

/* ── Image gallery ── */
.img-gallery { display:flex; flex-wrap:wrap; gap:10px; min-height:80px; align-items:center; }
.img-thumb {
    position:relative; width:88px; height:88px; border-radius:5px; border:2px solid #e0e0e0;
    overflow:hidden; cursor:move; background:#fafafa;
}
.img-thumb img { width:100%; height:100%; object-fit:cover; }
.img-thumb .img-actions {
    position:absolute; top:0; left:0; right:0; bottom:0;
    background:rgba(0,0,0,0.45); display:flex; align-items:center; justify-content:center; gap:4px;
    opacity:0; transition:opacity .15s;
}
.img-thumb:hover .img-actions { opacity:1; }
.img-thumb .btn-img-action {
    width:30px; height:30px; border-radius:4px; border:none; cursor:pointer; font-size:12px;
    display:flex; align-items:center; justify-content:center; color:#fff;
}
.btn-img-primary   { background:#27ae60; }
.btn-img-remove     { background:#c0392b; }
.img-thumb.primary  { border-color:#27ae60; }
.img-thumb.primary::after {
    content:'★'; position:absolute; top:3px; right:4px; font-size:13px; color:#27ae60;
    text-shadow:0 1px 2px rgba(0,0,0,0.5);
}
.drop-zone {
    width:88px; height:88px; border:2px dashed #ccc; border-radius:5px;
    display:flex; align-items:center; justify-content:center; cursor:pointer;
    flex-direction:column; color:#aaa; font-size:12px; gap:4px;
    transition:border-color .15s, background .15s;
}
.drop-zone:hover, .drop-zone.drag-over { border-color:#3a7bd5; background:#f0f7ff; color:#3a7bd5; }
.drop-zone input[type=file] { display:none; }

/* ── Variation card ── */
.var-card {
    border:1px solid #e0e0e0; border-radius:6px; margin-bottom:14px; background:#fafafa;
    overflow:hidden;
}
.var-card-header {
    padding:10px 14px; display:flex; align-items:center; justify-content:space-between;
    background:#f5f5f5; border-bottom:1px solid #e8e8e8; cursor:pointer;
}
.var-card-title { font-weight:600; font-size:13px; color:#333; }
.var-card-actions { display:flex; gap:6px; }
.var-card-body { padding:14px; }
.var-grid { display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:12px; }
.var-full { grid-column:1 / -1; }

@media (max-width: 900px) {
    .var-grid { grid-template-columns:1fr 1fr; }
}

/* ── Related products tag input ── */
.related-tags { display:flex; flex-wrap:wrap; gap:6px; align-items:center; min-height:40px; padding:8px; border:1px solid #ddd; border-radius:4px; background:#fff; }
.tag-item { display:flex; align-items:center; gap:5px; padding:3px 8px; background:#e8f4fd; border:1px solid #bcd7ea; border-radius:4px; font-size:12px; color:#2980b9; }
.tag-item .tag-remove { cursor:pointer; color:#2980b9; font-weight:700; line-height:1; padding:0 1px; }
.tag-item .tag-remove:hover { color:#c0392b; }
.search-wrap { flex:1; min-width:180px; position:relative; }
.search-wrap input { width:100%; border:none; outline:none; font-size:13px; padding:4px; }
.search-results { position:absolute; top:100%; left:0; right:0; background:#fff; border:1px solid #ddd; border-radius:4px; box-shadow:0 4px 12px rgba(0,0,0,0.12); z-index:100; max-height:220px; overflow:auto; }
.search-result-item { padding:8px 12px; cursor:pointer; font-size:13px; display:flex; justify-content:space-between; align-items:center; }
.search-result-item:hover { background:#f0f7ff; }
.search-result-item .prod-code { font-size:11px; color:#999; font-family:monospace; }
.no-results { padding:8px 12px; color:#aaa; font-size:12px; }

/* ── WYSIWYG Editor ── */
.note-editor { border-radius:4px !important; }
.note-editor.note-frame { border-color:#ddd !important; }

/* ── Misc ── */
.form-control { font-size:13px; }
label { font-weight:600; font-size:13px; margin-bottom:4px; display:block; color:#444; }
.help-block { font-size:11px; color:#888; margin-top:2px; }
.section-note { font-size:12px; color:#888; margin-bottom:12px; background:#f9f9f9; padding:8px 12px; border-radius:4px; border-left:3px solid #ccc; }
.badge-section { font-size:11px; background:#e8f4fd; color:#2980b9; padding:2px 8px; border-radius:4px; }

/* ── Category tags ── */
.cat-tags { display:flex; flex-wrap:wrap; gap:6px; align-items:center; min-height:38px; padding:6px 8px; border:1px solid #ddd; border-radius:4px; background:#fff; }
.cat-tag { display:flex; align-items:center; gap:4px; padding:2px 8px; background:#f0fdf4; border:1px solid #b2e0c4; border-radius:4px; font-size:12px; color:#27ae60; cursor:pointer; }
.cat-tag:hover { background:#ffe8e8; border-color:#f5c6cb; color:#c0392b; }
.cat-tag .cat-tag-name { max-width:120px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.cat-dropdown-wrap { position:relative; }
.cat-dropdown { position:absolute; top:100%; left:0; right:0; background:#fff; border:1px solid #ddd; border-radius:4px; box-shadow:0 4px 12px rgba(0,0,0,0.12); z-index:100; max-height:200px; overflow:auto; display:none; }
.cat-dropdown.show { display:block; }
.cat-dropdown-item { padding:7px 12px; cursor:pointer; font-size:13px; display:flex; justify-content:space-between; align-items:center; }
.cat-dropdown-item:hover { background:#f0f7ff; }
.cat-dropdown-item.selected { background:#e8f4fd; color:#2980b9; }

/* ── Button bar ── */
.form-actions { display:flex; gap:10px; flex-wrap:wrap; }
.btn-save-exit { padding:9px 24px; font-weight:600; border:none; border-radius:5px; cursor:pointer; font-size:14px; }
.btn-save-exit.primary { background:#3a7bd5; color:#fff; }
.btn-save-exit.primary:hover { background:#2f6bc4; }
.btn-save-exit.secondary { background:#f0f0f0; color:#555; border:1px solid #ccc; }
.btn-save-exit.secondary:hover { background:#e8e8e8; }

/* ── Spinner in button ── */
.btn-save-exit:disabled { opacity:.6; cursor:not-allowed; }

/* ── Inventory toggle ── */
.inventory-extra { display:none; }
.inventory-extra.show { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:12px; }

/* ── Special price dates ── */
.date-range { display:grid; grid-template-columns:1fr 1fr; gap:12px; }

/* ── Image sortable ── */
.img-gallery.sortable-ghost { opacity:.35; }
.img-thumb.sortable-chosen { transform:scale(1.05); box-shadow:0 4px 16px rgba(0,0,0,0.2); }

/* ── Error messages ── */
.field-error { border-color:#c0392b !important; }
span.error-msg { font-size:11px; color:#c0392b; display:block; margin-top:2px; }
</style>
@endpush

@section('content')
<div id="toast-wrap"></div>
<div style="max-width:1100px; margin:0 auto;">

    {{-- Header --}}
    <div class="page-header">
        <h2>
            <i class="fas fa-{{ isset($product) ? 'pen' : 'plus' }}" style="font-size:18px; margin-right:6px;"></i>
            {{ isset($product) ? 'Edit Product' : 'Add Product' }}
        </h2>
        <a href="{{ route('admin.products.index') }}" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>

    {{-- Form --}}
    <form id="product-form" enctype="multipart/form-data">

        {{-- ═══ BASIC INFO ═══ --}}
        <div class="form-section">
            <div class="section-header" onclick="toggleSection(this)">
                <h3><i class="fas fa-info-circle" style="margin-right:6px; color:#555;"></i> Basic Information</h3>
                <span class="toggle-icon">▾</span>
            </div>
            <div class="section-body">

                {{-- Product Images --}}
                <div class="form-group">
                    <label>Product Images <span style="font-weight:400; color:#888;">(multiple, drag to reorder)</span></label>
                    <div class="img-gallery" id="product-gallery">
                        {{-- Existing images for edit --}}
                        @if(isset($product) && $product->images->count())
                            @foreach($product->images as $img)
                                <div class="img-thumb {{ $img->is_primary ? 'primary' : '' }}" data-id="{{ $img->id }}">
                                    <img src="{{ $img->url }}" alt="">
                                    <div class="img-actions">
                                        <button type="button" class="btn-img-action btn-img-primary" title="Set Primary" onclick="setPrimaryImg(this)"><i class="fas fa-star"></i></button>
                                        <button type="button" class="btn-img-action btn-img-remove" title="Remove" onclick="removeImg(this)"><i class="fas fa-trash"></i></button>
                                    </div>
                                    <input type="hidden" name="existing_images[]" value="{{ $img->id }}">
                                </div>
                            @endforeach
                        @endif
                        <label class="drop-zone" id="drop-zone" title="Click or drag to upload">
                            <i class="fas fa-plus" style="font-size:18px;"></i>
                            <span>Add</span>
                            <input type="file" accept="image/*" multiple id="file-input">
                        </label>
                    </div>
                    <p class="help-block">First image is automatically set as primary. Click star to change. Drag to reorder.</p>
                </div>

                {{-- Product Name --}}
                <div class="form-group">
                    <label>Product Name <span style="color:#c0392b;">*</span></label>
                    <input type="text" id="prod-name" class="form-control" placeholder="e.g. Premium Cotton T-Shirt"
                        value="{{ $product->name ?? '' }}" required>
                    <span class="error-msg" id="err-name"></span>
                </div>

                {{-- Code + URL row --}}
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label>Product Code</label>
                        <input type="text" id="prod-code" class="form-control" placeholder="e.g. TSHIRT-001"
                            value="{{ $product->code ?? '' }}">
                        <p class="help-block">Unique identifier for the product</p>
                    </div>
                    <div class="form-group">
                        <label>URL Slug</label>
                        <input type="text" id="prod-url" class="form-control" placeholder="e.g. premium-cotton-tee"
                            value="{{ $product->seo_url ?? '' }}">
                        <p class="help-block">Leave empty to auto-generate from name</p>
                    </div>
                </div>

                {{-- Description (WYSIWYG) --}}
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="description" class="form-control summernote" rows="5"
                        placeholder="Write a detailed product description…">{{ $product->description ?? '' }}</textarea>
                </div>

                {{-- Categories --}}
                <div class="form-group">
                    <label>Categories <span style="font-weight:400; color:#888;">(click to toggle, multiple)</span></label>
                    <div class="cat-dropdown-wrap">
                        <div class="cat-tags" id="cat-tags-box">
                            {{-- Selected cats render inline --}}
                            @if(isset($product) && $product->categories->count())
                                @foreach($product->categories as $cat)
                                    <span class="cat-tag" data-id="{{ $cat->id }}">
                                        <span class="cat-tag-name">{{ $cat->name }}</span>
                                        <span class="cat-tag-remove" onclick="removeCat({{ $cat->id }})">&times;</span>
                                    </span>
                                @endforeach
                            @endif
                            <div class="search-wrap">
                                <input type="text" id="cat-search" placeholder="+ Add category…" autocomplete="off">
                            </div>
                        </div>
                        <div class="cat-dropdown" id="cat-dropdown">
                            @foreach($categories as $cat)
                                <div class="cat-dropdown-item {{ isset($product) && $product->categories->contains('id', $cat->id) ? 'selected' : '' }}"
                                    data-id="{{ $cat->id }}" onclick="toggleCat(this)">
                                    {{ $cat->name }}
                                    @if($cat->parent) <span style="color:#aaa; font-size:11px;"> ({{ $cat->parent->name }})</span> @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <input type="hidden" id="selected-categories" name="categories" value="{{ isset($product) ? $product->categories->pluck('id')->join(',') : '' }}">
                </div>

                {{-- Providers --}}
                <div class="form-group">
                    <label>Providers <span style="font-weight:400; color:#888;">(click to toggle, multiple)</span></label>
                    <div class="cat-dropdown-wrap" id="provider-dropdown-wrap">
                        <div class="cat-tags" id="provider-tags-box">
                            @if(isset($product) && $product->providers && $product->providers->count())
                                @foreach($product->providers as $prov)
                                    <span class="cat-tag" style="background:#fff3e0; border-color:#f0c9a0; color:#e67e22;" data-id="{{ $prov->id }}">
                                        <span class="cat-tag-name"><i class="fas fa-truck" style="font-size:10px; margin-right:3px;"></i>{{ $prov->name }}</span>
                                        <span class="cat-tag-remove" onclick="removeProviderTag({{ $prov->id }})">&times;</span>
                                    </span>
                                @endforeach
                            @endif
                            <div class="search-wrap">
                                <input type="text" id="provider-search" placeholder="+ Add provider…" autocomplete="off">
                            </div>
                        </div>
                        <div class="cat-dropdown" id="provider-dropdown">
                            @foreach($providers as $prov)
                                <div class="cat-dropdown-item {{ isset($product) && $product->providers && $product->providers->contains('id', $prov->id) ? 'selected' : '' }}"
                                    data-id="{{ $prov->id }}" onclick="toggleProvider(this)">
                                    <span><i class="fas fa-truck" style="font-size:11px; margin-right:5px; color:#e67e22;"></i>{{ $prov->name }}</span>
                                    @if($prov->city) <span style="color:#aaa; font-size:11px;"> — {{ $prov->city }}</span> @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <input type="hidden" id="selected-providers" name="providers" value="{{ isset($product) && $product->providers ? $product->providers->pluck('id')->join(',') : '' }}">
                    <p class="help-block">Assign providers who supply this product.</p>
                </div>

            </div>
        </div>

        {{-- ═══ PRICING ═══ --}}
        <div class="form-section">
            <div class="section-header" onclick="toggleSection(this)">
                <h3><i class="fas fa-tags" style="margin-right:6px; color:#555;"></i> Pricing</h3>
                <span class="toggle-icon fas fa-chevron-down"></span>
            </div>
            <div class="section-body">
                <p class="section-note">Set base pricing for this product. This applies when no variations exist, or as default for new variations.</p>
                <div style="display:grid; grid-template-columns:1fr 1fr 1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label>Price <span style="color:#c0392b;">*</span></label>
                        <input type="number" id="base-price" name="price" class="form-control" placeholder="0.00" step="0.01" min="0"
                            value="{{ $product->price ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label>Special Price</label>
                        <input type="number" id="base-special-price" name="special_price" class="form-control" placeholder="0.00" step="0.01" min="0"
                            value="{{ $product->special_price ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label>Special Price Start</label>
                        <input type="date" id="base-special-start" name="special_price_start" class="form-control"
                            value="{{ isset($product->special_price_start) ? $product->special_price_start->format('Y-m-d') : '' }}">
                    </div>
                    <div class="form-group">
                        <label>Special Price End</label>
                        <input type="date" id="base-special-end" name="special_price_end" class="form-control"
                            value="{{ isset($product->special_price_end) ? $product->special_price_end->format('Y-m-d') : '' }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ VARIATIONS ═══ --}}
        <div class="form-section">
            <div class="section-header">
                <h3><i class="fas fa-layer-group" style="margin-right:6px; color:#555;"></i> Variations</h3>
                <div style="display:flex; gap:10px; align-items:center;">
                    <span class="badge-section" id="var-count">{{ isset($product) ? $product->variations->count() : 0 }} variation(s)</span>
                    <button type="button" class="btn btn-xs btn-default" style="background:#27ae60; color:#fff; border:none; padding:4px 12px; border-radius:4px; font-size:12px;" onclick="addVariation()">
                        <i class="fas fa-plus"></i> Add Variation
                    </button>
                    <span class="toggle-icon fas fa-chevron-down" onclick="toggleSection(this.parentElement.parentElement)" style="cursor:pointer;"></span>
                </div>
            </div>
            <div class="section-body" id="variations-body">
                <p class="section-note">Add product variations (e.g. size/color combinations). Each gets its own price, special price, and stock. If not set, the base pricing above is used.</p>
                <div id="variations-list">
                    @if(isset($product) && $product->variations->count())
                        @foreach($product->variations as $var)
                            @include('admin.products.partials.variation-row', ['var' => $var])
                        @endforeach
                    @endif
                </div>
                @if(!isset($product) || $product->variations->isEmpty())
                    <div id="no-variations-msg" style="text-align:center; padding:30px; color:#aaa; border:1px dashed #ddd; border-radius:5px;">
                        <i class="fas fa-layer-group" style="font-size:28px; margin-bottom:8px; display:block;"></i>
                        No variations yet. Click <strong>Add Variation</strong> to create one.
                    </div>
                @endif
            </div>
        </div>

        {{-- ═══ INVENTORY ═══ --}}
        <div class="form-section">
            <div class="section-header" onclick="toggleSection(this)">
                <h3><i class="fas fa-warehouse" style="margin-right:6px; color:#555;"></i> Inventory</h3>
                <span class="toggle-icon">▾</span>
            </div>
            <div class="section-body">
                <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
                    <label style="margin:0; font-weight:600;">Manage Inventory?</label>
                    <input type="checkbox" id="manage-inventory" name="manage_inventory"
                        {{ (isset($product) && $product->manage_inventory) ? 'checked' : '' }}
                        onchange="toggleInventory()">
                    <span style="font-size:12px; color:#888;">Enable stock tracking for this product</span>
                </div>
                <div class="inventory-extra {{ isset($product) && $product->manage_inventory ? 'show' : '' }}" id="inventory-fields">
                    <div class="form-group">
                        <label>Stock Status</label>
                        <select id="stock-status" name="stock_status" class="form-control">
                            <option value="in_stock" {{ (isset($product) && $product->stock_status === 'in_stock') ? 'selected' : '' }}>In Stock</option>
                            <option value="out_of_stock" {{ (isset($product) && $product->stock_status === 'out_of_stock') ? 'selected' : '' }}>Out of Stock</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Quantity</label>
                        <input type="number" id="prod-qty" name="qty" class="form-control" min="0"
                            value="{{ $product->qty ?? 0 }}">
                    </div>
                </div>
                @if(!isset($product) || !$product->manage_inventory)
                    <p class="help-block" style="color:#888; margin-top:4px;">
                        <i class="fas fa-info-circle"></i> When inventory management is disabled, stock status is always "In Stock".
                        Enable it to track quantities and show out-of-stock status.
                    </p>
                @endif
            </div>
        </div>

        {{-- ═══ SEO ═══ --}}
        <div class="form-section">
            <div class="section-header" onclick="toggleSection(this)">
                <h3><i class="fas fa-search" style="margin-right:6px; color:#555;"></i> Product SEO</h3>
                <span class="toggle-icon">▾</span>
            </div>
            <div class="section-body">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label>Meta Title</label>
                        <input type="text" id="meta-title" name="meta_title" class="form-control"
                            placeholder="SEO title (defaults to product name)"
                            value="{{ $product->meta_title ?? '' }}" maxlength="255">
                        <p class="help-block">Recommended: 50-60 characters</p>
                    </div>
                    <div class="form-group">
                        <label>URL / Slug</label>
                        <input type="text" id="seo-url" class="form-control"
                            placeholder="auto-generated-from-name"
                            value="{{ $product->seo_url ?? '' }}">
                    </div>
                </div>
                <div class="form-group">
                    <label>Meta Description</label>
                    <textarea id="meta-description" name="meta_description" class="form-control" rows="3"
                        placeholder="Brief description for search engines (150-160 characters recommended)"
                        maxlength="320">{{ $product->meta_description ?? '' }}</textarea>
                    <p class="help-block">Character count: <span id="meta-desc-count">0</span>/320</p>
                </div>
            </div>
        </div>

        {{-- ═══ RELATED PRODUCTS ═══ --}}
        <div class="form-section">
            <div class="section-header" onclick="toggleSection(this)">
                <h3><i class="fas fa-link" style="margin-right:6px; color:#555;"></i> Related Products</h3>
                <span class="toggle-icon">▾</span>
            </div>
            <div class="section-body">
                <div class="related-tags" id="related-tags-box">
                    @if(isset($product) && $product->relatedProducts->count())
                        @foreach($product->relatedProducts as $rel)
                            <span class="tag-item" data-id="{{ $rel->id }}">
                                {{ $rel->name }}
                                <span class="tag-remove" onclick="removeRelated({{ $rel->id }})">&times;</span>
                            </span>
                        @endforeach
                    @endif
                    <div class="search-wrap">
                        <input type="text" id="related-search" placeholder="Type to search and add related products…"
                            autocomplete="off"
                            data-exclude="{{ isset($product) ? $product->id : '' }}">
                        <div class="search-results" id="related-results" style="display:none;"></div>
                    </div>
                </div>
                <input type="hidden" id="selected-related" name="related_products" value="{{ isset($product) ? $product->relatedProducts->pluck('id')->join(',') : '' }}">
                <input type="hidden" id="deleted-variations" name="deleted_variations" value="">
                <p class="help-block">Search and add related products. Click × to remove.</p>
            </div>
        </div>

        {{-- ═══ SETTINGS ═══ --}}
        <div class="form-section">
            <div class="section-header" onclick="toggleSection(this)">
                <h3><i class="fas fa-sliders-h" style="margin-right:6px; color:#555;"></i> Settings</h3>
                <span class="toggle-icon">▾</span>
            </div>
            <div class="section-body">
                <div style="display:flex; gap:30px; flex-wrap:wrap;">
                    <div style="display:flex; align-items:center; gap:8px;">
                        <input type="checkbox" id="is-active" name="is_active" {{ (isset($product) && $product->is_active) || !isset($product) ? 'checked' : '' }}>
                        <label style="margin:0; font-weight:600;">Active</label>
                        <span class="help-block" style="margin:0;">Product visible on store</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <input type="checkbox" id="is-featured" name="is_featured" {{ (isset($product) && $product->is_featured) ? 'checked' : '' }}>
                        <label style="margin:0; font-weight:600;">Featured</label>
                        <span class="help-block" style="margin:0;">Show in featured section</span>
                    </div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <input type="checkbox" id="is-returnable" name="is_returnable" {{ (isset($product) && $product->is_returnable) ? 'checked' : '' }}>
                        <label style="margin:0; font-weight:600;">Returnable</label>
                        <span class="help-block" style="margin:0;">Customers can request a return</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ ACTION BUTTONS ═══ --}}
        <div style="display:flex; gap:12px; margin-bottom:30px; align-items:center; flex-wrap:wrap;">
            <button type="button" id="btn-save" class="btn-save-exit primary" onclick="saveProduct('save')">
                <i class="fas fa-save"></i> Save Product
            </button>
            <button type="button" id="btn-save-exit" class="btn-save-exit secondary" onclick="saveProduct('save_exit')">
                <i class="fas fa-sign-out-alt"></i> Save & Exit
            </button>
            <a href="{{ route('admin.products.index') }}" class="btn-save-exit secondary" style="text-decoration:none;">
                Cancel
            </a>
            <span id="form-status" style="font-size:12px; color:#888;"></span>
        </div>

    </form>
</div>

{{-- Hidden template for new variation --}}
<template id="variation-template">
    <div class="var-card" data-var-id="">
        <div class="var-card-header" onclick="toggleVarBody(this)">
            <div style="display:flex; align-items:center; gap:10px; flex:1;">
                <i class="fas fa-chevron-down var-toggle-icon" style="font-size:11px; color:#888; transition:transform .2s;"></i>
                <span class="var-card-title">New Variation</span>
            </div>
            <button type="button" class="btn btn-xs" style="background:#c0392b; color:#fff; border:none; padding:3px 10px; border-radius:3px; font-size:11px;" onclick="removeVariation(this, event)">
                <i class="fas fa-trash"></i> Remove
            </button>
        </div>
        <div class="var-card-body">
            <div class="var-grid">
                <div class="form-group var-full">
                    <label>Variation Name <span style="color:#c0392b;">*</span></label>
                    <input type="text" class="form-control var-name" placeholder="e.g. Red / Large">
                </div>
                <div class="form-group">
                    <label>SKU</label>
                    <input type="text" class="form-control var-sku" placeholder="SKU code">
                </div>
                <div class="form-group">
                    <label>Price <span style="color:#c0392b;">*</span></label>
                    <input type="number" class="form-control var-price" placeholder="0.00" step="0.01" min="0">
                </div>
                <div class="form-group">
                    <label>Special Price</label>
                    <input type="number" class="form-control var-special" placeholder="0.00" step="0.01" min="0">
                </div>
                <div class="form-group" style="grid-column:1 / -1;">
                    <label>Special Price Period <span style="font-weight:400; color:#888;">(optional)</span></label>
                    <div class="date-range">
                        <div>
                            <label style="font-size:12px; color:#666;">Start Date</label>
                            <input type="date" class="form-control var-special-start">
                        </div>
                        <div>
                            <label style="font-size:12px; color:#666;">End Date</label>
                            <input type="date" class="form-control var-special-end">
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Stock Status</label>
                    <select class="form-control var-stock-status">
                        <option value="in_stock">In Stock</option>
                        <option value="out_of_stock">Out of Stock</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Quantity</label>
                    <input type="number" class="form-control var-qty" placeholder="0" min="0">
                </div>
                <div class="form-group var-full">
                    <label>Variation Images</label>
                    <div class="img-gallery var-img-gallery" id="">
                        <label class="drop-zone var-drop-zone" title="Click or drag to upload">
                            <i class="fas fa-plus" style="font-size:18px;"></i><span>Add</span>
                            <input type="file" accept="image/*" multiple class="var-file-input">
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote.min.js"></script>
<script>
var isEditMode = {{ isset($product) ? 'true' : 'false' }};
var productId  = {{ isset($product) ? $product->id : 'null' }};
var existingImages = [];
var selectedCategories = [];
var selectedProviders  = [];
var selectedRelated   = [];
var variationCounter  = 0;

// Pre-fill from server-side data
@if(isset($product))
    existingImages = {!! json_encode($product->images->map(fn($i) => ['id' => $i->id, 'url' => $i->url, 'is_primary' => $i->is_primary])) !!};
    selectedCategories = {!! json_encode($product->categories->pluck('id')->toArray()) !!};
    selectedProviders  = {!! json_encode($product->providers->pluck('id')->toArray()) !!};
    selectedRelated    = {!! json_encode($product->relatedProducts->pluck('id')->toArray()) !!};
@endif

$(function () {
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

    // Initialize WYSIWYG
    $('.summernote').summernote({
        height: 180,
        toolbar: [
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['para', ['paragraph']],
            ['insert', ['link', 'picture']],
            ['view', ['fullscreen', 'codeview']],
        ]
    });

    // Image drop zone
    var $dropZone  = $('#drop-zone');
    var $fileInput  = $('#file-input');

    $dropZone.on('click', function () { $fileInput.click(); });

    $fileInput.on('change', function () { handleFiles(this.files); });

    $dropZone.on('dragover', function (e) { e.preventDefault(); $(this).addClass('drag-over'); });
    $dropZone.on('dragleave', function () { $(this).removeClass('drag-over'); });
    $dropZone.on('drop', function (e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
        handleFiles(e.originalEvent.dataTransfer.files);
    });

    // Make gallery sortable
    initSortableGallery('#product-gallery');

    // Category search
    $('#cat-search').on('focus', function () {
        var q = $(this).val().toLowerCase();
        $('#cat-dropdown').find('.cat-dropdown-item').each(function () {
            var match = $(this).text().toLowerCase().includes(q);
            $(this).toggle(match);
        });
        $('#cat-dropdown').addClass('show');
    });
    $('#cat-search').on('input', function () {
        var q = $(this).val().toLowerCase();
        $('#cat-dropdown').find('.cat-dropdown-item').each(function () {
            var match = $(this).text().toLowerCase().includes(q);
            $(this).toggle(match);
        });
    });
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.cat-dropdown-wrap').length) {
            $('#cat-dropdown').removeClass('show');
        }
        if (!$(e.target).closest('#provider-dropdown-wrap').length) {
            $('#provider-dropdown').removeClass('show');
        }
    });

    // Provider search
    $('#provider-search').on('focus', function () {
        var q = $(this).val().toLowerCase();
        $('#provider-dropdown').find('.cat-dropdown-item').each(function () {
            var match = $(this).text().toLowerCase().includes(q);
            $(this).toggle(match);
        });
        $('#provider-dropdown').addClass('show');
    });
    $('#provider-search').on('input', function () {
        var q = $(this).val().toLowerCase();
        $('#provider-dropdown').find('.cat-dropdown-item').each(function () {
            var match = $(this).text().toLowerCase().includes(q);
            $(this).toggle(match);
        });
    });

    // Related product search
    var relatedTimer;
    $('#related-search').on('input', function () {
        clearTimeout(relatedTimer);
        var q = $(this).val();
        var exclude = $(this).data('exclude');
        var $results = $('#related-results');

        if (q.length < 1) { $results.hide(); return; }

        relatedTimer = setTimeout(function () {
            $.get('{{ route('admin.products.search') }}', { q: q, exclude: exclude }, function (res) {
                if (!res.success || !res.data.length) {
                    $results.html('<div class="no-results">No products found</div>').show();
                    return;
                }
                var html = '';
                res.data.forEach(function (p) {
                    var already = selectedRelated.includes(p.id);
                    html += '<div class="search-result-item' + (already ? ' disabled' : '') + '" data-id="' + p.id + '" data-name="' + p.name + '" onclick="addRelated(this)">' +
                        '<span>' + p.name + '</span>' +
                        '<span class="prod-code">' + (p.code || '') + '</span>' +
                        '</div>';
                });
                $results.html(html).show();
            });
        }, 250);
    });
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.related-tags').length) { $('#related-results').hide(); }
    });

    // Meta description char count
    $('#meta-description').on('input', function () {
        $('#meta-desc-count').text($(this).val().length);
    }).trigger('input');

});

// ── Image handling ──
function handleFiles(files) {
    for (var i = 0; i < files.length; i++) {
        uploadFile(files[i]);
    }
}

function uploadFile(file) {
    if (!file.type.startsWith('image/')) return;

    var fd = new FormData();
    fd.append('image', file);
    if (productId) fd.append('product_id', productId);

    $.ajax({
        url: '{{ route('admin.products.upload-image') }}',
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function (res) {
            if (!res.success) return;
            existingImages.push({ id: res.image_id, url: res.image_url, is_primary: res.is_primary });
            renderGallery();
        },
        error: function (xhr) {
            var err = xhr.responseJSON && xhr.responseJSON.message;
            if (xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.image) {
                err = xhr.responseJSON.errors.image[0];
            }
            toast(err || 'Image upload failed.', 'error');
        }
    });
}

function renderGallery() {
    var $gallery = $('#product-gallery');
    $gallery.find('.img-thumb').remove();
    $gallery.find('.drop-zone').remove();

    existingImages.forEach(function (img, idx) {
        var html = '<div class="img-thumb' + (img.is_primary ? ' primary' : '') + '" data-id="' + img.id + '">' +
            '<img src="' + img.url + '" alt="">' +
            '<div class="img-actions">' +
            '<button type="button" class="btn-img-action btn-img-primary" title="Set Primary" onclick="setPrimaryImg(this)"><i class="fas fa-star"></i></button>' +
            '<button type="button" class="btn-img-action btn-img-remove" title="Remove" onclick="removeImg(this)"><i class="fas fa-trash"></i></button>' +
            '</div>' +
            '<input type="hidden" name="existing_images[]" value="' + img.id + '">' +
            '</div>';
        $gallery.prepend(html);
    });

    $gallery.append('<label class="drop-zone" id="drop-zone" title="Click or drag to upload">' +
        '<i class="fas fa-plus" style="font-size:18px;"></i><span>Add</span>' +
        '<input type="file" accept="image/*" multiple id="file-input"></label>');

    // Rebind drop zone
    var $dz = $('#drop-zone');
    $dz.on('click', function () { $('#file-input').click(); });
    $dz.on('dragover', function (e) { e.preventDefault(); $(this).addClass('drag-over'); });
    $dz.on('dragleave', function () { $(this).removeClass('drag-over'); });
    $dz.on('drop', function (e) {
        e.preventDefault();
        $(this).removeClass('drag-over');
        handleFiles(e.originalEvent.dataTransfer.files);
    });
    $('#file-input').off('change').on('change', function () { handleFiles(this.files); });

    // Make gallery sortable
    initSortableGallery('#product-gallery');
}

function initSortableGallery(selector) {
    var el = document.querySelector(selector);
    if (!el) return;
    var sortable = Sortable.create(el, {
        animation: 150,
        ghostClass: 'sortable-ghost',
        chosenClass: 'sortable-chosen',
        draggable: '.img-thumb',
        filter: '.drop-zone',
        onEnd: function (evt) {
            var order = [];
            $(selector + ' .img-thumb').each(function () {
                var id = $(this).data('id');
                order.push(id);
            });
            // Reorder existingImages
            var reordered = [];
            order.forEach(function (id) {
                var found = existingImages.find(function (x) { return x.id == id; });
                if (found) reordered.push(found);
            });
            // First becomes primary
            if (reordered.length > 0) reordered[0].is_primary = true;
            existingImages = reordered;
            renderGallery();
        }
    });
}

function toggleVarBody(header) {
    var $body = $(header).next('.var-card-body');
    var $icon = $(header).find('.var-toggle-icon');
    if ($body.is(':visible')) {
        $body.slideUp();
        $icon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
    } else {
        $body.slideDown();
        $icon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
    }
}

function setPrimaryImg(btn) {
    var $thumb = $(btn).closest('.img-thumb');
    var id = $thumb.data('id');
    existingImages.forEach(function (img) { img.is_primary = (img.id == id); });
    renderGallery();
}

function removeImg(btn) {
    var $thumb = $(btn).closest('.img-thumb');
    var id = $thumb.data('id');

    // Check if it's a variation image
    var $varCard = $thumb.closest('.var-card');
    if ($varCard.length) {
        // It's a variation image!
        var $deletedInput = $varCard.find('.var-deleted-images');
        if ($deletedInput.length) {
            var currentVal = $deletedInput.val();
            $deletedInput.val(currentVal ? currentVal + ',' + id : id);
        }
        $thumb.remove();

        // Update remaining variation thumbs: first becomes primary
        var $gallery = $thumb.closest('.var-img-gallery');
        $gallery.find('.img-thumb').each(function(index) {
            $(this).toggleClass('primary', index === 0);
        });
    } else {
        // It's a product image!
        existingImages = existingImages.filter(function (img) { return img.id != id; });
        renderGallery();
    }
}

// ── Section toggle ──
function toggleSection(header) {
    $(header).toggleClass('collapsed');
    $(header).next('.section-body').slideToggle();
}

// ── Inventory toggle ──
function toggleInventory() {
    var checked = $('#manage-inventory').is(':checked');
    $('#inventory-fields').toggleClass('show', checked);
}

// ── Categories ──
function toggleCat(el) {
    var id = $(el).data('id');
    var name = $(el).text().trim();

    if ($(el).hasClass('selected')) {
        selectedCategories = selectedCategories.filter(function (c) { return c != id; });
        $(el).removeClass('selected');
        $('#cat-tags-box').find('.cat-tag[data-id="' + id + '"]').remove();
    } else {
        selectedCategories.push(id);
        $(el).addClass('selected');
        var tag = '<span class="cat-tag" data-id="' + id + '">' +
            '<span class="cat-tag-name">' + name + '</span>' +
            '<span class="cat-tag-remove" onclick="removeCat(' + id + ')">&times;</span>' +
            '</span>';
        $('#cat-search').closest('.search-wrap').before(tag);
    }

    $('#selected-categories').val(selectedCategories.join(','));
}

function removeCat(id) {
    selectedCategories = selectedCategories.filter(function (c) { return c != id; });
    $('#cat-tags-box').find('.cat-tag[data-id="' + id + '"]').remove();
    $('#cat-dropdown').find('.cat-dropdown-item[data-id="' + id + '"]').removeClass('selected');
    $('#selected-categories').val(selectedCategories.join(','));
}

// ── Providers ──
function toggleProvider(el) {
    var id = $(el).data('id');
    var nameText = $(el).find('span').first().text().trim();

    if ($(el).hasClass('selected')) {
        selectedProviders = selectedProviders.filter(function (p) { return p != id; });
        $(el).removeClass('selected');
        $('#provider-tags-box').find('.cat-tag[data-id="' + id + '"]').remove();
    } else {
        selectedProviders.push(id);
        $(el).addClass('selected');
        var tag = '<span class="cat-tag" style="background:#fff3e0; border-color:#f0c9a0; color:#e67e22;" data-id="' + id + '">' +
            '<i class="fas fa-truck" style="font-size:10px; margin-right:3px;"></i>' +
            '<span class="cat-tag-name">' + nameText + '</span>' +
            '<span class="cat-tag-remove" onclick="removeProviderTag(' + id + ')">&times;</span>' +
            '</span>';
        $('#provider-search').closest('.search-wrap').before(tag);
    }

    $('#selected-providers').val(selectedProviders.join(','));
}

function removeProviderTag(id) {
    selectedProviders = selectedProviders.filter(function (p) { return p != id; });
    $('#provider-tags-box').find('.cat-tag[data-id="' + id + '"]').remove();
    $('#provider-dropdown').find('.cat-dropdown-item[data-id="' + id + '"]').removeClass('selected');
    $('#selected-providers').val(selectedProviders.join(','));
}

// ── Related products ──
function addRelated(el) {
    var id   = $(el).data('id');
    var name = $(el).data('name');

    if (selectedRelated.includes(id)) return;
    selectedRelated.push(id);

    var tag = '<span class="tag-item" data-id="' + id + '">' + name +
        '<span class="tag-remove" onclick="removeRelated(' + id + ')">&times;</span></span>';
    $('#related-search').closest('.search-wrap').before(tag);
    $('#selected-related').val(selectedRelated.join(','));
    $(el).addClass('disabled').hide();
}

function removeRelated(id) {
    selectedRelated = selectedRelated.filter(function (r) { return r != id; });
    $('#related-tags-box').find('.tag-item[data-id="' + id + '"]').remove();
    $('#selected-related').val(selectedRelated.join(','));
    $('#related-results').find('.search-result-item[data-id="' + id + '"]').removeClass('disabled').show();
}

// ── Variations ──
function addVariation() {
    var $template = $('#variation-template').html();
    variationCounter++;
    var $html = $($template.replace(/__INDEX__/g, variationCounter));
    $html.attr('data-var-id', 'new_' + variationCounter);
    $html.find('.var-card-title').text('New Variation #' + variationCounter);

    $('#variations-list').append($html);
    $('#no-variations-msg').hide();
    updateVarCount();

    // Bind variation file inputs
    $html.find('.var-file-input').on('change', function () {
        var files = this.files;
        var varId = $(this).closest('.var-card').attr('data-var-id');
        for (var i = 0; i < files.length; i++) {
            uploadVariationFile(files[i], varId);
        }
    });

    // Initialize sortable for variation gallery
    setTimeout(function () {
        var varGallery = $html[0].querySelector('.var-img-gallery');
        if (varGallery) {
            Sortable.create(varGallery, {
                animation: 150,
                ghostClass: 'sortable-ghost',
                draggable: '.img-thumb',
                filter: '.drop-zone',
            });
        }
    }, 100);
}

function removeVariation(btn, e) {
    e.stopPropagation();
    var $card = $(btn).closest('.var-card');
    var varId = $card.attr('data-var-id');

    // If existing variation, track for deletion
    if (varId && !varId.startsWith('new_')) {
        var deleted = $('#deleted-variations').val();
        $('#deleted-variations').val(deleted ? deleted + ',' + varId : varId);
    }

    $card.slideUp(250, function () {
        $(this).remove();
        updateVarCount();
        var $list = $('#variations-list');
        if ($list.find('.var-card').length === 0) {
            $list.append('<div id="no-variations-msg" style="text-align:center; padding:30px; color:#aaa; border:1px dashed #ddd; border-radius:5px;">' +
                '<i class="fas fa-layer-group" style="font-size:28px; margin-bottom:8px; display:block;"></i>' +
                'No variations yet. Click <strong>Add Variation</strong> to create one.' +
                '</div>');
        }
    });
}

function updateVarCount() {
    var n = $('#variations-list .var-card').length;
    $('#var-count').text(n + ' variation(s)');
}

// Variation image upload
function uploadVariationFile(file, varId) {
    if (!file.type.startsWith('image/')) return;

    var fd = new FormData();
    fd.append('image', file);
    if (varId && !varId.startsWith('new_')) fd.append('variation_id', varId);

    $.ajax({
        url: '{{ route('admin.products.upload-image') }}',
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function (res) {
            if (!res.success) return;
            var $card = $('.var-card[data-var-id="' + varId + '"]');
            var $gallery = $card.find('.var-img-gallery');
            var $dz = $gallery.find('.drop-zone');
            var imgHtml = '<div class="img-thumb' + (res.is_primary ? ' primary' : '') + '" data-id="' + res.image_id + '">' +
                '<img src="' + res.image_url + '" alt="">' +
                '<div class="img-actions">' +
                '<button type="button" class="btn-img-action btn-img-primary" title="Set Primary" onclick="setPrimaryImg(this)"><i class="fas fa-star"></i></button>' +
                '<button type="button" class="btn-img-action btn-img-remove" title="Remove" onclick="removeImg(this)"><i class="fas fa-trash"></i></button>' +
                '</div>' +
                '<input type="hidden" name="var_' + varId + '_images[]" value="' + res.image_id + '">' +
                '</div>';
            $gallery.find('.drop-zone').before(imgHtml);
        },
        error: function (xhr) {
            var err = xhr.responseJSON && xhr.responseJSON.message;
            if (xhr.responseJSON && xhr.responseJSON.errors && xhr.responseJSON.errors.image) {
                err = xhr.responseJSON.errors.image[0];
            }
            toast(err || 'Variation image upload failed.', 'error');
        }
    });
}

// ── Save ──
function saveProduct(action) {
    clearErrors();

    var name = $('#prod-name').val().trim();
    if (!name) { $('#prod-name').addClass('field-error'); $('#err-name').text('Product name is required.').show(); $('#prod-name').focus(); return; }

    var url = isEditMode
        ? '{{ url('admin/products') }}/' + productId
        : '{{ route('admin.products.store') }}';

    var fd = new FormData();
    fd.append('name', name);
    fd.append('code', $('#prod-code').val().trim());
    fd.append('description', $('#description').summernote('code'));
    fd.append('url', $('#seo-url').val().trim());
    fd.append('meta_title', $('#meta-title').val().trim());
    fd.append('meta_description', $('#meta-description').val().trim());
    fd.append('price', $('#base-price').val());
    fd.append('special_price', $('#base-special-price').val());
    fd.append('special_price_start', $('#base-special-start').val());
    fd.append('special_price_end', $('#base-special-end').val());
    fd.append('manage_inventory', $('#manage-inventory').is(':checked') ? '1' : '0');
    fd.append('stock_status', $('#stock-status').val());
    fd.append('qty', $('#prod-qty').val());
    fd.append('is_featured', $('#is-featured').is(':checked') ? '1' : '0');
    fd.append('is_active', $('#is-active').is(':checked') ? '1' : '0');
    fd.append('is_returnable', $('#is-returnable').is(':checked') ? '1' : '0');
    fd.append('categories', $('#selected-categories').val());
    fd.append('providers', $('#selected-providers').val());
    fd.append('related_products', $('#selected-related').val());
    fd.append('deleted_variations', $('#deleted-variations').val() || '');

    if (isEditMode) fd.append('_method', 'PUT');

    // Image order
    var imageOrder = [];
    $('#product-gallery .img-thumb').each(function () { imageOrder.push($(this).data('id')); });
    fd.append('image_order', JSON.stringify(imageOrder));

    // Variations
    var variations = [];
    $('#variations-list .var-card').each(function (i) {
        var $card = $(this);
        var varId = $card.attr('data-var-id');
        var imgOrder = [];
        $card.find('.var-img-gallery .img-thumb').each(function () { imgOrder.push($(this).data('id')); });

        variations.push({
            id: (varId && !varId.startsWith('new_')) ? varId : null,
            name: $card.find('.var-name').val(),
            sku: $card.find('.var-sku').val(),
            price: $card.find('.var-price').val(),
            special_price: $card.find('.var-special').val() || null,
            special_price_start: $card.find('.var-special-start').val() || null,
            special_price_end: $card.find('.var-special-end').val() || null,
            stock_status: $card.find('.var-stock-status').val(),
            qty: $card.find('.var-qty').val(),
            images: imgOrder,
            _deleted_images: $card.find('.var-deleted-images').val() || '',
        });
    });
    fd.append('variations', JSON.stringify(variations));

    var $btn = action === 'save_exit' ? $('#btn-save-exit') : $('#btn-save');
    $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving…');

    $.ajax({
        url: url,
        method: 'POST',
        data: fd,
        processData: false,
        contentType: false,
        success: function (res) {
            if (!res.success) { toast(res.message || 'Error saving product.', 'error'); return; }

            if (action === 'save_exit') {
                window.location.href = '{{ route('admin.products.index') }}';
            } else {
                toast(res.message);
                if (!isEditMode && res.product_id) {
                    isEditMode = true;
                    productId = res.product_id;
                }
            }
        },
        error: function (xhr) {
            var errs = xhr.responseJSON && xhr.responseJSON.errors;
            if (errs) {
                clearErrors();
                var firstError = null;
                $.each(errs, function (field, messages) {
                    var $el = $('#' + field.replace('_', '-'));
                    var $input = $('[id="prod-' + field + '"], #' + field.replace('_', '-'));
                    if ($input.length) {
                        $input.addClass('field-error');
                        var $err = $input.next('.error-msg');
                        if ($err.length) $err.text(messages[0]).show();
                    }
                    if (!firstError) {
                        firstError = messages[0];
                        var $focus = $('[id="prod-' + field + '"], [name="' + field + '"]').first();
                        if ($focus.length) $focus.focus();
                    }
                });
                toast(firstError || 'Please check the form for errors.', 'error');
            } else {
                toast('Something went wrong. Please try again.', 'error');
            }
        },
        complete: function () {
            $btn.prop('disabled', false).html('<i class="fas fa-save"></i> ' + (action === 'save_exit' ? 'Save & Exit' : 'Save Product'));
        }
    });
}

function clearErrors() {
    $('.form-control').removeClass('field-error');
    $('.error-msg').hide().text('');
}

function toast(msg, type) {
    var icon = type === 'error' ? 'fa-exclamation-circle' : type === 'info' ? 'fa-info-circle' : 'fa-check-circle';
    var el = $('<div class="toast-msg ' + (type || 'success') + '"><i class="fas ' + icon + '"></i> ' + msg + '</div>');
    $('#toast-wrap').append(el);
    setTimeout(function () { el.fadeOut(300, function () { el.remove(); }); }, 3500);
}
</script>
@endpush
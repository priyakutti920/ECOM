@extends('layouts.admin')

@section('title', 'Navigation Bar Settings')

@push('styles')
<style>
.nav-manager-wrap {
    max-width: 1200px;
    margin: 0 auto;
    padding: 10px 0 40px;
}
.panel-nav {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    margin-bottom: 24px;
    overflow: hidden;
}
.panel-nav-head {
    padding: 14px 20px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.panel-nav-head h3 {
    margin: 0;
    font-size: 15px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 8px;
}
.panel-nav-body {
    padding: 20px;
}
.nav-preview-box {
    background: #0E1E3E;
    border-radius: 6px;
    padding: 10px 18px;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    margin-bottom: 20px;
    box-shadow: 0 4px 15px rgba(14, 30, 62, 0.15);
}
.nav-preview-left {
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}
.nav-preview-btn {
    background: #0068e1;
    color: #fff;
    padding: 7px 14px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.nav-preview-links {
    display: flex;
    align-items: center;
    gap: 14px;
    list-style: none;
    margin: 0;
    padding: 0;
    font-size: 13px;
}
.nav-preview-links a {
    color: rgba(255,255,255,0.85);
    text-decoration: none;
    font-weight: 500;
}
.nav-preview-links a.active {
    color: #ffffff;
    font-weight: 700;
}
.nav-preview-promo {
    font-size: 12.5px;
    color: rgba(255,255,255,0.9);
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.category-select-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 12px;
    margin-top: 10px;
}
.cat-select-card {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 10px 14px;
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    align-items: center;
    gap: 10px;
}
.cat-select-card:hover {
    border-color: #0068e1;
    background: #f0f7ff;
}
.cat-select-card input[type="checkbox"] {
    margin: 0;
    width: 17px;
    height: 17px;
    cursor: pointer;
}
.cat-select-info {
    flex: 1;
    min-width: 0;
}
.cat-select-name {
    font-size: 13px;
    font-weight: 600;
    color: #0f172a;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.cat-select-slug {
    font-size: 11px;
    color: #64748b;
}
.toggle-switch-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f1f5f9;
}
.toggle-switch-row:last-child {
    border-bottom: none;
}
.toggle-switch-label {
    display: flex;
    flex-direction: column;
}
.toggle-switch-label strong {
    font-size: 13.5px;
    color: #1e293b;
}
.toggle-switch-label span {
    font-size: 12px;
    color: #64748b;
}
.custom-link-row {
    display: grid;
    grid-template-columns: 1fr 1.5fr 120px 40px;
    gap: 10px;
    align-items: center;
    margin-bottom: 10px;
}
</style>
@endpush

@section('content')
<div class="nav-manager-wrap">

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px; flex-wrap:wrap; gap: 12px;">
        <div>
            <h2 style="margin:0 0 4px; font-weight:700; font-size:22px; color:#0f172a;">
                <i class="fas fa-compass" style="color:#0068e1; margin-right:8px;"></i> Storefront Navigation Bar
            </h2>
            <p style="margin:0; font-size:13px; color:#64748b;">
                Customize the main horizontal category bar, promo notice, and menu links shown across your online storefront.
            </p>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="{{ url('/') }}" target="_blank" class="btn btn-default">
                <i class="fas fa-external-link-alt"></i> View Storefront
            </a>
            <button type="submit" form="nav-form" class="btn btn-primary" style="font-weight:600; padding: 7px 20px;">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    <!-- Live Preview -->
    <div class="panel-nav">
        <div class="panel-nav-head">
            <h3><i class="fas fa-eye" style="color:#0068e1;"></i> Live Navigation Preview</h3>
            <span class="badge" style="background:#0068e1; font-weight:500;">Header Bar</span>
        </div>
        <div class="panel-nav-body" style="padding-bottom:12px;">
            <div class="nav-preview-box" id="navPreviewBox">
                <div class="nav-preview-left">
                    <div class="nav-preview-btn" id="prevAllCat" style="{{ $navSettings['nav_show_all_categories'] ? '' : 'display:none;' }}">
                        <span id="prevAllCatLabel">{{ $navSettings['nav_all_categories_label'] }}</span>
                        <i class="fas fa-bars"></i>
                    </div>
                    <ul class="nav-preview-links" id="prevLinksList">
                        <li id="prevHomeLink" style="{{ $navSettings['nav_show_home'] ? '' : 'display:none;' }}"><a href="#" class="active"><i class="fas fa-home"></i> <span id="prevHomeLabel">{{ $navSettings['nav_home_label'] }}</span></a></li>
                        <li id="prevShopLink" style="{{ $navSettings['nav_show_shop'] ? '' : 'display:none;' }}"><a href="#"><span id="prevShopLabel">{{ $navSettings['nav_shop_label'] }}</span></a></li>
                        
                        @php
                            $previewCatIds = $navSettings['nav_category_ids'] ?: $allCategories->take(5)->pluck('id')->all();
                        @endphp
                        @foreach($allCategories as $c)
                            <li class="prev-cat-item" data-cat-id="{{ $c->id }}" style="{{ in_array($c->id, $previewCatIds) ? '' : 'display:none;' }}">
                                <a href="#">{{ $c->name }}</a>
                            </li>
                        @endforeach

                        <li id="prevDealsLink" style="{{ $navSettings['nav_show_deals'] ? '' : 'display:none;' }}"><a href="#" style="color:#ff3366;"><i class="fas fa-fire"></i> <span id="prevDealsLabel">{{ $navSettings['nav_deals_label'] }}</span></a></li>
                    </ul>
                </div>
                <div class="nav-preview-promo" id="prevPromoBox" style="{{ $navSettings['nav_promo_enabled'] && !empty($navSettings['nav_promo_text']) ? '' : 'display:none;' }}">
                    <i class="fas fa-shipping-fast"></i>
                    <span id="prevPromoText">{{ $navSettings['nav_promo_text'] }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Settings Form -->
    <form id="nav-form" method="POST" action="{{ route('admin.navigation.update') }}">
        @csrf

        <div class="row">
            <!-- Left Column: Core Items & Promo Notice -->
            <div class="col-md-6">
                <!-- Standard Navigation Links -->
                <div class="panel-nav">
                    <div class="panel-nav-head">
                        <h3><i class="fas fa-list-ul" style="color:#0068e1;"></i> Standard Menu Items</h3>
                    </div>
                    <div class="panel-nav-body">
                        <!-- All Categories Button -->
                        <div class="toggle-switch-row">
                            <div class="toggle-switch-label">
                                <strong>"All Categories" Dropdown Button</strong>
                                <span>Toggles sidebar category menu on the frontend</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <input type="text" name="nav_all_categories_label" id="inpAllCatLabel" class="form-control input-sm" style="width:130px;" value="{{ $navSettings['nav_all_categories_label'] }}" oninput="updatePreview()">
                                <input type="checkbox" name="nav_show_all_categories" id="inpShowAllCat" value="1" {{ $navSettings['nav_show_all_categories'] ? 'checked' : '' }} onchange="updatePreview()">
                            </div>
                        </div>

                        <!-- Home Link -->
                        <div class="toggle-switch-row">
                            <div class="toggle-switch-label">
                                <strong>Home Page Link</strong>
                                <span>Points to storefront home URL (<code>/</code>)</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <input type="text" name="nav_home_label" id="inpHomeLabel" class="form-control input-sm" style="width:130px;" value="{{ $navSettings['nav_home_label'] }}" oninput="updatePreview()">
                                <input type="checkbox" name="nav_show_home" id="inpShowHome" value="1" {{ $navSettings['nav_show_home'] ? 'checked' : '' }} onchange="updatePreview()">
                            </div>
                        </div>

                        <!-- Shop Link -->
                        <div class="toggle-switch-row">
                            <div class="toggle-switch-label">
                                <strong>All Products / Shop Link</strong>
                                <span>Points to <code>/products</code></span>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <input type="text" name="nav_shop_label" id="inpShopLabel" class="form-control input-sm" style="width:130px;" value="{{ $navSettings['nav_shop_label'] }}" oninput="updatePreview()">
                                <input type="checkbox" name="nav_show_shop" id="inpShowShop" value="1" {{ $navSettings['nav_show_shop'] ? 'checked' : '' }} onchange="updatePreview()">
                            </div>
                        </div>

                        <!-- Flash Deals Link -->
                        <div class="toggle-switch-row">
                            <div class="toggle-switch-label">
                                <strong>Flash Deals Link</strong>
                                <span>Highlighted link for promotional/deal items (<code>/deals</code>)</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:8px;">
                                <input type="text" name="nav_deals_label" id="inpDealsLabel" class="form-control input-sm" style="width:130px;" value="{{ $navSettings['nav_deals_label'] }}" oninput="updatePreview()">
                                <input type="checkbox" name="nav_show_deals" id="inpShowDeals" value="1" {{ $navSettings['nav_show_deals'] ? 'checked' : '' }} onchange="updatePreview()">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side Promo Notice -->
                <div class="panel-nav">
                    <div class="panel-nav-head">
                        <h3><i class="fas fa-bullhorn" style="color:#0068e1;"></i> Header Promo Announcement</h3>
                        <label style="margin:0; font-weight:normal; cursor:pointer;">
                            <input type="checkbox" name="nav_promo_enabled" id="inpPromoEnabled" value="1" {{ $navSettings['nav_promo_enabled'] ? 'checked' : '' }} onchange="updatePreview()">
                            <span style="font-size:12px; margin-left:4px;">Enable</span>
                        </label>
                    </div>
                    <div class="panel-nav-body">
                        <div class="form-group">
                            <label style="font-size:12.5px; font-weight:600; color:#334155;">Promo Message Text</label>
                            <input type="text" name="nav_promo_text" id="inpPromoText" class="form-control" placeholder="e.g. Free shipping on all orders over ₹499" value="{{ $navSettings['nav_promo_text'] }}" oninput="updatePreview()">
                            <span class="help-block" style="font-size:11.5px; color:#64748b; margin-top:4px;">
                                Appears on the right end of the horizontal navigation bar on desktop screens.
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Featured Categories in Navigation -->
            <div class="col-md-6">
                <div class="panel-nav">
                    <div class="panel-nav-head">
                        <h3><i class="fas fa-th-large" style="color:#0068e1;"></i> Categories in Navigation</h3>
                        <span style="font-size:11.5px; color:#64748b;">Select categories to show in the bar</span>
                    </div>
                    <div class="panel-nav-body">
                        <p style="font-size:12.5px; color:#475569; margin-bottom:12px;">
                            Check which categories should appear directly as menu links. Unchecked categories remain accessible under the <strong>"All Categories"</strong> dropdown.
                        </p>

                        @php
                            $selectedIds = $navSettings['nav_category_ids'] ?: $allCategories->take(5)->pluck('id')->all();
                        @endphp

                        <div class="category-select-grid">
                            @foreach($allCategories as $cat)
                                <label class="cat-select-card">
                                    <input type="checkbox" name="category_ids[]" value="{{ $cat->id }}" {{ in_array($cat->id, $selectedIds) ? 'checked' : '' }} onchange="updateCategoryPreview(this, {{ $cat->id }})">
                                    <div class="cat-select-info">
                                        <div class="cat-select-name">{{ $cat->name }}</div>
                                        <div class="cat-select-slug">/shop?category={{ $cat->id }}</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        @if($allCategories->count() === 0)
                            <div style="text-align:center; padding:20px; color:#94a3b8;">
                                No active categories found. Please add categories under <a href="{{ route('admin.categories.index') }}">Categories</a>.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Custom Menu Links -->
                <div class="panel-nav">
                    <div class="panel-nav-head">
                        <h3><i class="fas fa-link" style="color:#0068e1;"></i> Custom Menu Links</h3>
                        <button type="button" class="btn btn-xs btn-default" onclick="addCustomLinkRow()">
                            <i class="fas fa-plus"></i> Add Link
                        </button>
                    </div>
                    <div class="panel-nav-body">
                        <div id="customLinksContainer">
                            @if(!empty($navSettings['nav_custom_links']))
                                @foreach($navSettings['nav_custom_links'] as $link)
                                    <div class="custom-link-row">
                                        <input type="text" name="custom_link_title[]" class="form-control input-sm" placeholder="Title (e.g. About)" value="{{ $link['title'] ?? '' }}">
                                        <input type="text" name="custom_link_url[]" class="form-control input-sm" placeholder="URL (/about or https://...)" value="{{ $link['url'] ?? '' }}">
                                        <select name="custom_link_target[]" class="form-control input-sm">
                                            <option value="_self" {{ ($link['target'] ?? '') === '_self' ? 'selected' : '' }}>Same Tab</option>
                                            <option value="_blank" {{ ($link['target'] ?? '') === '_blank' ? 'selected' : '' }}>New Tab</option>
                                        </select>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.custom-link-row').remove()"><i class="fas fa-times"></i></button>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <span class="help-block" style="font-size:11.5px; color:#64748b; margin-top:6px;">
                            Add custom links like special campaign pages, blog, or external resources.
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div style="text-align:right; margin-top:10px;">
            <button type="submit" class="btn btn-primary btn-lg" style="font-weight:700; padding:10px 32px;">
                <i class="fas fa-save"></i> Save Navigation Settings
            </button>
        </div>
    </form>

</div>

@push('scripts')
<script>
function updatePreview() {
    // All Categories
    const showAllCat = document.getElementById('inpShowAllCat').checked;
    const allCatLabel = document.getElementById('inpAllCatLabel').value || 'All Categories';
    document.getElementById('prevAllCat').style.display = showAllCat ? 'inline-flex' : 'none';
    document.getElementById('prevAllCatLabel').textContent = allCatLabel;

    // Home
    const showHome = document.getElementById('inpShowHome').checked;
    const homeLabel = document.getElementById('inpHomeLabel').value || 'Home';
    document.getElementById('prevHomeLink').style.display = showHome ? 'inline-block' : 'none';
    document.getElementById('prevHomeLabel').textContent = homeLabel;

    // Shop
    const showShop = document.getElementById('inpShowShop').checked;
    const shopLabel = document.getElementById('inpShopLabel').value || 'Shop';
    document.getElementById('prevShopLink').style.display = showShop ? 'inline-block' : 'none';
    document.getElementById('prevShopLabel').textContent = shopLabel;

    // Deals
    const showDeals = document.getElementById('inpShowDeals').checked;
    const dealsLabel = document.getElementById('inpDealsLabel').value || 'Flash Deals';
    document.getElementById('prevDealsLink').style.display = showDeals ? 'inline-block' : 'none';
    document.getElementById('prevDealsLabel').textContent = dealsLabel;

    // Promo
    const promoEnabled = document.getElementById('inpPromoEnabled').checked;
    const promoText = document.getElementById('inpPromoText').value;
    const promoBox = document.getElementById('prevPromoBox');
    if (promoEnabled && promoText.trim() !== '') {
        promoBox.style.display = 'inline-flex';
        document.getElementById('prevPromoText').textContent = promoText;
    } else {
        promoBox.style.display = 'none';
    }
}

function updateCategoryPreview(checkbox, catId) {
    const item = document.querySelector(`.prev-cat-item[data-cat-id="${catId}"]`);
    if (item) {
        item.style.display = checkbox.checked ? 'inline-block' : 'none';
    }
}

function addCustomLinkRow() {
    const container = document.getElementById('customLinksContainer');
    const div = document.createElement('div');
    div.className = 'custom-link-row';
    div.innerHTML = `
        <input type="text" name="custom_link_title[]" class="form-control input-sm" placeholder="Title (e.g. About)">
        <input type="text" name="custom_link_url[]" class="form-control input-sm" placeholder="URL (/about or https://...)">
        <select name="custom_link_target[]" class="form-control input-sm">
            <option value="_self">Same Tab</option>
            <option value="_blank">New Tab</option>
        </select>
        <button type="button" class="btn btn-sm btn-danger" onclick="this.closest('.custom-link-row').remove()"><i class="fas fa-times"></i></button>
    `;
    container.appendChild(div);
}
</script>
@endpush
@endsection

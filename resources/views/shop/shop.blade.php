@extends('layouts.shop')

@section('title', (!empty($activeCategory) ? $activeCategory->name . ' — ' : '') . 'Shop — ' . $storeName)
@section('description', !empty($activeCategory) ? ('Explore ' . $activeCategory->name . ' collection at ' . $storeName) : (\App\Models\StoreSetting::getValue('meta_description') ?: ('Shop all products at ' . $storeName)))

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<div class="shop-page-wrap">

    {{-- ── Breadcrumbs ── --}}
    <nav class="shop-breadcrumbs" aria-label="Breadcrumb">
        <a href="{{ url('/') }}" class="shop-bc-link"><i class="las la-home"></i> Home</a>
        <span class="shop-bc-sep"><i class="las la-angle-right"></i></span>
        <a href="{{ url('/shop') }}" class="shop-bc-link {{ empty($activeCategory) ? 'is-active' : '' }}">Shop</a>
        @if(!empty($activeCategory))
            @if($activeCategory->parent)
                <span class="shop-bc-sep"><i class="las la-angle-right"></i></span>
                <a href="{{ url('/shop?category=' . $activeCategory->parent->id) }}" class="shop-bc-link">{{ $activeCategory->parent->name }}</a>
            @endif
            <span class="shop-bc-sep"><i class="las la-angle-right"></i></span>
            <span class="shop-bc-current">{{ $activeCategory->name }}</span>
        @endif
    </nav>

    {{-- ── Category / Page Hero Header ── --}}
    <div class="shop-page-head">
        <div class="shop-head-content">
            <div class="shop-title-row">
                <h1 class="shop-page-title">
                    @if(!empty($activeCategory))
                        {{ $activeCategory->name }}
                    @else
                        All Products
                    @endif
                </h1>
                <span class="shop-count-pill">
                    {{ $products->total() }} {{ Str::plural('Item', $products->total()) }}
                </span>
            </div>
            <p class="shop-page-sub">
                @if(!empty($activeCategory))
                    Explore premium {{ strtolower($activeCategory->name) }} crafted for comfort, style, and everyday wear.
                @else
                    Discover our full range of signature clothing, best sellers, and everyday essentials.
                @endif
            </p>
        </div>
    </div>

    {{-- ── Categories bar (round icons) ── --}}
    <div class="shop-cats-card">
        <div class="shop-cats-top">
            <span class="shop-cats-heading"><i class="las la-th-large"></i> Categories</span>
            @if(!empty($activeCategory))
                <a href="{{ url('/shop') }}" class="shop-cats-reset" title="Show all products">
                    <i class="las la-times-circle"></i> View All
                </a>
            @endif
        </div>
        <div class="cat-tiles-row shop-cats-bar">
            {{-- "All" tile --}}
            <a href="{{ url('/shop') }}" class="cat-tile {{ empty($activeCategory) ? 'is-active' : '' }}" title="All Categories">
                <div class="cat-tile-circle cat-all-circle">
                    <i class="las la-shapes"></i>
                </div>
                <div class="cat-tile-name">All</div>
            </a>

            @foreach($categories as $cat)
                @include('shop.partials.category-tile', [
                    'cat'      => $cat,
                    'href'     => url('/shop?category=' . $cat->id),
                    'isActive' => !empty($activeCategory) && $activeCategory->id === $cat->id,
                ])
            @endforeach
        </div>
    </div>

    {{-- ── Toolbar (results count & sorting) ── --}}
    <div class="shop-toolbar">
        <div class="shop-toolbar-left">
            <span class="shop-results-info">
                Showing <strong>{{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? $products->count() }}</strong> of <strong>{{ $products->total() }}</strong> {{ Str::plural('item', $products->total()) }}
            </span>
            @if(!empty($activeCategory))
                <span class="shop-active-chip">
                    <span class="chip-label">Category:</span>
                    <strong class="chip-val">{{ $activeCategory->name }}</strong>
                    <a href="{{ url('/shop') }}" class="chip-remove" title="Remove category filter" aria-label="Remove filter">
                        <i class="las la-times"></i>
                    </a>
                </span>
            @endif
        </div>
        <form method="GET" class="shop-toolbar-right" id="shopSortForm">
            @if(!empty($activeCategory))
                <input type="hidden" name="category" value="{{ $activeCategory->id }}">
            @endif
            <label for="shop-sort" class="shop-sort-label">
                <i class="las la-sort-amount-down"></i> Sort by
            </label>
            <div class="shop-select-wrapper">
                <select id="shop-sort" name="sort" class="shop-sort-select" onchange="this.form.submit()">
                    <option value="latest"     {{ ($sort ?? 'latest') == 'latest' ? 'selected' : '' }}>Newest Arrivals</option>
                    <option value="price_low"  {{ ($sort ?? '') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="price_high" {{ ($sort ?? '') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                    <option value="name"       {{ ($sort ?? '') == 'name' ? 'selected' : '' }}>Alphabetical (A–Z)</option>
                </select>
                <i class="las la-angle-down shop-select-arrow"></i>
            </div>
        </form>
    </div>

    {{-- ── Product grid ── --}}
    @if($products->count() > 0)
        <div class="products-grid shop-products-grid">
            @foreach($products as $product)
                @include('shop.partials.product-card', ['product' => $product])
            @endforeach
        </div>

        <div class="shop-pagination-wrap">
            {{ $products->withQueryString()->links('pagination::simple-default') }}
        </div>
    @else
        <div class="shop-empty">
            <div class="shop-empty-icon-wrap">
                <i class="las la-box-open"></i>
            </div>
            <h3 class="shop-empty-title">No products found</h3>
            <p class="shop-empty-text">
                @if(!empty($activeCategory))
                    There are currently no products in the <strong>{{ $activeCategory->name }}</strong> category.
                @else
                    No products match your current selection.
                @endif
            </p>
            <div class="shop-empty-actions">
                @if(!empty($activeCategory))
                    <a href="{{ url('/shop') }}" class="shop-btn-primary">
                        <i class="las la-arrow-left"></i> Browse All Products
                    </a>
                @else
                    <a href="{{ url('/') }}" class="shop-btn-primary">
                        <i class="las la-home"></i> Back to Home
                    </a>
                @endif
            </div>
        </div>
    @endif

</div>
@endsection

@push('styles')
<style>
/* ── Page Container ── */
.shop-page-wrap {
    max-width: 1440px;
    margin: 0 auto;
    padding: 16px 20px 60px;
}

/* ── Breadcrumbs ── */
.shop-breadcrumbs {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--color-muted, #64748b);
    margin-bottom: 16px;
    flex-wrap: wrap;
}
.shop-bc-link {
    color: var(--color-muted, #64748b);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: color 0.15s ease;
}
.shop-bc-link:hover,
.shop-bc-link.is-active {
    color: var(--color-primary, #0068e1);
}
.shop-bc-sep {
    font-size: 11px;
    color: #cbd5e1;
    display: inline-flex;
    align-items: center;
}
.shop-bc-current {
    color: var(--color-heading, #0f172a);
    font-weight: 600;
}

/* ── Hero Category Header ── */
.shop-page-head {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px solid var(--color-border, #e2e8f0);
    border-radius: var(--radius-lg, 12px);
    padding: 24px 28px;
    margin-bottom: 20px;
    position: relative;
    overflow: hidden;
}
.shop-page-head::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: var(--color-primary, #0068e1);
    border-radius: 4px 0 0 4px;
}
.shop-head-content {
    position: relative;
    z-index: 1;
}
.shop-title-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 6px;
    flex-wrap: wrap;
}
.shop-page-title {
    font-size: 26px;
    font-weight: 800;
    color: var(--color-heading, #0f172a);
    margin: 0;
    line-height: 1.2;
    letter-spacing: -0.02em;
}
.shop-count-pill {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    background: #ffffff;
    border: 1px solid var(--color-border, #e2e8f0);
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    color: var(--color-primary, #0068e1);
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}
.shop-page-sub {
    font-size: 14px;
    color: var(--color-muted, #64748b);
    margin: 0;
    max-width: 720px;
    line-height: 1.5;
}

/* ── Categories Bar ── */
.shop-cats-card {
    background: #ffffff;
    border: 1px solid var(--color-border, #e2e8f0);
    border-radius: var(--radius-lg, 12px);
    padding: 16px 18px 14px;
    margin-bottom: 20px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}
.shop-cats-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
    padding: 0 4px;
}
.shop-cats-heading {
    font-size: 13px;
    font-weight: 700;
    color: var(--color-heading, #0f172a);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.shop-cats-heading i {
    color: var(--color-primary, #0068e1);
    font-size: 15px;
}
.shop-cats-reset {
    font-size: 12px;
    font-weight: 600;
    color: var(--color-primary, #0068e1);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    border-radius: 6px;
    background: rgba(0, 104, 225, 0.08);
    transition: all 0.2s ease;
}
.shop-cats-reset:hover {
    background: rgba(0, 104, 225, 0.16);
    color: var(--color-primary-hover, #0051b3);
}

.cat-tiles-row {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    overflow-x: auto;
    padding: 4px 4px 10px;
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
    -webkit-overflow-scrolling: touch;
}
.cat-tiles-row::-webkit-scrollbar {
    height: 5px;
}
.cat-tiles-row::-webkit-scrollbar-track {
    background: transparent;
}
.cat-tiles-row::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 99px;
}
.cat-tiles-row::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Category individual tile */
.cat-tile {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    min-width: 78px;
    max-width: 88px;
    text-align: center;
    flex-shrink: 0;
    cursor: pointer;
    transition: transform 0.2s cubic-bezier(0.2, 0, 0, 1);
}
.cat-tile:hover {
    transform: translateY(-3px);
}
.cat-tile-circle {
    width: 62px;
    height: 62px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    background: #f1f5f9;
    color: #475569;
    font-size: 22px;
    border: 2px solid #e2e8f0;
    box-shadow: 0 2px 6px rgba(0,0,0,0.04);
    transition: all 0.2s cubic-bezier(0.2, 0, 0, 1);
    position: relative;
}
.cat-tile:hover .cat-tile-circle {
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.cat-tile.is-active .cat-tile-circle {
    border-color: var(--color-primary, #0068e1);
    box-shadow: 0 0 0 3px rgba(0, 104, 225, 0.22), 0 4px 12px rgba(0, 104, 225, 0.15);
    transform: scale(1.05);
}
.cat-tile-circle img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.cat-all-circle {
    background: linear-gradient(135deg, var(--color-primary, #0068e1) 0%, #2563eb 100%);
    color: #ffffff;
    border-color: transparent;
}
.cat-tile-name {
    font-size: 11.5px;
    font-weight: 600;
    color: var(--color-heading, #1e293b);
    line-height: 1.25;
    transition: color 0.15s ease;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    word-break: break-word;
}
.cat-tile:hover .cat-tile-name {
    color: var(--color-primary, #0068e1);
}
.cat-tile.is-active .cat-tile-name {
    color: var(--color-primary, #0068e1);
    font-weight: 700;
}

/* ── Toolbar (results & sort) ── */
.shop-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    padding: 12px 18px;
    background: #ffffff;
    border: 1px solid var(--color-border, #e2e8f0);
    border-radius: var(--radius-lg, 12px);
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.02);
    flex-wrap: wrap;
}
.shop-toolbar-left {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.shop-results-info {
    font-size: 13.5px;
    color: var(--color-muted, #64748b);
}
.shop-results-info strong {
    color: var(--color-heading, #0f172a);
    font-weight: 700;
}
.shop-active-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(0, 104, 225, 0.08);
    border: 1px solid rgba(0, 104, 225, 0.2);
    border-radius: 20px;
    padding: 3px 10px 3px 12px;
    font-size: 12px;
}
.shop-active-chip .chip-label {
    color: var(--color-muted, #64748b);
}
.shop-active-chip .chip-val {
    color: var(--color-primary, #0068e1);
    font-weight: 700;
}
.shop-active-chip .chip-remove {
    color: var(--color-primary, #0068e1);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    margin-left: 2px;
    transition: background 0.15s ease, color 0.15s ease;
}
.shop-active-chip .chip-remove:hover {
    background: var(--color-primary, #0068e1);
    color: #ffffff;
}

.shop-toolbar-right {
    display: flex;
    align-items: center;
    gap: 10px;
}
.shop-sort-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--color-muted, #64748b);
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin: 0;
}
.shop-select-wrapper {
    position: relative;
    display: inline-block;
}
.shop-sort-select {
    appearance: none;
    -webkit-appearance: none;
    padding: 7px 32px 7px 12px;
    border: 1px solid var(--color-border, #e2e8f0);
    border-radius: var(--radius-md, 8px);
    font-size: 13px;
    font-weight: 500;
    background: #ffffff;
    color: var(--color-heading, #0f172a);
    cursor: pointer;
    outline: none;
    transition: border-color 0.15s ease, box-shadow 0.15s ease;
}
.shop-sort-select:focus {
    border-color: var(--color-primary, #0068e1);
    box-shadow: 0 0 0 3px rgba(0, 104, 225, 0.15);
}
.shop-select-arrow {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    font-size: 12px;
    color: var(--color-muted, #64748b);
}

/* ── Product Grid ── */
.shop-products-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    background: transparent;
    border: none;
    padding: 0;
    margin-bottom: 24px;
}
.shop-pagination-wrap {
    margin-top: 24px;
    display: flex;
    justify-content: center;
}

/* ── Empty State ── */
.shop-empty {
    background: #ffffff;
    border: 1px solid var(--color-border, #e2e8f0);
    border-radius: var(--radius-lg, 12px);
    padding: 60px 24px;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.shop-empty-icon-wrap {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: #f1f5f9;
    color: #94a3b8;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 38px;
    margin-bottom: 18px;
}
.shop-empty-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--color-heading, #0f172a);
    margin: 0 0 8px;
}
.shop-empty-text {
    font-size: 14px;
    color: var(--color-muted, #64748b);
    max-width: 440px;
    margin: 0 auto 22px;
    line-height: 1.5;
}
.shop-empty-actions {
    display: flex;
    justify-content: center;
    gap: 12px;
}
.shop-btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: var(--color-primary, #0068e1);
    color: #ffffff;
    padding: 10px 24px;
    border-radius: var(--radius-md, 8px);
    font-weight: 600;
    text-decoration: none;
    font-size: 13.5px;
    box-shadow: 0 2px 6px rgba(0, 104, 225, 0.25);
    transition: background 0.15s ease, transform 0.15s ease, box-shadow 0.15s ease;
}
.shop-btn-primary:hover {
    background: var(--color-primary-hover, #0051b3);
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(0, 104, 225, 0.35);
}

/* ── Responsive Queries ── */
@media (max-width: 1200px) {
    .shop-products-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
    }
}
@media (max-width: 900px) {
    .shop-page-head {
        padding: 20px;
    }
    .shop-page-title {
        font-size: 22px;
    }
}
@media (max-width: 768px) {
    .shop-page-wrap {
        padding: 12px 14px 40px;
    }
    .shop-page-head {
        padding: 16px 18px;
        margin-bottom: 16px;
    }
    .shop-page-title {
        font-size: 20px;
    }
    .shop-cats-card {
        padding: 14px 12px 10px;
        margin-bottom: 16px;
    }
    .cat-tiles-row {
        gap: 12px;
        padding-bottom: 8px;
    }
    .cat-tile {
        min-width: 70px;
        max-width: 76px;
    }
    .cat-tile-circle {
        width: 54px;
        height: 54px;
        font-size: 19px;
    }
    .cat-tile-name {
        font-size: 11px;
    }
    .shop-toolbar {
        padding: 10px 14px;
        margin-bottom: 16px;
    }
    .shop-products-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    .shop-empty {
        padding: 44px 16px;
    }
}
@media (max-width: 480px) {
    .shop-page-wrap {
        padding: 10px 10px 32px;
    }
    .shop-page-title {
        font-size: 18px;
    }
    .shop-page-sub {
        font-size: 13px;
    }
    .shop-toolbar {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    .shop-toolbar-right {
        width: 100%;
        justify-content: space-between;
    }
    .shop-select-wrapper,
    .shop-sort-select {
        width: 100%;
    }
    .shop-products-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
}
</style>
@endpush

@extends('layouts.shop')

@section('title', 'Shop — ' . $storeName)
@section('description', 'Shop all products at ' . $storeName . ' — Electronics, Fashion, Home & Kitchen, Grocery and more at the best prices.')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<div class="shop-page-wrap">

    {{-- ── Page header ── --}}
    <div class="shop-page-head">
        <div class="shop-head-inner">
            <h1 class="shop-page-title">
                @if(!empty($activeCategory))
                    <i class="fas fa-store" style="color: var(--amazon-orange); margin-right: 6px;"></i>
                    {{ $activeCategory->name }}
                @else
                    <i class="fas fa-shopping-bag" style="color: var(--amazon-orange); margin-right: 6px;"></i>
                    Shop All
                @endif
            </h1>
            <p class="shop-page-sub">
                @if(!empty($activeCategory))
                    {{ $products->total() }} {{ Str::plural('product', $products->total()) }} in this category
                @else
                    {{ $products->total() }} {{ Str::plural('product', $products->total()) }} available
                @endif
            </p>
        </div>
    </div>

    {{-- ── Categories bar (round icons: image -> circle, fallback -> icon -> circle) ── --}}
    <div class="cat-tiles-row shop-cats-bar">
        {{-- "All" tile --}}
        <a href="{{ url('/shop') }}" class="cat-tile {{ empty($activeCategory) ? 'is-active' : '' }}" title="All categories">
            <div class="cat-tile-circle" style="background: linear-gradient(135deg, #232f3e 0%, #131921 100%); color: #FF9900; border-color: #131921;">
                <i class="fas fa-grip"></i>
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

    {{-- ── Toolbar (sort) ── --}}
    <div class="shop-toolbar">
        <div class="shop-toolbar-left">
            <i class="fas fa-filter" style="color: var(--medium-gray);"></i>
            <span>Showing
                <strong>{{ $products->count() }}</strong>
                of <strong>{{ $products->total() }}</strong>
            </span>
        </div>
        <form method="GET" class="shop-toolbar-right">
            @if(!empty($activeCategory))
                <input type="hidden" name="category" value="{{ $activeCategory->id }}">
            @endif
            <label for="shop-sort">Sort by:</label>
            <select id="shop-sort" name="sort" onchange="this.form.submit()">
                <option value="latest"    {{ ($sort ?? 'latest') == 'latest' ? 'selected' : '' }}>Latest</option>
                <option value="price_low" {{ ($sort ?? '') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                <option value="price_high"{{ ($sort ?? '') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                <option value="name"      {{ ($sort ?? '') == 'name' ? 'selected' : '' }}>Name (A–Z)</option>
            </select>
        </form>
    </div>

    {{-- ── Product grid ── --}}
    @if($products->count() > 0)
        <div class="products-grid shop-products-grid">
            @foreach($products as $product)
                @include('shop.partials.product-card', ['product' => $product])
            @endforeach
        </div>

        <div class="shop-pagination">
            {{ $products->withQueryString()->links('pagination::simple-default') }}
        </div>
    @else
        <div class="shop-empty">
            <i class="fas fa-box-open"></i>
            <h3>No products to show</h3>
            <p>
                @if(!empty($activeCategory))
                    There are no products in <strong>{{ $activeCategory->name }}</strong> right now.
                @else
                    Add products in the admin panel to populate the storefront.
                @endif
            </p>
            @if(!empty($activeCategory))
                <a href="{{ url('/shop') }}" class="shop-empty-btn">
                    <i class="fas fa-arrow-left"></i> Browse all products
                </a>
            @else
                <a href="{{ url('/') }}" class="shop-empty-btn">
                    <i class="fas fa-home"></i> Go to homepage
                </a>
            @endif
        </div>
    @endif

</div>
@endsection

@push('styles')
<style>
/* ── Shop page wrapper ── */
.shop-page-wrap { max-width: 1400px; margin: 0 auto; padding: 16px 12px 40px; }

/* ── Page header ── */
.shop-page-head { padding: 8px 4px 14px; }
.shop-head-inner { display: flex; flex-direction: column; gap: 4px; }
.shop-page-title { font-size: 22px; font-weight: 700; color: var(--amazon-charcoal); margin: 0; line-height: 1.2; }
.shop-page-sub   { font-size: 13px; color: var(--medium-gray); margin: 0; }

/* ── Categories bar (white card wrapping the new .cat-tile row) ── */
.shop-cats-bar {
    background: #fff;
    border: 1px solid #e7e7e7;
    border-radius: 6px;
    padding: 12px 8px;
    margin-bottom: 14px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.03);
}

/* ── Toolbar ── */
.shop-toolbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    background: #fff;
    border: 1px solid #e7e7e7;
    border-radius: 6px;
    margin-bottom: 12px;
    flex-wrap: wrap;
}
.shop-toolbar-left { font-size: 13px; color: var(--medium-gray); display: flex; align-items: center; gap: 6px; }
.shop-toolbar-left strong { color: #0F1111; }
.shop-toolbar-right { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--medium-gray); }
.shop-toolbar-right select {
    padding: 7px 10px;
    border: 1px solid #d5d9d9;
    border-radius: 4px;
    font-size: 13px;
    background: #fff;
    cursor: pointer;
}
.shop-toolbar-right select:focus { outline: none; border-color: #007185; box-shadow: 0 0 0 2px rgba(0,113,133,0.15); }

/* ── Product grid ── */
.shop-products-grid { background: #fff; border: 1px solid #e7e7e7; border-radius: 6px; padding: 14px 10px; }
.shop-pagination { margin-top: 18px; display: flex; justify-content: center; }

/* ── Empty state ── */
.shop-empty {
    background: #fff;
    border: 1px solid #e7e7e7;
    border-radius: 6px;
    padding: 60px 20px;
    text-align: center;
}
.shop-empty i { font-size: 64px; color: #cbd5e1; display: block; margin-bottom: 14px; }
.shop-empty h3 { font-size: 18px; font-weight: 700; color: var(--amazon-charcoal); margin: 0 0 6px; }
.shop-empty p  { font-size: 14px; color: var(--medium-gray); margin: 0 0 16px; }
.shop-empty-btn {
    display: inline-block;
    background: var(--amazon-orange);
    color: var(--amazon-dark);
    padding: 9px 22px;
    border-radius: 100px;
    font-weight: 700;
    text-decoration: none;
    font-size: 13px;
    transition: background 0.18s;
}
.shop-empty-btn:hover { background: #fa8900; }

/* ── Responsive ── */
@media (max-width: 768px) {
    .shop-page-wrap { padding: 12px 8px 32px; }
    .shop-page-title { font-size: 18px; }
    .shop-cats-bar { padding: 10px 4px; }
    .shop-cat-tile { width: 64px; }
    .shop-cat-icon { width: 52px; height: 52px; }
    .shop-cat-icon-fallback i, .shop-cat-icon-all i { font-size: 20px; }
    .shop-cat-name { font-size: 10px; max-width: 64px; }
    .shop-toolbar { padding: 8px 10px; }
    .shop-products-grid { padding: 10px 6px; }
    .shop-empty { padding: 40px 16px; }
    .shop-empty i { font-size: 48px; }
}
@media (max-width: 480px) {
    .shop-cat-tile { width: 58px; }
    .shop-cat-icon { width: 46px; height: 46px; }
    .shop-cat-icon-fallback i, .shop-cat-icon-all i { font-size: 18px; }
    .shop-cat-name { font-size: 9px; max-width: 58px; }
    .shop-toolbar-right label { display: none; }
}
</style>
@endpush

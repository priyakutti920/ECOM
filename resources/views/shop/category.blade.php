@extends('layouts.shop')

@section('title', $category->name . ' — ' . $storeName)
@section('description', 'Shop ' . $category->name . ' at ' . $storeName . '. Great prices, fast delivery.')

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<div class="cat-page-wrap">

    {{-- ── Breadcrumb ── --}}
    <nav class="cat-breadcrumbs" aria-label="Breadcrumb">
        <a href="{{ url('/') }}" class="cat-bc-link"><i class="las la-home"></i> Home</a>
        @if($category->parent)
            <span class="cat-bc-sep"><i class="las la-angle-right"></i></span>
            <a href="{{ url('/category/' . ($category->parent->slug ?: $category->parent->id)) }}" class="cat-bc-link">{{ $category->parent->name }}</a>
        @endif
        <span class="cat-bc-sep"><i class="las la-angle-right"></i></span>
        <span class="cat-bc-current">{{ $category->name }}</span>
    </nav>

    {{-- ── Category Hero Header ── --}}
    <div class="cat-page-head">
        <div class="cat-head-content">
            <div class="cat-title-row">
                <h1 class="cat-page-title">{{ $category->name }}</h1>
                <span class="cat-count-pill">
                    {{ $products->total() }} {{ Str::plural('Product', $products->total()) }}
                </span>
            </div>
            @if(!empty($category->meta_description))
                <p class="cat-page-sub">{{ $category->meta_description }}</p>
            @else
                <p class="cat-page-sub">Explore our curated collection of premium {{ strtolower($category->name) }}. Quality essentials built to last.</p>
            @endif
        </div>
    </div>

    {{-- ── Subcategories Bar ── --}}
    @if($subcategories->count() > 0)
        <div class="subcats-card">
            <div class="subcats-head">
                <span class="subcats-label"><i class="las la-tags"></i> Subcategories</span>
                @if(!empty($isRoot) && $products->count() > 0)
                    <span class="subcats-hint">Showing all products including sub-categories</span>
                @endif
            </div>
            <div class="subcats-list">
                @foreach($subcategories as $sub)
                    <a href="{{ url('/category/' . ($sub->slug ?: $sub->id)) }}" class="subcat-pill">
                        {{ $sub->name }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ── Toolbar (results & sorting) ── --}}
    <div class="cat-toolbar">
        <div class="cat-toolbar-left">
            <span class="cat-results-info">
                Showing <strong>{{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? $products->count() }}</strong> of <strong>{{ $products->total() }}</strong> {{ Str::plural('product', $products->total()) }}
            </span>
        </div>
        <form method="GET" class="cat-toolbar-right">
            <label for="cat-sort" class="cat-sort-label">
                <i class="las la-sort-amount-down"></i> Sort by
            </label>
            <div class="cat-select-wrapper">
                <select id="cat-sort" name="sort" class="cat-sort-select" onchange="this.form.submit()">
                    <option value="latest"     {{ ($sort ?? 'latest') == 'latest' ? 'selected' : '' }}>Newest Arrivals</option>
                    <option value="price_low"  {{ ($sort ?? '') == 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="price_high" {{ ($sort ?? '') == 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                    <option value="name"       {{ ($sort ?? '') == 'name' ? 'selected' : '' }}>Alphabetical (A–Z)</option>
                </select>
                <i class="las la-angle-down cat-select-arrow"></i>
            </div>
        </form>
    </div>

    {{-- ── Products Grid ── --}}
    @if($products->count() > 0)
        <div class="products-grid cat-products-grid">
            @foreach($products as $product)
                @include('shop.partials.product-card', ['product' => $product])
            @endforeach
        </div>

        <div class="cat-pagination-wrap">
            {{ $products->withQueryString()->links('pagination::simple-default') }}
        </div>
    @else
        <div class="cat-empty">
            <div class="cat-empty-icon-wrap">
                <i class="las la-box-open"></i>
            </div>
            <h3 class="cat-empty-title">No products in this category yet</h3>
            <p class="cat-empty-text">Check back soon for new arrivals or explore other popular styles.</p>
            <div class="cat-empty-actions">
                <a href="{{ url('/shop') }}" class="cat-btn-primary">
                    <i class="las la-shopping-bag"></i> Browse All Products
                </a>
            </div>
        </div>
    @endif

</div>
@endsection

@push('styles')
<style>
/* ── Page Layout ── */
.cat-page-wrap {
    max-width: 1440px;
    margin: 0 auto;
    padding: 16px 20px 60px;
}

/* ── Breadcrumbs ── */
.cat-breadcrumbs {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--color-muted, #64748b);
    margin-bottom: 16px;
    flex-wrap: wrap;
}
.cat-bc-link {
    color: var(--color-muted, #64748b);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: color 0.15s ease;
}
.cat-bc-link:hover {
    color: var(--color-primary, #0068e1);
}
.cat-bc-sep {
    font-size: 11px;
    color: #cbd5e1;
    display: inline-flex;
    align-items: center;
}
.cat-bc-current {
    color: var(--color-heading, #0f172a);
    font-weight: 600;
}

/* ── Hero Category Header ── */
.cat-page-head {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px solid var(--color-border, #e2e8f0);
    border-radius: var(--radius-lg, 12px);
    padding: 24px 28px;
    margin-bottom: 20px;
    position: relative;
    overflow: hidden;
}
.cat-page-head::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: var(--color-primary, #0068e1);
    border-radius: 4px 0 0 4px;
}
.cat-head-content {
    position: relative;
    z-index: 1;
}
.cat-title-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 6px;
    flex-wrap: wrap;
}
.cat-page-title {
    font-size: 26px;
    font-weight: 800;
    color: var(--color-heading, #0f172a);
    margin: 0;
    line-height: 1.2;
    letter-spacing: -0.02em;
}
.cat-count-pill {
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
.cat-page-sub {
    font-size: 14px;
    color: var(--color-muted, #64748b);
    margin: 0;
    max-width: 720px;
    line-height: 1.5;
}

/* ── Subcategories Bar ── */
.subcats-card {
    background: #ffffff;
    border: 1px solid var(--color-border, #e2e8f0);
    border-radius: var(--radius-lg, 12px);
    padding: 16px 20px;
    margin-bottom: 20px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.02);
}
.subcats-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
    flex-wrap: wrap;
    gap: 8px;
}
.subcats-label {
    font-size: 13px;
    font-weight: 700;
    color: var(--color-heading, #0f172a);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.subcats-label i {
    color: var(--color-primary, #0068e1);
}
.subcats-hint {
    font-size: 12px;
    color: var(--color-muted, #64748b);
}
.subcats-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
.subcat-pill {
    padding: 6px 14px;
    background: #f8fafc;
    border: 1px solid var(--color-border, #e2e8f0);
    border-radius: 100px;
    font-size: 12.5px;
    font-weight: 500;
    color: var(--color-heading, #0f172a);
    text-decoration: none;
    transition: all 0.2s ease;
}
.subcat-pill:hover {
    background: var(--color-primary, #0068e1);
    border-color: var(--color-primary, #0068e1);
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0, 104, 225, 0.25);
}

/* ── Toolbar ── */
.cat-toolbar {
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
.cat-results-info {
    font-size: 13.5px;
    color: var(--color-muted, #64748b);
}
.cat-results-info strong {
    color: var(--color-heading, #0f172a);
    font-weight: 700;
}
.cat-toolbar-right {
    display: flex;
    align-items: center;
    gap: 10px;
}
.cat-sort-label {
    font-size: 13px;
    font-weight: 600;
    color: var(--color-muted, #64748b);
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin: 0;
}
.cat-select-wrapper {
    position: relative;
    display: inline-block;
}
.cat-sort-select {
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
.cat-sort-select:focus {
    border-color: var(--color-primary, #0068e1);
    box-shadow: 0 0 0 3px rgba(0, 104, 225, 0.15);
}
.cat-select-arrow {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    font-size: 12px;
    color: var(--color-muted, #64748b);
}

/* ── Product Grid ── */
.cat-products-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    background: transparent;
    border: none;
    padding: 0;
    margin-bottom: 24px;
}
.cat-pagination-wrap {
    margin-top: 24px;
    display: flex;
    justify-content: center;
}

/* ── Empty State ── */
.cat-empty {
    background: #ffffff;
    border: 1px solid var(--color-border, #e2e8f0);
    border-radius: var(--radius-lg, 12px);
    padding: 60px 24px;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.cat-empty-icon-wrap {
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
.cat-empty-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--color-heading, #0f172a);
    margin: 0 0 8px;
}
.cat-empty-text {
    font-size: 14px;
    color: var(--color-muted, #64748b);
    max-width: 440px;
    margin: 0 auto 22px;
    line-height: 1.5;
}
.cat-empty-actions {
    display: flex;
    justify-content: center;
}
.cat-btn-primary {
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
.cat-btn-primary:hover {
    background: var(--color-primary-hover, #0051b3);
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(0, 104, 225, 0.35);
}

/* ── Responsive ── */
@media (max-width: 1200px) {
    .cat-products-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
    }
}
@media (max-width: 900px) {
    .cat-page-head {
        padding: 20px;
    }
    .cat-page-title {
        font-size: 22px;
    }
}
@media (max-width: 768px) {
    .cat-page-wrap {
        padding: 12px 14px 40px;
    }
    .cat-page-head {
        padding: 16px 18px;
        margin-bottom: 16px;
    }
    .cat-page-title {
        font-size: 20px;
    }
    .cat-toolbar {
        padding: 10px 14px;
        margin-bottom: 16px;
    }
    .cat-products-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    .cat-empty {
        padding: 44px 16px;
    }
}
@media (max-width: 480px) {
    .cat-page-wrap {
        padding: 10px 10px 32px;
    }
    .cat-page-title {
        font-size: 18px;
    }
    .cat-toolbar {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    .cat-toolbar-right {
        width: 100%;
        justify-content: space-between;
    }
    .cat-select-wrapper,
    .cat-sort-select {
        width: 100%;
    }
    .cat-products-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
}
</style>
@endpush

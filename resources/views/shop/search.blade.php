@extends('layouts.shop')

@section('title', ($q !== '' ? 'Search: ' . $q . ' — ' : 'Search Products — ') . $storeName)

@php
    use Illuminate\Support\Str;
@endphp

@section('content')
<div class="search-page-wrap">

    {{-- ── Breadcrumbs ── --}}
    <nav class="search-breadcrumbs" aria-label="Breadcrumb">
        <a href="{{ url('/') }}" class="search-bc-link"><i class="las la-home"></i> Home</a>
        <span class="search-bc-sep"><i class="las la-angle-right"></i></span>
        <span class="search-bc-current">Search Results</span>
    </nav>

    {{-- ── Search Hero Header ── --}}
    <div class="search-page-head">
        <div class="search-head-content">
            <div class="search-title-row">
                <h1 class="search-page-title">
                    @if($q !== '')
                        Results for <span class="search-highlight">"{{ $q }}"</span>
                    @else
                        Search Products
                    @endif
                </h1>
                @if($q !== '' && $products->count() > 0)
                    <span class="search-count-pill">
                        {{ $products->total() }} {{ Str::plural('Result', $products->total()) }}
                    </span>
                @endif
            </div>
            @if($q !== '')
                <p class="search-page-sub">Showing products matching your search query. Refine your search if needed.</p>
            @else
                <p class="search-page-sub">Use the search bar above to discover trending apparel, best sellers, and everyday essentials.</p>
            @endif
        </div>
    </div>

    @if($q !== '' && $products->count() > 0)
        {{-- ── Toolbar ── --}}
        <div class="search-toolbar">
            <span class="search-results-info">
                Showing <strong>{{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? $products->count() }}</strong> of <strong>{{ $products->total() }}</strong> items
            </span>
            <div class="search-active-pill">
                Query: <strong>{{ $q }}</strong>
                <a href="{{ url('/shop') }}" title="Clear search"><i class="las la-times"></i></a>
            </div>
        </div>

        {{-- ── Products Grid ── --}}
        <div class="products-grid search-products-grid">
            @foreach($products as $product)
                @include('shop.partials.product-card', ['product' => $product])
            @endforeach
        </div>

        <div class="search-pagination-wrap">
            {{ $products->withQueryString()->links('pagination::simple-default') }}
        </div>
    @elseif($q !== '')
        <div class="search-empty">
            <div class="search-empty-icon-wrap">
                <i class="las la-search"></i>
            </div>
            <h3 class="search-empty-title">No matching products found</h3>
            <p class="search-empty-text">We couldn't find any products matching "<strong>{{ $q }}</strong>". Try checking for spelling errors or searching for broader terms.</p>
            <div class="search-empty-actions">
                <a href="{{ url('/shop') }}" class="search-btn-primary">
                    <i class="las la-shopping-bag"></i> Browse All Products
                </a>
                <a href="{{ url('/') }}" class="search-btn-secondary">
                    <i class="las la-home"></i> Back to Home
                </a>
            </div>
        </div>
    @else
        <div class="search-empty">
            <div class="search-empty-icon-wrap">
                <i class="las la-search"></i>
            </div>
            <h3 class="search-empty-title">Ready to find something great?</h3>
            <p class="search-empty-text">Type in product names, categories, or styles in the search bar above.</p>
            <div class="search-empty-actions">
                <a href="{{ url('/shop') }}" class="search-btn-primary">
                    <i class="las la-shopping-bag"></i> Explore Full Catalog
                </a>
            </div>
        </div>
    @endif

</div>
@endsection

@push('styles')
<style>
/* ── Page Layout ── */
.search-page-wrap {
    max-width: 1440px;
    margin: 0 auto;
    padding: 16px 20px 60px;
}

/* ── Breadcrumbs ── */
.search-breadcrumbs {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: var(--color-muted, #64748b);
    margin-bottom: 16px;
    flex-wrap: wrap;
}
.search-bc-link {
    color: var(--color-muted, #64748b);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: color 0.15s ease;
}
.search-bc-link:hover {
    color: var(--color-primary, #0068e1);
}
.search-bc-sep {
    font-size: 11px;
    color: #cbd5e1;
    display: inline-flex;
    align-items: center;
}
.search-bc-current {
    color: var(--color-heading, #0f172a);
    font-weight: 600;
}

/* ── Search Hero Header ── */
.search-page-head {
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px solid var(--color-border, #e2e8f0);
    border-radius: var(--radius-lg, 12px);
    padding: 24px 28px;
    margin-bottom: 20px;
    position: relative;
    overflow: hidden;
}
.search-page-head::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: var(--color-primary, #0068e1);
    border-radius: 4px 0 0 4px;
}
.search-head-content {
    position: relative;
    z-index: 1;
}
.search-title-row {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 6px;
    flex-wrap: wrap;
}
.search-page-title {
    font-size: 26px;
    font-weight: 800;
    color: var(--color-heading, #0f172a);
    margin: 0;
    line-height: 1.2;
    letter-spacing: -0.02em;
}
.search-highlight {
    color: var(--color-primary, #0068e1);
}
.search-count-pill {
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
.search-page-sub {
    font-size: 14px;
    color: var(--color-muted, #64748b);
    margin: 0;
    max-width: 720px;
    line-height: 1.5;
}

/* ── Toolbar ── */
.search-toolbar {
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
.search-results-info {
    font-size: 13.5px;
    color: var(--color-muted, #64748b);
}
.search-results-info strong {
    color: var(--color-heading, #0f172a);
    font-weight: 700;
}
.search-active-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: rgba(0, 104, 225, 0.08);
    border: 1px solid rgba(0, 104, 225, 0.2);
    border-radius: 20px;
    padding: 3px 10px 3px 12px;
    font-size: 12px;
    color: var(--color-muted, #64748b);
}
.search-active-pill strong {
    color: var(--color-primary, #0068e1);
}
.search-active-pill a {
    color: var(--color-primary, #0068e1);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    margin-left: 2px;
}
.search-active-pill a:hover {
    background: var(--color-primary, #0068e1);
    color: #ffffff;
}

/* ── Product Grid ── */
.search-products-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 20px;
    background: transparent;
    border: none;
    padding: 0;
    margin-bottom: 24px;
}
.search-pagination-wrap {
    margin-top: 24px;
    display: flex;
    justify-content: center;
}

/* ── Empty State ── */
.search-empty {
    background: #ffffff;
    border: 1px solid var(--color-border, #e2e8f0);
    border-radius: var(--radius-lg, 12px);
    padding: 60px 24px;
    text-align: center;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
}
.search-empty-icon-wrap {
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
.search-empty-title {
    font-size: 20px;
    font-weight: 700;
    color: var(--color-heading, #0f172a);
    margin: 0 0 8px;
}
.search-empty-text {
    font-size: 14px;
    color: var(--color-muted, #64748b);
    max-width: 480px;
    margin: 0 auto 22px;
    line-height: 1.5;
}
.search-empty-actions {
    display: flex;
    justify-content: center;
    gap: 12px;
    flex-wrap: wrap;
}
.search-btn-primary {
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
.search-btn-primary:hover {
    background: var(--color-primary-hover, #0051b3);
    color: #ffffff;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(0, 104, 225, 0.35);
}
.search-btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #ffffff;
    color: var(--color-heading, #0f172a);
    border: 1px solid var(--color-border, #e2e8f0);
    padding: 10px 24px;
    border-radius: var(--radius-md, 8px);
    font-weight: 600;
    text-decoration: none;
    font-size: 13.5px;
    transition: all 0.15s ease;
}
.search-btn-secondary:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}

/* ── Responsive ── */
@media (max-width: 1200px) {
    .search-products-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 18px;
    }
}
@media (max-width: 900px) {
    .search-page-head {
        padding: 20px;
    }
    .search-page-title {
        font-size: 22px;
    }
}
@media (max-width: 768px) {
    .search-page-wrap {
        padding: 12px 14px 40px;
    }
    .search-page-head {
        padding: 16px 18px;
        margin-bottom: 16px;
    }
    .search-page-title {
        font-size: 20px;
    }
    .search-toolbar {
        padding: 10px 14px;
        margin-bottom: 16px;
    }
    .search-products-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }
    .search-empty {
        padding: 44px 16px;
    }
}
@media (max-width: 480px) {
    .search-page-wrap {
        padding: 10px 10px 32px;
    }
    .search-page-title {
        font-size: 18px;
    }
    .search-products-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }
}
</style>
@endpush

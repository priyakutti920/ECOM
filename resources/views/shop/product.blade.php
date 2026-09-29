@extends('layouts.shop')

@php
    $basePrice = (float) $product->effective_price;
    $original = (float) $product->price;
    $isOnSale = $product->is_on_sale;
    $discount = 0;
    if ($isOnSale && $original > 0) {
        $discount = round((($original - $basePrice) / $original) * 100);
    }
    $inStock = $product->stock_status === 'in_stock' && ($product->qty === null || $product->qty > 0);
    $productUrl = url('/product/' . ($product->slug ?: $product->id));
    $imageUrl = $product->image_url ?: 'https://via.placeholder.com/600x600?text=No+Image';
    $allImages = $product->images->count() > 0
        ? $product->images->pluck('image')->map(fn($i) => str_starts_with($i, 'http') ? $i : (str_starts_with($i, 'product/') || str_starts_with($i, 'category/') ? asset($i) : asset('storage/' . $i)))->toArray()
        : [$imageUrl];

    $avgRating = $product->average_rating;
    $reviewCount = $product->rating_count;
    $ratingDist = $product->rating_distribution;
    $currencySymbol = \App\Models\StoreSetting::getCurrencySymbol();
@endphp

@section('title', $product->meta_title ?: ($product->name . ' — ' . $storeName))
@section('description', $product->meta_description ?: Str::limit(strip_tags($product->short_description ?: $product->description ?: $product->name), 160))

@push('meta')
    <meta name="keywords" content="{{ $product->meta_keywords ?? '' }}">
    <link rel="canonical" href="{{ $productUrl }}">
    <!-- Open Graph -->
    <meta property="og:title" content="{{ $product->og_title ?: $product->name }}">
    <meta property="og:description" content="{{ $product->og_description ?: Str::limit(strip_tags($product->short_description ?: $product->description), 160) }}">
    <meta property="og:image" content="{{ $product->og_image_url ?: $imageUrl }}">
    <meta property="og:url" content="{{ $productUrl }}">
    <meta property="og:type" content="product">
    <meta property="product:price:amount" content="{{ $basePrice }}">
    <meta property="product:price:currency" content="INR">
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $product->og_title ?: $product->name }}">
    <meta name="twitter:description" content="{{ $product->og_description ?: Str::limit(strip_tags($product->short_description ?: $product->description), 160) }}">
    <meta name="twitter:image" content="{{ $product->og_image_url ?: $imageUrl }}">

    <!-- Product Structured Data (JSON-LD) -->
    @php
        $schemaData = [
            '@context' => 'https://schema.org/',
            '@type' => 'Product',
            'name' => $product->name,
            'image' => $allImages,
            'description' => strip_tags($product->short_description ?: $product->description ?: $product->name),
            'sku' => $product->sku ?: 'SKU-' . $product->id,
            'offers' => [
                '@type' => 'Offer',
                'url' => $productUrl,
                'priceCurrency' => 'INR',
                'price' => (string) $basePrice,
                'availability' => $inStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'itemCondition' => 'https://schema.org/NewCondition',
            ]
        ];
        if ($reviewCount > 0) {
            $schemaData['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => (string) $avgRating,
                'reviewCount' => (string) $reviewCount,
            ];
        }
    @endphp
    <script type="application/ld+json">
    {!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
@endpush

@push('styles')
<style>
.pdp-container { max-width: 1400px; margin: 16px auto; padding: 0 16px; }
.pdp-breadcrumb { font-size: 13px; color: var(--medium-gray); margin-bottom: 16px; padding: 12px 16px; background: #fff; border-radius: 4px; }
.pdp-breadcrumb a { color: var(--amazon-blue); text-decoration: none; }
.pdp-breadcrumb i { font-size: 9px; margin: 0 6px; color:#cbd5e1; }
.pdp-main { display: grid; grid-template-columns: 1.2fr 1.5fr 0.9fr; gap: 20px; background: #fff; padding: 24px; border-radius: 6px; border: 1px solid #e7e7e7; }
.pdp-gallery { display: flex; flex-direction: column; gap: 12px; }
.pdp-main-img { width: 100%; aspect-ratio: 1/1; background: #f8fafc; border-radius: 6px; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid #f1f5f9; }
.pdp-main-img img { max-width: 90%; max-height: 90%; object-fit: contain; transition: transform 0.2s ease; }
.pdp-main-img:hover img { transform: scale(1.05); }
.pdp-thumbs { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 4px; }
.pdp-thumb { width: 64px; height: 64px; flex-shrink: 0; border: 2px solid transparent; border-radius: 6px; cursor: pointer; padding: 4px; background: #f8fafc; transition: all 0.2s; }
.pdp-thumb img { width: 100%; height: 100%; object-fit: contain; }
.pdp-thumb.active { border-color: var(--amazon-orange); box-shadow: 0 0 0 1px var(--amazon-orange); }
.pdp-info { display: flex; flex-direction: column; gap: 14px; }
.pdp-brand { font-size: 13px; color: var(--amazon-blue); font-weight: 500; text-decoration: none; }
.pdp-title { font-size: 22px; font-weight: 600; line-height: 1.35; color: #0F1111; margin: 0; }
.pdp-rating-row { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px solid #e7e7e7; font-size: 13px; }
.pdp-rating-row .rating-badge { background: #ffa41c; color: #fff; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; }
.pdp-rating-row a { color: var(--amazon-blue); text-decoration: none; font-weight: 500; }
.pdp-rating-row a:hover { text-decoration: underline; color: #c45500; }
.pdp-price-block { background: #fafafa; padding: 12px 16px; border-radius: 6px; border: 1px solid #f0f0f0; }
.pdp-price { display: flex; align-items: baseline; gap: 8px; }
.pdp-price .sym { font-size: 18px; font-weight: 600; color: #0F1111; }
.pdp-price .amt { font-size: 32px; font-weight: 700; color: #0F1111; line-height: 1.1; }
.pdp-orig { font-size: 14px; color: #565959; text-decoration: line-through; }
.pdp-save { color: #cc0c39; font-size: 14px; font-weight: 700; }
.pdp-tax { font-size: 12px; color: #565959; margin-top: 4px; }

/* Variations & Swatches */
.pdp-section-title { font-size: 13px; font-weight: 700; color: #0F1111; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
.pdp-variation-chips { display: flex; flex-wrap: wrap; gap: 8px; }
.pdp-var-chip { border: 1px solid #d5d9d9; border-radius: 4px; padding: 6px 12px; font-size: 13px; font-weight: 500; background: #fff; cursor: pointer; transition: all 0.15s; }
.pdp-var-chip:hover { border-color: #0F1111; }
.pdp-var-chip.active { border-color: #e77600; background: #fef8f2; color: #111; font-weight: 700; box-shadow: 0 0 0 1px #e77600; }
.pdp-var-chip.out-of-stock { opacity: 0.5; text-decoration: line-through; cursor: not-allowed; }

.pdp-color-swatches { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
.pdp-color-swatch { width: 32px; height: 32px; border-radius: 50%; border: 2px solid #d5d9d9; cursor: pointer; transition: all 0.2s; position: relative; }
.pdp-color-swatch:hover { transform: scale(1.1); }
.pdp-color-swatch.active { border-color: #e77600; box-shadow: 0 0 0 2px #fff, 0 0 0 4px #e77600; }

.pdp-options-list { display: flex; flex-direction: column; gap: 8px; }
.pdp-option-item { display: flex; align-items: center; gap: 10px; padding: 8px 12px; background: #fdfdfd; border: 1px solid #e7e7e7; border-radius: 4px; cursor: pointer; font-size: 13px; transition: background 0.15s; }
.pdp-option-item:hover { background: #f8fafc; }
.pdp-option-item input[type="checkbox"] { width: 16px; height: 16px; accent-color: #e77600; cursor: pointer; }

/* Buy Box */
.pdp-buybox { padding: 20px; border: 1px solid #d5d9d9; border-radius: 6px; display: flex; flex-direction: column; gap: 14px; background: #fff; align-self: start; box-shadow: 0 1px 4px rgba(0,0,0,0.04); }
.pdp-buybox .price { font-size: 28px; font-weight: 700; color: #b12704; }
.pdp-stock { color: #007600; font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 6px; }
.pdp-stock.oos { color: #b12704; }
.pdp-qty-row { display: flex; align-items: center; gap: 10px; justify-content: space-between; }
.pdp-qty-row .qty-label { font-size: 13px; font-weight: 600; color: #0F1111; }
.pdp-qty-selector { display: flex; align-items: stretch; border: 1px solid #d5d9d9; border-radius: 20px; overflow: hidden; height: 34px; background: #fff; }
.pdp-qty-selector button { width: 34px; height: 100%; background: #f0f2f2; border: none; cursor: pointer; font-size: 16px; font-weight: 700; color: #0F1111; display: flex; align-items: center; justify-content: center; }
.pdp-qty-selector button:hover { background: #e7e7e7; }
.pdp-qty-selector button:disabled { opacity: 0.4; cursor: not-allowed; }
.pdp-qty-selector .qty-val { min-width: 40px; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; color: #0F1111; border-left: 1px solid #d5d9d9; border-right: 1px solid #d5d9d9; }
.pdp-btn-add { background: #ffd814; border: 1px solid #fcd200; border-radius: 100px; padding: 12px 16px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s; color: #0F1111; }
.pdp-btn-add:hover { background: #f7ca00; }
.pdp-btn-buy { background: #ffa41c; border: 1px solid #ff8f00; border-radius: 100px; padding: 12px 16px; font-size: 14px; font-weight: 600; color: #0F1111; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s; }
.pdp-btn-buy:hover { background: #fa8900; }
.pdp-btn-wish { background: #fff; border: 1px solid #d5d9d9; border-radius: 100px; padding: 10px 16px; font-size: 13px; font-weight: 600; color: #0F1111; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.2s; }
.pdp-btn-wish:hover { background: #f8fafc; border-color: #adb1b8; }
.pdp-secure { font-size: 12px; color: #565959; display: flex; align-items: center; gap: 6px; }

/* Description */
.pdp-description { font-size: 14px; line-height: 1.7; color: #0F1111; margin-top: 8px; }
.pdp-description h2, .pdp-description h3 { font-size: 16px; font-weight: 700; margin: 12px 0 6px; }
.pdp-description p { margin: 8px 0; }
.pdp-description ul { margin: 8px 0 8px 20px; }

/* Reviews Section */
.pdp-reviews-wrap { margin-top: 32px; background: #fff; border: 1px solid #e7e7e7; border-radius: 6px; padding: 24px; }
.pdp-reviews-grid { display: grid; grid-template-columns: 340px 1fr; gap: 36px; }
.rating-bar-row { display: flex; align-items: center; gap: 8px; font-size: 13px; margin-bottom: 6px; }
.rating-bar-track { flex: 1; height: 16px; background: #f0f2f2; border-radius: 3px; overflow: hidden; }
.rating-bar-fill { height: 100%; background: #ffa41c; }
.review-item { padding: 16px 0; border-bottom: 1px solid #f0f0f0; }
.review-item:last-child { border-bottom: none; }
.review-user-name { font-size: 13px; font-weight: 700; color: #0F1111; }
.review-stars { color: #ffa41c; font-size: 13px; }
.review-title { font-size: 14px; font-weight: 700; color: #0F1111; margin: 4px 0; }
.review-comment { font-size: 13px; line-height: 1.6; color: #333; margin: 6px 0 0; }
.verified-badge { color: #c45500; font-size: 11px; font-weight: 700; margin-left: 6px; }

@media (max-width: 992px) {
    .pdp-main { grid-template-columns: 1fr; }
    .pdp-reviews-grid { grid-template-columns: 1fr; gap: 20px; }
}
</style>
@endpush

@section('content')
<div class="pdp-container">

    <!-- Breadcrumb -->
    <div class="pdp-breadcrumb">
        <a href="{{ url('/') }}">Home</a>
        <i class="fas fa-chevron-right"></i>
        @if($product->categories->count() > 0)
            @php $primaryCategory = $product->categories->first(); @endphp
            <a href="{{ url('/category/' . ($primaryCategory->slug ?: $primaryCategory->id)) }}">{{ $primaryCategory->name }}</a>
            <i class="fas fa-chevron-right"></i>
        @endif
        <span>{{ $product->name }}</span>
    </div>

    <!-- Main product area -->
    <div class="pdp-main">
        <!-- Gallery -->
        <div class="pdp-gallery">
            <div class="pdp-main-img" id="mainImageWrap">
                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" id="mainImage">
            </div>
            @if(count($allImages) > 1)
                <div class="pdp-thumbs">
                    @foreach($allImages as $idx => $img)
                        <div class="pdp-thumb {{ $idx === 0 ? 'active' : '' }}" onclick="switchImage('{{ $img }}', this)">
                            <img src="{{ $img }}" alt="thumb {{ $idx + 1 }}">
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Info -->
        <div class="pdp-info">
            @if($product->categories->count() > 0)
                <a href="{{ url('/category/' . ($product->categories->first()->slug ?: $product->categories->first()->id)) }}" class="pdp-brand">
                    {{ $product->categories->first()->name }}
                </a>
            @endif
            <h1 class="pdp-title">{{ $product->name }}</h1>

            <!-- Ratings Row -->
            <div class="pdp-rating-row">
                @if($reviewCount > 0)
                    <span class="rating-badge"><i class="fas fa-star"></i> {{ number_format($avgRating, 1) }}</span>
                    <a href="#customer-reviews">{{ $reviewCount }} {{ Str::plural('rating', $reviewCount) }}</a>
                @else
                    <span style="color:#565959; font-size:13px;"><i class="far fa-star"></i> No reviews yet</span>
                    <a href="#write-review" style="color:var(--amazon-blue); font-size:13px;">Write the first review</a>
                @endif
                @if($product->sku)
                    <span style="color:#565959; margin-left:auto; font-size:12px;">SKU: <strong id="pdp-sku-display">{{ $product->sku }}</strong></span>
                @endif
            </div>

            <!-- Price Block -->
            <div class="pdp-price-block">
                <div class="pdp-price">
                    <span class="sym">{{ $currencySymbol }}</span>
                    <span class="amt" id="pdp-display-price">{{ number_format($basePrice, 0) }}</span>
                </div>
                <div id="pdp-mrp-wrapper" style="{{ $discount >= 5 ? '' : 'display:none;' }}">
                    <span class="pdp-orig">M.R.P.: <span style="text-decoration:line-through;" id="pdp-display-mrp">{{ $currencySymbol }}{{ number_format($original, 0) }}</span></span>
                    <span class="pdp-save" id="pdp-display-discount"> ({{ $discount }}% off)</span>
                </div>
                <div class="pdp-tax">Inclusive of all taxes</div>
            </div>

            <!-- Product Variations (Size, Storage, Material, etc.) -->
            @if($product->variations->count() > 0)
                <div>
                    <div class="pdp-section-title">Select Variation / Size:</div>
                    <div class="pdp-variation-chips" id="variation-chips">
                        @foreach($product->variations as $idx => $v)
                            @php
                                $varPrice = (float) ($v->sale_price ?: $v->price ?: $basePrice);
                                $varMrp = (float) ($v->price ?: $original);
                                $varStock = (int) ($v->stock ?? $product->qty ?? 10);
                                $varInStock = $varStock > 0 && ($v->status ?? 1) == 1;
                            @endphp
                            <button type="button" 
                                class="pdp-var-chip {{ $idx === 0 ? 'active' : '' }} {{ !$varInStock ? 'out-of-stock' : '' }}"
                                data-id="{{ $v->id }}"
                                data-name="{{ $v->name }}"
                                data-price="{{ $varPrice }}"
                                data-mrp="{{ $varMrp }}"
                                data-stock="{{ $varStock }}"
                                data-instock="{{ $varInStock ? '1' : '0' }}"
                                data-sku="{{ $v->sku ?: $product->sku }}"
                                onclick="selectVariation(this)">
                                {{ $v->name }}
                                <span style="font-size:11px; opacity:0.8; margin-left:4px;">({{ $currencySymbol }}{{ number_format($varPrice, 0) }})</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Color Options -->
            @if($product->colors->count() > 0)
                <div>
                    <div class="pdp-section-title">Color: <span id="selected-color-label" style="font-weight:normal; text-transform:none;">{{ $product->colors->first()->name }}</span></div>
                    <div class="pdp-color-swatches">
                        @foreach($product->colors as $cIdx => $c)
                            <div class="pdp-color-swatch {{ $cIdx === 0 ? 'active' : '' }}" 
                                 style="background-color: {{ $c->code ?: '#ccc' }};"
                                 title="{{ $c->name }}"
                                 data-color-name="{{ $c->name }}"
                                 onclick="selectColor('{{ $c->name }}', this)">
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Product Options / Add-ons (e.g., Gift Wrap, Warranty) -->
            @if($product->options->count() > 0)
                <div>
                    <div class="pdp-section-title">Add-on Services / Options:</div>
                    <div class="pdp-options-list">
                        @foreach($product->options as $opt)
                            <label class="pdp-option-item">
                                <input type="checkbox" 
                                       class="pdp-option-checkbox" 
                                       value="{{ $opt->id }}" 
                                       data-name="{{ $opt->name }}"
                                       data-price="{{ (float) $opt->price }}"
                                       onchange="recalculatePrice()">
                                <div style="flex:1;">
                                    <strong>{{ $opt->name }}</strong>
                                    @if($opt->price > 0)
                                        <span style="color:#b12704; font-weight:600; margin-left:4px;">(+{{ $currencySymbol }}{{ number_format($opt->price, 0) }})</span>
                                    @else
                                        <span style="color:#007600; font-weight:600; margin-left:4px;">(Free)</span>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Description -->
            @if($product->short_description || $product->description)
                <div class="pdp-description">
                    <h3 style="font-size: 15px; font-weight: 700; margin: 12px 0 8px; padding-bottom:6px; border-bottom:1px solid #e7e7e7;">About this item</h3>
                    @if($product->short_description)
                        <div style="font-weight:500; margin-bottom:8px;">{!! $product->short_description !!}</div>
                    @endif
                    {!! $product->description !!}
                </div>
            @endif
        </div>

        <!-- Buy box -->
        <div class="pdp-buybox">
            <div class="price" id="buybox-price">{{ $currencySymbol }}{{ number_format($basePrice, 0) }}</div>
            
            <div id="pdp-stock-badge" class="pdp-stock {{ $inStock ? '' : 'oos' }}">
                <i class="fas {{ $inStock ? 'fa-check-circle' : 'fa-times-circle' }}"></i> 
                <span>{{ $inStock ? 'In stock' : 'Out of stock' }}</span>
            </div>

            <div class="pdp-qty-row">
                <span class="qty-label">Quantity:</span>
                <div class="pdp-qty-selector">
                    <button type="button" class="qty-btn-minus" onclick="changeQty(-1)" {{ !$inStock ? 'disabled' : '' }}>−</button>
                    <span class="qty-val" id="pdp-qty-display">1</span>
                    <input type="hidden" id="pdp-qty" value="1">
                    <button type="button" class="qty-btn-plus" onclick="changeQty(1)" {{ !$inStock ? 'disabled' : '' }}>+</button>
                </div>
            </div>

            <button type="button" class="pdp-btn-add" id="btn-add-to-cart" onclick="handleAddToCart()" {{ !$inStock ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : '' }}>
                <i class="fas fa-shopping-cart"></i> Add to Cart
            </button>

            <button type="button" class="pdp-btn-buy" id="btn-buy-now" onclick="handleBuyNow()" {{ !$inStock ? 'disabled style="opacity:0.5;cursor:not-allowed;"' : '' }}>
                <i class="fas fa-bolt"></i> Buy Now
            </button>

            <button type="button" class="pdp-btn-wish" onclick="toggleWishlist({{ $product->id }}, this)">
                <i class="far fa-heart"></i> Add to Wishlist
            </button>

            <div class="pdp-secure">
                <i class="fas fa-lock" style="color:#007600;"></i> Secure transaction
            </div>
            <div style="font-size: 12px; color: #565959;">
                <i class="fas fa-truck"></i> Fast Delivery available
            </div>
            <div style="font-size: 12px; color: #565959;">
                <i class="fas fa-shield-alt"></i> Genuine Guaranteed Product
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if(isset($related) && $related->count() > 0)
        <div style="margin-top: 36px;">
            <h2 style="font-size: 18px; font-weight: 700; color: #0F1111; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid #e7e7e7;">
                Customers who viewed this item also viewed
            </h2>
            <div class="products-grid" style="background:#fff; padding: 16px; border-radius: 6px; border: 1px solid #e7e7e7;">
                @foreach($related as $rProd)
                    @include('shop.partials.product-card', ['product' => $rProd])
                @endforeach
            </div>
        </div>
    @endif

    <!-- Customer Reviews & Rating Moderation Section -->
    <div class="pdp-reviews-wrap" id="customer-reviews">
        <h2 style="font-size: 20px; font-weight: 700; color: #0F1111; margin-bottom: 20px; border-bottom: 1px solid #e7e7e7; padding-bottom: 12px;">
            Customer Reviews
        </h2>

        <div class="pdp-reviews-grid">
            <!-- Left Column: Rating Breakdown -->
            <div>
                <div style="display:flex; align-items:baseline; gap:8px;">
                    <span style="font-size:36px; font-weight:700; color:#0F1111;">{{ number_format($avgRating, 1) }}</span>
                    <span style="font-size:16px; color:#ffa41c;">
                        @for($s = 1; $s <= 5; $s++)
                            <i class="{{ $s <= round($avgRating) ? 'fas' : 'far' }} fa-star"></i>
                        @endfor
                    </span>
                    <span style="font-size:13px; color:#565959;">out of 5</span>
                </div>
                <div style="font-size: 13px; color: #565959; margin-bottom: 16px;">
                    {{ $reviewCount }} global {{ Str::plural('rating', $reviewCount) }}
                </div>

                <!-- Rating Distribution Bars -->
                <div>
                    @foreach([5, 4, 3, 2, 1] as $star)
                        @php
                            $pct = $ratingDist[$star] ?? 0;
                        @endphp
                        <div class="rating-bar-row">
                            <span style="width: 45px; color:var(--amazon-blue);">{{ $star }} star</span>
                            <div class="rating-bar-track">
                                <div class="rating-bar-fill" style="width: {{ $pct }}%;"></div>
                            </div>
                            <span style="width: 35px; text-align:right; color:#565959;">{{ $pct }}%</span>
                        </div>
                    @endforeach
                </div>

                <!-- Write a review button / trigger -->
                <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid #e7e7e7;" id="write-review">
                    <h3 style="font-size: 15px; font-weight: 700; margin-bottom: 6px;">Review this product</h3>
                    <p style="font-size: 13px; color: #565959; margin-bottom: 12px;">Share your thoughts with other customers</p>
                    
                    @if(Auth::guard('customer')->check())
                        <button type="button" class="pdp-btn-wish" onclick="document.getElementById('review-form-wrap').style.display='block'; this.style.display='none';">
                            Write a product review
                        </button>
                    @else
                        <a href="{{ route('shop.login.email') }}" class="pdp-btn-wish" style="text-decoration:none; text-align:center;">
                            Sign in to write a review
                        </a>
                    @endif
                </div>
            </div>

            <!-- Right Column: Form and Approved Reviews List -->
            <div>
                <!-- Review Form -->
                <div id="review-form-wrap" style="display:none; background:#f8fafc; padding:20px; border-radius:6px; border:1px solid #e2e8f0; margin-bottom:24px;">
                    <h3 style="font-size:16px; font-weight:700; margin-bottom:12px; color:#0F1111;">Write your review</h3>
                    <form id="productReviewForm" onsubmit="submitReview(event)">
                        @csrf
                        <div style="margin-bottom:12px;">
                            <label style="font-size:13px; font-weight:600; display:block; margin-bottom:6px;">Overall Rating</label>
                            <div id="star-picker" style="font-size:24px; color:#d5d9d9; cursor:pointer;">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star star-opt" data-val="{{ $i }}" onclick="setStarRating({{ $i }})" onmouseover="highlightStars({{ $i }})" onmouseout="resetStars()"></i>
                                @endfor
                            </div>
                            <input type="hidden" name="rating" id="review-rating-val" value="5" required>
                        </div>

                        <div style="margin-bottom:12px;">
                            <label style="font-size:13px; font-weight:600; display:block; margin-bottom:4px;">Review Title</label>
                            <input type="text" name="title" id="review-title-input" class="form-control" placeholder="What's most important to know?" required style="width:100%; padding:8px 12px; border:1px solid #d5d9d9; border-radius:4px; font-size:13px;">
                        </div>

                        <div style="margin-bottom:14px;">
                            <label style="font-size:13px; font-weight:600; display:block; margin-bottom:4px;">Write your review</label>
                            <textarea name="comment" id="review-comment-input" rows="4" class="form-control" placeholder="What did you like or dislike? What did you use this product for?" required style="width:100%; padding:8px 12px; border:1px solid #d5d9d9; border-radius:4px; font-size:13px;"></textarea>
                        </div>

                        <div style="display:flex; gap:10px;">
                            <button type="submit" class="pdp-btn-buy" style="padding:8px 24px;">Submit Review</button>
                            <button type="button" class="pdp-btn-wish" onclick="document.getElementById('review-form-wrap').style.display='none'; document.getElementById('write-review').querySelector('button').style.display='block';">Cancel</button>
                        </div>
                        <div id="review-msg" style="margin-top:10px; font-size:13px;"></div>
                    </form>
                </div>

                <!-- Existing Reviews List -->
                @if($product->approvedReviews->count() > 0)
                    <div class="reviews-list">
                        @foreach($product->approvedReviews as $rev)
                            <div class="review-item">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <div style="width:28px; height:28px; border-radius:50%; background:#e2e8f0; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:12px; color:#475569;">
                                        {{ strtoupper(substr($rev->customer_name, 0, 1)) }}
                                    </div>
                                    <span class="review-user-name">{{ $rev->customer_name }}</span>
                                    @if($rev->is_verified_purchase)
                                        <span class="verified-badge"><i class="fas fa-check"></i> Verified Purchase</span>
                                    @endif
                                </div>
                                <div style="margin-top:6px; display:flex; align-items:center; gap:8px;">
                                    <span class="review-stars">
                                        @for($st = 1; $st <= 5; $st++)
                                            <i class="{{ $st <= $rev->rating ? 'fas' : 'far' }} fa-star"></i>
                                        @endfor
                                    </span>
                                    <strong class="review-title">{{ $rev->title }}</strong>
                                </div>
                                <div style="font-size:12px; color:#565959; margin-top:2px;">
                                    Reviewed on {{ $rev->created_at ? $rev->created_at->format('d M Y') : 'Recent' }}
                                </div>
                                <p class="review-comment">{{ $rev->comment }}</p>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="text-align:center; padding:32px 16px; color:#565959; background:#fafafa; border-radius:6px;">
                        <i class="far fa-comment-dots" style="font-size:32px; color:#cbd5e1; margin-bottom:8px;"></i>
                        <p style="font-size:14px; margin:0;">No reviews written yet. Be the first to share your experience!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
// State management
let currentBasePrice = {{ $basePrice }};
let currentMrp = {{ $original }};
let currentVariation = null;
let currentColor = @js($product->colors->first()->name ?? null);
let selectedOptionIds = [];
let maxStock = {{ $product->qty > 0 ? min(10, $product->qty) : 10 }};

// Switch gallery image
function switchImage(src, el) {
    document.getElementById('mainImage').src = src;
    document.querySelectorAll('.pdp-thumb').forEach(t => t.classList.remove('active'));
    if (el) el.classList.add('active');
}

// Variation selection
function selectVariation(el) {
    if (el.dataset.instock === '0') return;

    document.querySelectorAll('.pdp-var-chip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');

    currentVariation = {
        id: parseInt(el.dataset.id),
        name: el.dataset.name,
        price: parseFloat(el.dataset.price),
        mrp: parseFloat(el.dataset.mrp),
        stock: parseInt(el.dataset.stock),
        sku: el.dataset.sku
    };

    if (currentVariation.sku) {
        document.getElementById('pdp-sku-display').textContent = currentVariation.sku;
    }

    maxStock = Math.min(10, Math.max(1, currentVariation.stock));
    recalculatePrice();
}

// Color selection
function selectColor(name, el) {
    currentColor = name;
    document.getElementById('selected-color-label').textContent = name;
    document.querySelectorAll('.pdp-color-swatch').forEach(s => s.classList.remove('active'));
    el.classList.add('active');
}

// Recalculate price when variation or options change
function recalculatePrice() {
    let unitPrice = currentVariation ? currentVariation.price : currentBasePrice;
    let unitMrp = currentVariation ? currentVariation.mrp : currentMrp;

    // Add option prices
    selectedOptionIds = [];
    let optionsExtra = 0;
    document.querySelectorAll('.pdp-option-checkbox:checked').forEach(cb => {
        selectedOptionIds.push(parseInt(cb.value));
        optionsExtra += parseFloat(cb.dataset.price || 0);
    });

    let finalPrice = unitPrice + optionsExtra;
    let finalMrp = unitMrp + optionsExtra;

    document.getElementById('pdp-display-price').textContent = Math.round(finalPrice).toLocaleString('en-IN');
    document.getElementById('buybox-price').textContent = '{{ $currencySymbol }}' + Math.round(finalPrice).toLocaleString('en-IN');

    if (finalMrp > finalPrice && finalMrp > 0) {
        let disc = Math.round(((finalMrp - finalPrice) / finalMrp) * 100);
        if (disc >= 5) {
            document.getElementById('pdp-mrp-wrapper').style.display = 'block';
            document.getElementById('pdp-display-mrp').textContent = '{{ $currencySymbol }}' + Math.round(finalMrp).toLocaleString('en-IN');
            document.getElementById('pdp-display-discount').textContent = ' (' + disc + '% off)';
        } else {
            document.getElementById('pdp-mrp-wrapper').style.display = 'none';
        }
    } else {
        document.getElementById('pdp-mrp-wrapper').style.display = 'none';
    }
}

// Quantity controls
function changeQty(delta) {
    let input = document.getElementById('pdp-qty');
    let val = parseInt(input.value) + delta;
    if (val < 1) val = 1;
    if (val > maxStock) val = maxStock;
    input.value = val;
    document.getElementById('pdp-qty-display').textContent = val;
}

// Gather selected product payload for Cart / Buy Now
function getProductPayload() {
    let qty = parseInt(document.getElementById('pdp-qty').value) || 1;
    let unitPrice = currentVariation ? currentVariation.price : currentBasePrice;
    
    // Add checked options
    let selectedOptions = [];
    document.querySelectorAll('.pdp-option-checkbox:checked').forEach(cb => {
        selectedOptions.push({
            id: parseInt(cb.value),
            name: cb.dataset.name,
            price: parseFloat(cb.dataset.price || 0)
        });
    });

    return {
        id: {{ $product->id }},
        qty: qty,
        name: @js($product->name),
        price: unitPrice,
        image: @js($imageUrl),
        slug: @js($product->slug ?: 'product-' . $product->id),
        variation_id: currentVariation ? currentVariation.id : null,
        variation_name: currentVariation ? currentVariation.name : null,
        color: currentColor,
        options: selectedOptions
    };
}

function handleAddToCart() {
    let p = getProductPayload();
    if (typeof window.addToCartDetailed === 'function') {
        window.addToCartDetailed(p);
    } else if (typeof window.addToCart === 'function') {
        window.addToCart(p.id, p.qty, p.name, p.price, p.image, p.slug, p.variation_id, p.variation_name, p.color, p.options);
    }
}

function handleBuyNow() {
    let p = getProductPayload();
    localStorage.setItem('buyNowItem', JSON.stringify(p));
    window.location.href = '{{ url('/buy-now') }}?single=1';
}

// Initialize first variation if present
document.addEventListener('DOMContentLoaded', function() {
    let activeVar = document.querySelector('.pdp-var-chip.active');
    if (activeVar) {
        selectVariation(activeVar);
    }
});

// Star rating interactive picker
let selectedRating = 5;
function highlightStars(val) {
    document.querySelectorAll('.star-opt').forEach(s => {
        let v = parseInt(s.dataset.val);
        s.style.color = v <= val ? '#ffa41c' : '#d5d9d9';
    });
}
function resetStars() {
    highlightStars(selectedRating);
}
function setStarRating(val) {
    selectedRating = val;
    document.getElementById('review-rating-val').value = val;
    highlightStars(val);
}

// Submit review AJAX
async function submitReview(e) {
    e.preventDefault();
    let msgEl = document.getElementById('review-msg');
    msgEl.innerHTML = '<span style="color:#565959;">Submitting review...</span>';

    try {
        let res = await fetch('{{ route('shop.reviews.store', $product->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                rating: document.getElementById('review-rating-val').value,
                title: document.getElementById('review-title-input').value,
                comment: document.getElementById('review-comment-input').value
            })
        });

        let data = await res.json();
        if (data.success) {
            msgEl.innerHTML = '<span style="color:#007600; font-weight:600;"><i class="fas fa-check-circle"></i> ' + data.message + '</span>';
            document.getElementById('productReviewForm').reset();
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            msgEl.innerHTML = '<span style="color:#b12704;"><i class="fas fa-exclamation-circle"></i> ' + (data.message || 'Error submitting review') + '</span>';
        }
    } catch(err) {
        msgEl.innerHTML = '<span style="color:#b12704;">Failed to submit review. Please try again.</span>';
    }
}
</script>
@endpush

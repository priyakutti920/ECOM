@php
  $currency = \App\Models\StoreSetting::getCurrencySymbol();
  $price = (float) ($product->special_price ?: $product->price);
  $original = (float) $product->price;
  $discount = 0;
  if ($product->special_price && $product->price > $product->special_price) {
    $discount = round((($product->price - $product->special_price) / $product->price) * 100);
  }
  $productUrl = url('/product/' . ($product->slug ?: $product->id));
  $imageUrl = $product->image_url;
  $inStock = $product->stock_status === 'in_stock';
  $rating = (float) ($product->average_rating ?? 0);
  $ratingCount = (int) ($product->rating_count ?? 0);
  $catName = $product->categories->first()?->name;
@endphp

<div class="product-card compact-product-card" data-id="{{ $product->id }}" onclick="window.location='{{ $productUrl }}'" role="article" aria-label="{{ $product->name }}" tabindex="0">
  <!-- Top: Badges, Actions, Image -->
  <div class="product-card-top">
    <!-- Context Badge (Sale / New / Featured) -->
    @if($discount >= 5)
      <span class="card-pill-badge badge-sale">-{{ $discount }}%</span>
    @elseif($product->is_featured)
      <span class="card-pill-badge badge-featured">Featured</span>
    @elseif($product->created_at && $product->created_at->diffInDays(now()) <= 7)
      <span class="card-pill-badge badge-new">New</span>
    @endif

    <!-- Wishlist Action -->
    <button type="button" class="btn-wishlist card-wishlist-btn" data-product-id="{{ $product->id }}" onclick="event.stopPropagation(); toggleWishlist({{ $product->id }}, this);" title="Save to wishlist" aria-label="Save to wishlist">
      <i class="lar la-heart"></i>
    </button>

    <!-- Product Image -->
    <div class="product-image">
      @if(!empty($imageUrl))
        <img src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="lazy" width="220" height="220" onerror="this.src='/assets/images/placeholder.png';">
      @else
        <div class="product-image-fallback">
          <i class="las la-image"></i>
        </div>
      @endif

      @if(!$inStock)
        <div class="card-stock-overlay">
          <span>Out of Stock</span>
        </div>
      @endif
    </div>
  </div>

  <!-- Middle: Context, Title, Price, Actions -->
  <div class="product-info">
    <!-- Category & Rating Context Line -->
    <div class="card-meta-row">
      @if(!empty($catName))
        <span class="product-brand" title="{{ $catName }}">{{ $catName }}</span>
      @else
        <span class="product-brand">Apparel</span>
      @endif

      @if($ratingCount > 0)
        <span class="card-rating-badge" title="{{ number_format($rating, 1) }} out of 5 stars ({{ $ratingCount }} reviews)">
          <i class="las la-star"></i> {{ number_format($rating, 1) }} <span class="rating-sub">({{ $ratingCount }})</span>
        </span>
      @endif
    </div>

    <!-- Product Title -->
    <a href="{{ $productUrl }}" class="product-name" title="{{ $product->name }}">
      {{ $product->name }}
    </a>

    <!-- Price & Savings Row -->
    <div class="product-price">
      <span class="current-price">{{ $currency }}{{ number_format($price, 2) }}</span>
      @if($discount >= 5)
        <span class="original-price">{{ $currency }}{{ number_format($original, 2) }}</span>
        <span class="price-discount-tag">{{ $discount }}% OFF</span>
      @endif
    </div>

    <!-- Compact Action Buttons -->
    <div class="card-actions">
      @if($inStock)
        <button type="button" class="btn-add-cart" onclick="event.stopPropagation(); addToCart({{ $product->id }}, 1, @js($product->name), {{ $price }}, @js($imageUrl), @js($product->slug)); openCartSidebar();" title="Add to Cart" aria-label="Add to Cart">
          <i class="las la-shopping-bag"></i> <span>Add</span>
        </button>
        <button type="button" class="btn-buy-now" onclick="event.stopPropagation(); buyNow({{ $product->id }}, 1, @js($product->name), {{ $price }}, @js($imageUrl), @js($product->slug));" title="Buy Now" aria-label="Buy Now">
          <i class="las la-bolt"></i> <span>Buy</span>
        </button>
      @else
        <button type="button" class="btn-add-cart btn-out-of-stock" disabled>
          <i class="las la-ban"></i> <span>Out of Stock</span>
        </button>
      @endif
    </div>
  </div>
</div>

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

<div class="product-card" onclick="window.location='{{ $productUrl }}'" role="article" aria-label="{{ $product->name }}">
  <!-- Top: Badges, Actions, Image -->
  <div class="product-card-top">
    <!-- Badges (Only based on actual DB flags) -->
    <div class="product-badge">
      @if($discount >= 5)
        <span class="product-badge-item" style="background:#ff3366; color:#fff; font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px;">-{{ $discount }}%</span>
      @elseif($product->is_featured)
        <span class="product-badge-item" style="background:var(--color-primary); color:#fff; font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px;">Featured</span>
      @elseif($product->created_at && $product->created_at->diffInDays(now()) <= 7)
        <span class="product-badge-item" style="background:var(--color-primary); color:#fff; font-size:11px; font-weight:700; padding:2px 8px; border-radius:4px;">New</span>
      @endif
    </div>

    <!-- Wishlist Button -->
    <div class="product-card-actions">
      <button type="button" class="btn-wishlist" data-product-id="{{ $product->id }}" onclick="event.stopPropagation(); toggleWishlist({{ $product->id }}, this);" title="Save to wishlist" aria-label="Wishlist">
        <i class="lar la-heart"></i>
      </button>
    </div>

    <!-- Product Image (From DB) -->
    <div class="product-image">
      @if(!empty($imageUrl))
        <img src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="lazy" width="220" height="220">
      @else
        <div style="width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#cbd5e1; font-size:36px; background:#f8fafc;">
          <i class="las la-image"></i>
        </div>
      @endif

      @if(!$inStock)
        <div style="position:absolute; inset:0; background:rgba(255,255,255,0.85); display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; color:#ef4444; text-transform:uppercase;">
          Out of Stock
        </div>
      @endif
    </div>
  </div>

  <!-- Middle: Category, Title, Rating -->
  <div class="product-info">
    @if(!empty($catName))
      <div class="product-brand">{{ $catName }}</div>
    @endif
    
    <a href="{{ $productUrl }}" class="product-name" title="{{ $product->name }}">
      {{ $product->name }}
    </a>

    <div class="product-rating">
      @if($ratingCount > 0)
        <span style="color:var(--color-star); display:inline-flex; align-items:center; gap:2px; font-size:12px;">
          @for($i = 1; $i <= 5; $i++)
            @if($i <= round($rating))
              <i class="las la-star"></i>
            @else
              <i class="lar la-star"></i>
            @endif
          @endfor
        </span>
        <span class="count">({{ $ratingCount }})</span>
      @else
        <span style="color:#cbd5e1; display:inline-flex; align-items:center; gap:2px; font-size:12px;">
          <i class="lar la-star"></i>
          <i class="lar la-star"></i>
          <i class="lar la-star"></i>
          <i class="lar la-star"></i>
          <i class="lar la-star"></i>
        </span>
      @endif
      @if($inStock)
        <span style="margin-left:auto; font-size:11px; color:#10b981; font-weight:600;"><i class="las la-check-circle"></i> In Stock</span>
      @endif
    </div>

    <!-- Price Section -->
    <div class="product-price">
      <span class="current-price">{{ $currency }}{{ number_format($price, 2) }}</span>
      @if($discount >= 5)
        <span class="original-price">{{ $currency }}{{ number_format($original, 2) }}</span>
      @endif
    </div>

    <!-- Add to Cart & Buy Buttons -->
    <div class="card-actions">
      @if($inStock)
        <button type="button" class="btn-add-cart" onclick="event.stopPropagation(); addToCart({{ $product->id }}, 1, @js($product->name), {{ $price }}, @js($imageUrl), @js($product->slug)); openCartSidebar();" title="Add to Cart" aria-label="Add to Cart">
          <i class="las la-shopping-cart" style="font-size:15px;"></i> Add
        </button>
        <button type="button" class="btn-buy-now" onclick="event.stopPropagation(); buyNow({{ $product->id }}, 1, @js($product->name), {{ $price }}, @js($imageUrl), @js($product->slug));" title="Buy Now" aria-label="Buy Now">
          <i class="las la-bolt" style="font-size:14px;"></i> Buy
        </button>
      @else
        <button type="button" class="btn-add-cart" disabled style="grid-column: span 2; opacity:0.6; cursor:not-allowed;">
          <i class="las la-ban"></i> Out of Stock
        </button>
      @endif
    </div>
  </div>
</div>

@php
 $price = (float) ($product->special_price ?: $product->price);
 $original = (float) $product->price;
 $discount = 0;
 if ($product->special_price && $product->price > 0) {
 $discount = round((($product->price - $product->special_price) / $product->price) * 100);
 }
 $productUrl = url('/product/' . ($product->slug ?: $product->id));
 $imageUrl = $product->image_url ?: 'https://via.placeholder.com/300x300?text=No+Image';
 $inStock = $product->stock_status === 'in_stock';
@endphp
<div class="product-card" onclick="window.location='{{ $productUrl }}'">
 @if($discount >= 5)
 <span class="quick-badge">{{ $discount }}% off</span>
 @elseif($product->is_featured)
 <span class="quick-badge" style="background: var(--amazon-blue);">Featured</span>
 @endif
 <button class="wishlist-btn" data-product-id="{{ $product->id }}" onclick="event.stopPropagation(); toggleWishlist({{ $product->id }}, this)">
 <i class="far fa-heart"></i>
 </button>
 <div class="product-image">
 <img src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="lazy">
 </div>
 <div class="product-info">
 @if($product->categories->count() > 0)
 <div class="product-brand">{{ $product->categories->first()->name }}</div>
 @endif
 <div class="product-name">{{ $product->name }}</div>
 <div class="product-price">
 <span class="current-price">₹{{ number_format($price, 0) }}</span>
 @if($discount >= 5)
 <span class="original-price">₹{{ number_format($original, 0) }}</span>
 <span class="discount">({{ $discount }}% off)</span>
 @endif
 </div>
 <div class="product-rating">
 <span class="rating-badge"><i class="fas fa-star"></i> {{ number_format($product->average_rating, 1) }}</span>
 <span class="rating-count">({{ $product->rating_count ?: 12 }})</span>
 </div>
 @if($inStock)
 <div class="prime-badge"><i class="fas fa-check-circle"></i> In Stock</div>
 <div class="card-actions">
 <button class="card-add-cart" onclick="event.stopPropagation(); addToCart({{ $product->id }}, 1, @js($product->name), {{ $price }}, @js($imageUrl), @js($product->slug)); openCartSidebar();" title="Add to Cart">
 <i class="fas fa-shopping-cart"></i> Add
 </button>
 <button class="card-buy-now" onclick="event.stopPropagation(); buyNow({{ $product->id }}, 1, @js($product->name), {{ $price }}, @js($imageUrl), @js($product->slug));" title="Buy Now">
 <i class="fas fa-bolt"></i> Buy Now
 </button>
 </div>
 @else
 <div class="prime-badge" style="color:#c7511f;"><i class="fas fa-times-circle"></i> Out of Stock</div>
 @endif
 </div>
</div>

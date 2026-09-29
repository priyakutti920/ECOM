@extends('layouts.shop')

@section('title', $storeName . ' — Shop Online')
@section('description', 'Shop online at ' . $storeName . ' — Electronics, Fashion, Home & Kitchen, Grocery and more at the best prices.')

@section('content')

@if(!request()->is('products') && $banners->count() > 0)
<div class="banner">
  <div class="banner-slider" id="banner-slider">
    @foreach($banners as $banner)
      <div class="banner-slide" style="background-image: url('{{ $banner->image_url }}');">
        <div class="banner-content">
          @if($banner->primary_text)
            <h2>{{ $banner->primary_text }}</h2>
          @endif
          @if($banner->tagline)
            <p>{{ $banner->tagline }}</p>
          @endif
          @if($banner->show_button && $banner->button_name && $banner->button_link)
            <a href="{{ url($banner->button_link) }}" class="btn">{{ $banner->button_name }}</a>
          @endif
        </div>
      </div>
    @endforeach
  </div>
  <div class="banner-dots" id="banner-dots"></div>
</div>
@endif

@if(isset($flashSaleBanner) && $flashSaleBanner)
@php
    $flashEndTs = $flashSaleBanner->end_date ? \Carbon\Carbon::parse($flashSaleBanner->end_date)->timestamp : now()->endOfDay()->timestamp;
@endphp
<div class="flash-sale-wrapper" style="margin: 16px 0; background: linear-gradient(135deg, #b12704 0%, #ea580c 100%); border-radius: 8px; overflow: hidden; color: #fff; box-shadow: 0 4px 12px rgba(177, 39, 4, 0.2);">
  <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; padding: 20px 24px; gap: 16px;">
    <div style="flex: 1; min-width: 260px;">
      <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.2); padding: 4px 10px; border-radius: 100px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px;">
        <i class="fas fa-bolt"></i> Flash Sale
      </div>
      <h2 style="font-size: 22px; font-weight: 800; margin: 0 0 6px; color: #fff;">{{ $flashSaleBanner->primary_text }}</h2>
      @if($flashSaleBanner->tagline)
        <p style="margin: 0; font-size: 13.5px; opacity: 0.95;">{{ $flashSaleBanner->tagline }}</p>
      @endif
    </div>

    <!-- Countdown Timer -->
    <div style="display: flex; align-items: center; gap: 16px; flex-wrap: wrap;">
      <div class="flash-timer" id="flash-countdown" data-end="{{ $flashEndTs }}" style="display: flex; gap: 8px; align-items: center;">
        <div style="text-align: center; background: rgba(0,0,0,0.3); padding: 8px 12px; border-radius: 6px; min-width: 48px;">
          <span id="flash-h" style="font-size: 20px; font-weight: 800; display: block; line-height: 1;">00</span>
          <span style="font-size: 9px; text-transform: uppercase; opacity: 0.8;">Hours</span>
        </div>
        <span style="font-size: 20px; font-weight: 700;">:</span>
        <div style="text-align: center; background: rgba(0,0,0,0.3); padding: 8px 12px; border-radius: 6px; min-width: 48px;">
          <span id="flash-m" style="font-size: 20px; font-weight: 800; display: block; line-height: 1;">00</span>
          <span style="font-size: 9px; text-transform: uppercase; opacity: 0.8;">Mins</span>
        </div>
        <span style="font-size: 20px; font-weight: 700;">:</span>
        <div style="text-align: center; background: rgba(0,0,0,0.3); padding: 8px 12px; border-radius: 6px; min-width: 48px;">
          <span id="flash-s" style="font-size: 20px; font-weight: 800; display: block; line-height: 1;">00</span>
          <span style="font-size: 9px; text-transform: uppercase; opacity: 0.8;">Secs</span>
        </div>
      </div>

      @if($flashSaleBanner->show_button && $flashSaleBanner->button_link)
        <a href="{{ url($flashSaleBanner->button_link) }}" class="btn" style="background: #fff; color: #b12704; font-weight: 700; padding: 10px 20px; border-radius: 100px; text-decoration: none; box-shadow: 0 2px 6px rgba(0,0,0,0.15);">
          {{ $flashSaleBanner->button_name ?: 'Shop Flash Deals' }} <i class="fas fa-arrow-right" style="margin-left: 4px;"></i>
        </a>
      @endif
    </div>
  </div>
</div>
@endif

<!-- Deal of the Day -->
@if($deals->count() > 0)
<div class="deal-banner">
 <div style="display: flex; align-items: center; gap: 10px;">
 <i class="fas fa-bolt" style="font-size: 20px;"></i>
 <h3>Deal of the Day</h3>
 </div>
 <div class="timer" id="deal-timer" data-end="{{ now()->endOfDay()->timestamp }}">
 <span id="deal-h">00</span>:
 <span id="deal-m">00</span>:
 <span id="deal-s">00</span>
 <span style="background: transparent; color: white; padding: 3px 10px; font-size: 11px;">Ends in</span>
 </div>
</div>
@endif

<!-- Featured Products -->
@if($featured->count() > 0)
<section class="product-section">
 <div class="section-header">
 <h3>Featured Products</h3>
 <a href="/products" class="view-all">See all offers <i class="fas fa-chevron-right"></i></a>
 </div>
 <div class="products-grid">
 @foreach($featured as $product)
 @include('shop.partials.product-card', ['product' => $product])
 @endforeach
 </div>
</section>
@endif

<!-- Shop by Category (round icons: image -> circle, fallback -> icon -> circle) -->
@if($categories->count() > 0)
<section class="product-section">
  <div class="section-header">
  <h3>Shop by Category</h3>
  <a href="{{ url('/shop') }}" class="view-all">View All <i class="fas fa-chevron-right"></i></a>
  </div>
  <div class="cat-tiles-row">
    {{-- "All" tile --}}
    <a href="{{ url('/shop') }}" class="cat-tile" title="All categories">
      <div class="cat-tile-circle" style="background: linear-gradient(135deg, #232f3e 0%, #131921 100%); color: #FF9900; border-color: #131921;">
        <i class="fas fa-grip"></i>
      </div>
      <div class="cat-tile-name">All</div>
    </a>

    @foreach($categories as $cat)
      @include('shop.partials.category-tile', [
        'cat'       => $cat,
        'href'      => url('/shop?category=' . $cat->id),
        'isActive'  => false,
      ])
    @endforeach
  </div>
</section>
@endif

<!-- Deal of the Day Products -->
@if($deals->count() > 0)
<section class="product-section">
 <div class="section-header">
 <h3>Today's Deals</h3>
 <a href="/products" class="view-all">See all offers <i class="fas fa-chevron-right"></i></a>
 </div>
 <div class="products-grid">
 @foreach($deals as $product)
 @include('shop.partials.product-card', ['product' => $product])
 @endforeach
 </div>
</section>
@endif

<!-- Best Sellers -->
@if($bestSellers->count() > 0)
<section class="product-section">
 <div class="section-header">
 <h3>Best Sellers</h3>
 <a href="/products" class="view-all">See all offers <i class="fas fa-chevron-right"></i></a>
 </div>
 <div class="products-grid">
 @foreach($bestSellers as $product)
 @include('shop.partials.product-card', ['product' => $product])
 @endforeach
 </div>
</section>
@endif

<!-- Latest Products -->
@if($latest->count() > 0)
<section class="product-section">
 <div class="section-header">
 <h3>New Arrivals</h3>
 <a href="/products" class="view-all">See all offers <i class="fas fa-chevron-right"></i></a>
 </div>
 <div class="products-grid">
 @foreach($latest as $product)
 @include('shop.partials.product-card', ['product' => $product])
 @endforeach
 </div>
</section>
@endif

@if($featured->count() == 0 && $latest->count() == 0)
<section class="product-section" style="text-align:center; padding:60px 20px;">
 <i class="fas fa-box-open" style="font-size:64px; color:#cbd5e1; margin-bottom:20px; display:block;"></i>
 <h3 style="font-size:20px; font-weight:700; color: var(--amazon-charcoal); margin-bottom:8px;">No products yet</h3>
 <p style="color: var(--medium-gray); font-size:14px;">Add products in the admin panel to populate the storefront.</p>
 <a href="/admin/login" style="display:inline-block; margin-top:20px; background: var(--amazon-orange); color: var(--amazon-dark); padding:10px 24px; border-radius:18px; font-weight:600; text-decoration:none;">Go to Admin Panel</a>
</section>
@endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Deal timer countdown
    const dealTimer = document.getElementById('deal-timer');
    if (dealTimer) {
        const endTs = parseInt(dealTimer.dataset.end) || Math.floor(Date.now() / 1000) + 14400;
        function updateDealTimer() {
            const now = Math.floor(Date.now() / 1000);
            const remaining = Math.max(0, endTs - now);
            const h = Math.floor(remaining / 3600);
            const m = Math.floor((remaining % 3600) / 60);
            const s = remaining % 60;
            document.getElementById('deal-h').textContent = String(h).padStart(2, '0');
            document.getElementById('deal-m').textContent = String(m).padStart(2, '0');
            document.getElementById('deal-s').textContent = String(s).padStart(2, '0');
        }
        updateDealTimer();
        setInterval(updateDealTimer, 1000);
    }

    // Flash sale banner countdown
    const flashTimer = document.getElementById('flash-countdown');
    if (flashTimer) {
        const fEndTs = parseInt(flashTimer.dataset.end) || Math.floor(Date.now() / 1000) + 86400;
        function updateFlashTimer() {
            const now = Math.floor(Date.now() / 1000);
            const remaining = Math.max(0, fEndTs - now);
            const h = Math.floor(remaining / 3600);
            const m = Math.floor((remaining % 3600) / 60);
            const s = remaining % 60;
            const hEl = document.getElementById('flash-h');
            const mEl = document.getElementById('flash-m');
            const sEl = document.getElementById('flash-s');
            if (hEl) hEl.textContent = String(h).padStart(2, '0');
            if (mEl) mEl.textContent = String(m).padStart(2, '0');
            if (sEl) sEl.textContent = String(s).padStart(2, '0');
        }
        updateFlashTimer();
        setInterval(updateFlashTimer, 1000);
    }
});
</script>
@endpush

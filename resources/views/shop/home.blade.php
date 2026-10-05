@extends('layouts.shop')

@php
  $storeName = \App\Models\StoreSetting::getStoreName();
  $freeShippingMin = \App\Models\StoreSetting::getValue('free_shipping_min_amount');
@endphp

@section('title', $storeName . ' — Online Shopping Store')
@section('description', 'Discover handpicked collections at ' . $storeName . '. Enjoy fast delivery, exclusive deals, and secure payment.')

@push('styles')
<style>
/* ── Hero Banner Slider ──────────────────────────────── */
.home-hero-wrap {
  padding: 20px 0 10px;
}
.home-slider {
  position: relative;
  border-radius: var(--radius-lg, 12px);
  overflow: hidden;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
  background: var(--color-heading);
  min-height: 400px;
}
.home-slide {
  position: relative;
  width: 100%;
  min-height: 400px;
  display: flex;
  align-items: center;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  display: none;
  overflow: hidden;
}
.home-slide.active {
  display: flex;
  animation: slideFadeIn 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
@keyframes slideFadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

.home-slide-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(90deg, rgba(14, 30, 62, 0.90) 0%, rgba(14, 30, 62, 0.6) 55%, rgba(14, 30, 62, 0.15) 100%);
  z-index: 1;
}
.home-slide-content {
  position: relative;
  z-index: 2;
  padding: 50px 60px;
  max-width: 650px;
  color: #ffffff;
}
.home-slide.active .home-slide-tagline {
  animation: dropTagline 0.5s cubic-bezier(0.16, 1, 0.3, 1) 0.1s both;
}
.home-slide.active .home-slide-title {
  animation: slideTitleUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.2s both;
}
.home-slide.active .btn-slider {
  animation: floatInBtn 0.6s cubic-bezier(0.16, 1, 0.3, 1) 0.35s both;
}
@keyframes dropTagline {
  from { opacity: 0; transform: translateY(-16px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes slideTitleUp {
  from { opacity: 0; transform: translateY(22px); }
  to { opacity: 1; transform: translateY(0); }
}
@keyframes floatInBtn {
  from { opacity: 0; transform: translateY(16px) scale(0.95); }
  to { opacity: 1; transform: translateY(0) scale(1); }
}

.home-slide-tagline {
  display: inline-block;
  font-size: 13px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  color: #ffffff;
  background: var(--color-primary);
  padding: 5px 14px;
  border-radius: var(--radius-sm);
  margin-bottom: 16px;
  box-shadow: 0 2px 8px rgba(0, 104, 225, 0.3);
}
.home-slide-title {
  font-size: 40px;
  font-weight: 800;
  line-height: 1.2;
  margin-bottom: 14px;
  color: #ffffff;
  letter-spacing: -0.5px;
}
.home-slide-desc {
  font-size: 15px;
  color: rgba(255, 255, 255, 0.88);
  margin-bottom: 24px;
  line-height: 1.55;
}

.btn-slider {
  position: relative;
  overflow: hidden;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: var(--color-primary);
  color: #ffffff;
  font-size: 14px;
  font-weight: 700;
  padding: 13px 30px;
  border-radius: var(--radius-md, 8px);
  text-transform: uppercase;
  letter-spacing: 0.5px;
  transition: all 0.25s cubic-bezier(0.2, 0, 0, 1);
  border: none;
  cursor: pointer;
  box-shadow: 0 4px 14px rgba(0, 104, 225, 0.4);
}
.btn-slider:hover {
  background: var(--color-primary-hover);
  color: #ffffff;
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(0, 104, 225, 0.5);
}
.btn-slider::after {
  content: '';
  position: absolute;
  top: -50%;
  left: -75%;
  width: 50%;
  height: 200%;
  background: linear-gradient(90deg, rgba(255,255,255,0) 0%, rgba(255,255,255,0.3) 50%, rgba(255,255,255,0) 100%);
  transform: rotate(25deg);
  opacity: 0;
  transition: opacity 0.2s;
  pointer-events: none;
}
.btn-slider:hover::after {
  opacity: 1;
  animation: shineSweep 0.85s ease-in-out;
}

.slider-dots {
  position: absolute;
  bottom: 24px;
  left: 60px;
  z-index: 3;
  display: flex;
  gap: 8px;
}
.slider-dot {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background: rgba(255, 255, 255, 0.4);
  cursor: pointer;
  transition: all 0.35s cubic-bezier(0.4, 0, 0.2, 1);
}
.slider-dot:hover {
  background: rgba(255, 255, 255, 0.8);
}
.slider-dot.active {
  width: 32px;
  border-radius: var(--radius-pill);
  background: var(--color-primary);
  box-shadow: 0 0 10px rgba(0, 104, 225, 0.6);
}

/* ── 4-Feature Trust Strip ─────────────────────────── */
.features-wrap {
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg, 12px);
  padding: 24px 28px;
  margin: 20px 0 30px;
  box-shadow: var(--shadow-card);
}
.features-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 24px;
}
.single-feature {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 12px 14px;
  border-radius: var(--radius-md, 8px);
  transition: transform 0.25s cubic-bezier(0.2, 0, 0, 1), background-color 0.2s ease;
}
.single-feature:hover {
  background-color: #f8fafc;
  transform: translateY(-3px);
}
.single-feature .feature-icon {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  background: var(--color-primary-transparent-lite-2);
  color: var(--color-primary);
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 24px;
  flex-shrink: 0;
  transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), background-color 0.2s, color 0.2s;
}
.single-feature:hover .feature-icon {
  background: var(--color-primary);
  color: #ffffff;
  transform: scale(1.15) rotate(7deg);
}
.single-feature .feature-details h6 {
  font-size: 14.5px;
  font-weight: 700;
  color: var(--color-heading);
  margin: 0 0 2px;
}
.single-feature .feature-details span {
  font-size: 12.5px;
  color: var(--color-muted);
}

/* ── Category Tiles Row ─────────────────────────────── */
.category-strip {
  margin-bottom: 34px;
}
.cat-tiles-grid {
  display: grid;
  grid-template-columns: repeat(6, 1fr);
  gap: 16px;
}
.cat-card {
  background: #ffffff;
  border: 1px solid var(--color-border);
  border-radius: var(--radius-lg, 12px);
  padding: 22px 14px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 12px;
  transition: transform 0.3s cubic-bezier(0.2, 0, 0, 1), box-shadow 0.3s cubic-bezier(0.2, 0, 0, 1), border-color 0.2s;
  text-decoration: none;
  box-shadow: var(--shadow-card);
}
.cat-card:hover {
  border-color: rgba(0, 104, 225, 0.4);
  transform: translateY(-5px);
  box-shadow: 0 14px 28px rgba(0, 104, 225, 0.12), 0 2px 6px rgba(0, 0, 0, 0.04);
}
.cat-card-icon {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  background: #f8fafc;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 26px;
  color: var(--color-primary);
  overflow: hidden;
  transition: transform 0.35s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s;
  border: 2px solid transparent;
}
.cat-card:hover .cat-card-icon {
  transform: scale(1.1);
  border-color: var(--color-primary);
  box-shadow: 0 0 0 4px rgba(0, 104, 225, 0.18);
}
.cat-card-icon img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.cat-card-name {
  font-size: 13.5px;
  font-weight: 600;
  color: var(--color-heading);
  line-height: 1.25;
  transition: color 0.15s ease;
}
.cat-card:hover .cat-card-name {
  color: var(--color-primary);
}

/* ── Flash Sale Banner Strip ───────────────────────── */
.flash-sale-strip {
  background: linear-gradient(135deg, var(--color-heading) 0%, #1e293b 100%);
  border-radius: var(--radius-lg, 12px);
  padding: 24px 32px;
  margin-bottom: 34px;
  color: #ffffff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 20px;
  box-shadow: 0 8px 24px rgba(15, 23, 42, 0.15);
}
.flash-sale-left {
  display: flex;
  align-items: center;
  gap: 16px;
}
.flash-badge-pill {
  background: #ff3366;
  color: #ffffff;
  font-size: 12px;
  font-weight: 700;
  padding: 5px 14px;
  border-radius: 100px;
  text-transform: uppercase;
  display: inline-flex;
  align-items: center;
  gap: 6px;
  animation: radarPulse 2s infinite;
}
@keyframes radarPulse {
  0% { box-shadow: 0 0 0 0 rgba(255, 51, 102, 0.7); }
  70% { box-shadow: 0 0 0 12px rgba(255, 51, 102, 0); }
  100% { box-shadow: 0 0 0 0 rgba(255, 51, 102, 0); }
}

.flash-sale-timer-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
}
.flash-box {
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: var(--radius-md, 8px);
  padding: 8px 14px;
  min-width: 56px;
  text-align: center;
  backdrop-filter: blur(4px);
  transition: transform 0.2s;
}
.flash-box:hover {
  transform: translateY(-2px);
}
.flash-box .num {
  font-size: 19px;
  font-weight: 800;
  color: #ffffff;
  display: block;
}
.flash-box .lbl {
  font-size: 10px;
  text-transform: uppercase;
  color: rgba(255, 255, 255, 0.75);
  letter-spacing: 0.5px;
}

@media (max-width: 991px) {
  .features-row { grid-template-columns: repeat(2, 1fr); }
  .cat-tiles-grid { grid-template-columns: repeat(3, 1fr); }
  .home-slide-title { font-size: 28px; }
  .home-slide-content { padding: 30px; }
}
@media (max-width: 576px) {
  .features-row { grid-template-columns: 1fr; }
  .cat-tiles-grid { grid-template-columns: repeat(2, 1fr); }
  .flash-sale-strip { flex-direction: column; text-align: center; }
  .flash-sale-left { flex-direction: column; }
}
</style>
@endpush

@section('content')
<main class="container">

  <!-- 1. Hero Banner Slider (Rendered exclusively from DB banners) -->
  @if($banners && $banners->count() > 0)
    <section class="home-hero-wrap">
      <div class="home-slider" id="homeSlider">
        @foreach($banners as $idx => $banner)
          <div class="home-slide {{ $idx === 0 ? 'active' : '' }}" style="background-image: url('{{ $banner->image_url }}');">
            <div class="home-slide-overlay"></div>
            <div class="home-slide-content">
              @if($banner->tagline)
                <span class="home-slide-tagline">{{ $banner->tagline }}</span>
              @endif
              @if($banner->primary_text)
                <h1 class="home-slide-title">{{ $banner->primary_text }}</h1>
              @endif
              @if($banner->show_button && $banner->button_link)
                <a href="{{ url($banner->button_link) }}" class="btn-slider">
                  {{ $banner->button_name ?: 'Shop Now' }} <i class="las la-arrow-right"></i>
                </a>
              @endif
            </div>
          </div>
        @endforeach

        @if($banners->count() > 1)
          <div class="slider-dots" id="sliderDots">
            @foreach($banners as $idx => $b)
              <div class="slider-dot {{ $idx === 0 ? 'active' : '' }}" onclick="goToSlide({{ $idx }})"></div>
            @endforeach
          </div>
        @endif
      </div>
    </section>
  @endif

  <!-- 2. Trust Features Strip (Dynamic from Database) -->
  @php
    $storeFeatures = \App\Models\StoreSetting::getFeatures();
  @endphp
  @if(!empty($storeFeatures))
    <section class="features-wrap">
      <div class="features-row">
        @foreach($storeFeatures as $feat)
          <div class="single-feature">
            <div class="feature-icon">
              <i class="{{ $feat['icon'] }}"></i>
            </div>
            <div class="feature-details">
              <h6>{{ $feat['title'] }}</h6>
              @if(!empty($feat['desc']))
                <span>{{ $feat['desc'] }}</span>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    </section>
  @endif

  <!-- 3. Explore Categories Strip (Dynamic from Database) -->
  @if($categories && $categories->count() > 0)
    <section class="category-strip">
      <div class="section-header-wrap">
        <h3><i class="las la-th-large" style="color:var(--color-primary);"></i> Explore Categories</h3>
        <a href="{{ url('/products') }}" class="view-all">All Categories <i class="las la-arrow-right"></i></a>
      </div>
      <div class="cat-tiles-grid">
        @foreach($categories->take(6) as $cat)
          <a href="{{ url('/shop?category=' . $cat->id) }}" class="cat-card">
            <div class="cat-card-icon">
              @if($cat->image_url)
                <img src="{{ $cat->image_url }}" alt="{{ $cat->name }}" loading="lazy">
              @else
                <i class="las la-tshirt"></i>
              @endif
            </div>
            <span class="cat-card-name">{{ $cat->name }}</span>
          </a>
        @endforeach
      </div>
    </section>
  @endif

  <!-- 4. Flash Sale Strip (Only if banner exists in DB) -->
  @if(isset($flashSaleBanner) && $flashSaleBanner)
    @php
      $flashEndTs = $flashSaleBanner->end_date ? \Carbon\Carbon::parse($flashSaleBanner->end_date)->timestamp : (time() + 86400);
    @endphp
    <section class="flash-sale-strip">
      <div class="flash-sale-left">
        <span class="flash-badge-pill"><i class="las la-bolt"></i> Flash Sale</span>
        <div>
          @if($flashSaleBanner->primary_text)
            <h4 style="margin:0; font-size:18px; font-weight:700;">{{ $flashSaleBanner->primary_text }}</h4>
          @endif
          @if($flashSaleBanner->tagline)
            <span style="font-size:13px; color:rgba(255,255,255,0.8);">{{ $flashSaleBanner->tagline }}</span>
          @endif
        </div>
      </div>

      <div class="flash-sale-timer-wrap" id="flashTimerWrap" data-end="{{ $flashEndTs }}">
        <div class="flash-box">
          <span class="num" id="f-hours">00</span>
          <span class="lbl">Hours</span>
        </div>
        <span style="font-weight:700; font-size:18px;">:</span>
        <div class="flash-box">
          <span class="num" id="f-mins">00</span>
          <span class="lbl">Mins</span>
        </div>
        <span style="font-weight:700; font-size:18px;">:</span>
        <div class="flash-box">
          <span class="num" id="f-secs">00</span>
          <span class="lbl">Secs</span>
        </div>

        @if($flashSaleBanner->button_link)
          <a href="{{ url($flashSaleBanner->button_link) }}" class="btn-slider" style="padding:8px 20px; font-size:13px; margin-left:12px;">
            {{ $flashSaleBanner->button_name ?: 'Shop Deals' }} <i class="las la-arrow-right"></i>
          </a>
        @endif
      </div>
    </section>
  @endif

  <!-- Dynamic Home Product Sections (Hot Deals, New Arrivals, Best Sellers, Featured Products) -->
  @if(isset($homeSections) && count($homeSections) > 0)
    @foreach($homeSections as $secKey => $section)
      @if(!empty($section['enabled']) && isset($section['products']) && $section['products']->count() > 0)
        <section class="product-section" id="section-{{ $secKey }}">
          <div class="section-header-wrap">
            <h3>
              <i class="{{ $section['icon'] ?? 'las la-star' }}" style="color:{{ $section['icon_color'] ?? 'var(--color-primary)' }};"></i>
              {{ $section['title'] }}
            </h3>
            @if(!empty($section['view_all_url']))
              <a href="{{ url($section['view_all_url']) }}" class="view-all">
                {{ $section['view_all_text'] ?: 'View All' }} <i class="las la-arrow-right"></i>
              </a>
            @endif
          </div>
          <div class="products-grid">
            @foreach($section['products'] as $product)
              @include('shop.partials.product-card', ['product' => $product])
            @endforeach
          </div>
        </section>
      @endif
    @endforeach
  @else
    {{-- Fallback --}}
    @if($featured && $featured->count() > 0)
      <section class="product-section">
        <div class="section-header-wrap">
          <h3><i class="las la-star" style="color:var(--color-primary);"></i> Featured Products</h3>
          <a href="{{ url('/products') }}" class="view-all">View All <i class="las la-arrow-right"></i></a>
        </div>
        <div class="products-grid">
          @foreach($featured as $product)
            @include('shop.partials.product-card', ['product' => $product])
          @endforeach
        </div>
      </section>
    @endif

    @if($deals && $deals->count() > 0)
      <section class="product-section">
        <div class="section-header-wrap">
          <h3><i class="las la-fire" style="color:#ff3366;"></i> Hot Deals</h3>
          <a href="{{ url('/deals') }}" class="view-all">See All Deals <i class="las la-arrow-right"></i></a>
        </div>
        <div class="products-grid">
          @foreach($deals as $product)
            @include('shop.partials.product-card', ['product' => $product])
          @endforeach
        </div>
      </section>
    @endif

    @if($bestSellers && $bestSellers->count() > 0)
      <section class="product-section">
        <div class="section-header-wrap">
          <h3><i class="las la-award" style="color:var(--color-primary);"></i> Best Sellers</h3>
          <a href="{{ url('/products') }}" class="view-all">View All <i class="las la-arrow-right"></i></a>
        </div>
        <div class="products-grid">
          @foreach($bestSellers as $product)
            @include('shop.partials.product-card', ['product' => $product])
          @endforeach
        </div>
      </section>
    @endif

    @if($latest && $latest->count() > 0)
      <section class="product-section">
        <div class="section-header-wrap">
          <h3><i class="las la-tshirt" style="color:var(--color-primary);"></i> New Arrivals</h3>
          <a href="{{ url('/products') }}" class="view-all">Discover More <i class="las la-arrow-right"></i></a>
        </div>
        <div class="products-grid">
          @foreach($latest as $product)
            @include('shop.partials.product-card', ['product' => $product])
          @endforeach
        </div>
      </section>
    @endif
  @endif

  @php
    $totalVisibleProducts = isset($homeSections)
      ? collect($homeSections)->filter(fn($s) => !empty($s['enabled']))->sum(fn($s) => isset($s['products']) ? $s['products']->count() : 0)
      : (($featured ? $featured->count() : 0) + ($latest ? $latest->count() : 0));
  @endphp

  <!-- Empty Catalog State (Only shown if DB has 0 active products) -->
  @if($totalVisibleProducts === 0)
    <section class="product-section" style="text-align:center; padding:60px 20px; background:#fff; border-radius:var(--radius-md); border:1px solid var(--color-border); margin:20px 0;">
      <i class="las la-shopping-bag" style="font-size:60px; color:#cbd5e1; margin-bottom:16px; display:inline-block;"></i>
      <h3 style="font-size:20px; font-weight:700; color:var(--color-heading); margin-bottom:8px;">Welcome to {{ $storeName }}</h3>
      <p style="font-size:14px; color:var(--color-muted); max-width:480px; margin:0 auto 20px;">We are currently restocking our catalog. Please check back soon or browse all products.</p>
      <a href="{{ url('/products') }}" class="btn-slider" style="display:inline-flex;">Browse All Products</a>
    </section>
  @endif

</main>
@endsection

@push('scripts')
<script>
// Hero banner slider rotation
let currentSlide = 0;
const slides = document.querySelectorAll('.home-slide');
const dots = document.querySelectorAll('.slider-dot');

function goToSlide(idx) {
  if (!slides.length) return;
  slides.forEach(s => s.classList.remove('active'));
  dots.forEach(d => d.classList.remove('active'));
  currentSlide = (idx + slides.length) % slides.length;
  slides[currentSlide].classList.add('active');
  if (dots[currentSlide]) dots[currentSlide].classList.add('active');
}

if (slides.length > 1) {
  setInterval(() => {
    goToSlide(currentSlide + 1);
  }, 5000);
}

// Flash timer countdown
const timerWrap = document.getElementById('flashTimerWrap');
if (timerWrap) {
  const endTs = parseInt(timerWrap.dataset.end) || Math.floor(Date.now() / 1000) + 86400;
  function updateTimer() {
    const now = Math.floor(Date.now() / 1000);
    const rem = Math.max(0, endTs - now);
    const h = Math.floor(rem / 3600);
    const m = Math.floor((rem % 3600) / 60);
    const s = rem % 60;
    const hEl = document.getElementById('f-hours');
    const mEl = document.getElementById('f-mins');
    const sEl = document.getElementById('f-secs');
    if (hEl) hEl.textContent = String(h).padStart(2, '0');
    if (mEl) mEl.textContent = String(m).padStart(2, '0');
    if (sEl) sEl.textContent = String(s).padStart(2, '0');
  }
  updateTimer();
  setInterval(updateTimer, 1000);
}
</script>
@endpush

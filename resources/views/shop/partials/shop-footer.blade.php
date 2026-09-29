@php
  $storeAddress = \App\Models\StoreSetting::getValue('address', 'Tamil Nadu, India');
  $storeName = \App\Models\StoreSetting::getStoreName();
  $showSocialFooter = \App\Models\StoreSetting::getValue('social_show_in_footer', '1') === '1';
  $socialOpenNewTab = \App\Models\StoreSetting::getValue('social_open_new_tab', '1') === '1';
  $socialLinks = \App\Models\StoreSetting::getSocialLinks();
  $socialCustom = json_decode(\App\Models\StoreSetting::getValue('social_custom_links', '[]'), true) ?: [];
@endphp

<footer class="footer">
  <div class="back-to-top" onclick="window.scrollTo({top:0,behavior:'smooth'})">
  Back to top
  </div>

  <div class="footer-main">
  <div class="footer-grid">
  <div class="footer-section">
  <h4>Quick Links</h4>
  <ul>
    <li><a href="{{ url('/') }}">Home</a></li>
    <li><a href="{{ url('/products') }}">Shop</a></li>
    <li><a href="{{ url('/cart') }}">Cart</a></li>
    <li><a href="{{ route('shop.wishlist') }}">Wishlist</a></li>
    @auth('customer')
      <li><a href="{{ route('shop.orders.index') }}">Orders</a></li>
      <li><a href="{{ route('shop.account') }}">Account</a></li>
    @else
      <li><a href="{{ route('shop.login.email') }}">Orders</a></li>
      <li><a href="{{ route('shop.login.email') }}">Account</a></li>
    @endauth
    <li><a href="{{ route('shop.help') }}">Help &amp; Support</a></li>
    @auth('customer')
      <li>
        <a href="{{ route('shop.logout') }}" onclick="event.preventDefault(); document.getElementById('footer-logout-form').submit();" style="color:var(--amazon-orange-dark); font-weight:600;">Logout</a>
        <form id="footer-logout-form" action="{{ route('shop.logout') }}" method="POST" style="display:none;">@csrf</form>
      </li>
    @else
      <li><a href="{{ route('shop.login.email') }}" style="color:var(--amazon-orange-dark); font-weight:600;">Login</a></li>
    @endauth
  </ul>
  </div>
  @if($showSocialFooter)
  <div class="footer-section">
  <h4>Connect with Us</h4>
  <ul>
    @php $renderedAny = false; @endphp
    @foreach($socialLinks as $sPlatform => $sItem)
      @if(!empty($sItem['url']) && (!isset($sItem['active']) || $sItem['active']))
        @php $renderedAny = true; @endphp
        <li>
          <a href="{{ $sItem['url'] }}" @if($socialOpenNewTab) target="_blank" rel="noopener noreferrer" @endif>
            @if(!empty($sItem['icon']))<i class="{{ $sItem['icon'] }}" style="width:16px; margin-right:6px;"></i>@endif{{ $sItem['name'] ?? ucfirst($sPlatform) }}
          </a>
        </li>
      @endif
    @endforeach
    @foreach($socialCustom as $cItem)
      @if(!empty($cItem['url']) && (!isset($cItem['active']) || $cItem['active']))
        @php $renderedAny = true; @endphp
        <li>
          <a href="{{ $cItem['url'] }}" @if($socialOpenNewTab) target="_blank" rel="noopener noreferrer" @endif>
            <i class="{{ $cItem['icon'] ?? 'fas fa-link' }}" style="width:16px; margin-right:6px;"></i>{{ $cItem['name'] }}
          </a>
        </li>
      @endif
    @endforeach
    @if(!$renderedAny)
      <li><a href="#"><i class="fab fa-facebook-f" style="width:16px; margin-right:6px;"></i>Facebook</a></li>
      <li><a href="#"><i class="fab fa-x-twitter" style="width:16px; margin-right:6px;"></i>Twitter</a></li>
      <li><a href="#"><i class="fab fa-instagram" style="width:16px; margin-right:6px;"></i>Instagram</a></li>
      <li><a href="#"><i class="fab fa-youtube" style="width:16px; margin-right:6px;"></i>YouTube</a></li>
    @endif
  </ul>
  </div>
  @endif
  <div class="footer-section" style="grid-column: span 2;">
  <h4>Store Location</h4>
  @if(!empty($storeAddress))
  <p style="color: #ddd; font-size: 12.5px; line-height: 1.5; margin-bottom: 10px;">
    <i class="fas fa-map-marker-alt" style="color: var(--amazon-orange); margin-right: 6px;"></i>
    {{ $storeAddress }}
  </p>
  @endif
  @if(\App\Models\StoreSetting::isMapEnabled())
  <div style="border-radius: 4px; overflow: hidden; border: 1px solid #3a4553; height: 130px; margin-top: 10px;">
    <iframe 
      width="100%" 
      height="130" 
      style="border:0;" 
      loading="lazy" 
      allowfullscreen 
      src="{{ \App\Models\StoreSetting::getMapEmbedUrl() }}">
    </iframe>
  </div>
  @endif
  </div>
  </div>

  <div class="footer-divider">
  <div class="footer-logo">{{ $storeName }}</div>
  <div class="footer-lang">
  <select>
  <option>English</option>
  <option>Tamil</option>
  <option>हिन्दी</option>
  </select>
  </div>
  </div>
  </div>

  <div class="footer-bottom">
  <div class="footer-bottom-content">
  <div class="footer-links">
  <a href="#">Conditions of Use</a><span>&nbsp;|&nbsp;</span>
  <a href="#">Privacy Notice</a><span>&nbsp;|&nbsp;</span>
  <a href="#">Interest-Based Ads</a>
  </div>
  <div class="footer-copyright">
  © {{ date('Y') }} {{ $storeName }}, Inc. or its affiliates
  <span>Made with care in India</span>
  </div>
  </div>
  </div>
</footer>

@include('shop.partials.whatsapp-float', ['storeName' => $storeName])

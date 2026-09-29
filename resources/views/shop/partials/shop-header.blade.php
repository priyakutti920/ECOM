<header class="header">
 <div class="header-top">
 <div class="header-top-content">
 <a href="#">
 <i class="fas fa-map-marker-alt location-icon"></i>
 <span style="color: #ccc; font-size: 11px;">Deliver to</span>
 @auth('customer')
  @php
   $cust = auth('customer')->user();
   $defaultAddr = \App\Models\CustomerAddress::forCustomer($cust->id)->where('is_default', 1)->first()
                  ?? \App\Models\CustomerAddress::forCustomer($cust->id)->first();
  @endphp
  <span style="font-weight: 600;">{{ $defaultAddr ? ($defaultAddr->city . ' ' . $defaultAddr->pincode) : 'India' }}</span>
 @else
  <span style="font-weight: 600;">India</span>
 @endauth
 </a>
 <div class="header-top-right">
 @auth('customer')
 <span style="color: #ccc; font-size: 11px;">Hello,</span>
 @php $customer = auth('customer')->user(); @endphp
 <a href="{{ route('shop.account') }}" style="font-weight: 600;">{{ $customer->name ?? $customer->email }}</a>
 <a href="{{ route('shop.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" style="font-size:11px; color:#ccc;">Logout</a>
 <form id="logout-form" action="{{ route('shop.logout') }}" method="POST" style="display:none;">@csrf</form>
 @else
 <a href="{{ route('shop.login.email') }}">Hello, sign in</a>
 @endauth
 <a href="#">Returns &amp; Orders</a>
 <a href="{{ route('shop.help') }}">Help</a>
 </div>
 </div>
 </div>

 <div class="header-main">
 <!-- 1. Three-dot / hamburger menu (always visible) -->
 <button class="mobile-menu-btn" onclick="toggleMobileMenu()" aria-label="Menu">
 <i class="fas fa-bars"></i>
 </button>

 <!-- 2. Logo -->
 <a href="{{ url('/') }}" class="logo">
 @if($storeLogo = \App\Models\StoreSetting::getLogoUrl())
  <img src="{{ $storeLogo }}" alt="{{ $storeName }}" style="max-height: 36px; max-width: 140px; object-fit: contain; vertical-align: middle;">
 @else
  <span class="logo-text">{{ $storeName }}<span class="in"></span></span>
 @endif
 </a>

 <!-- 3. Search bar (grows to fill space) -->
 <form class="search-bar" action="{{ url('/search') }}" method="GET">
 <input type="text" name="q" placeholder="Search {{ $storeName ?? 'Store' }}" value="{{ request('q') }}" style="border-radius: 4px 0 0 4px;">
 <button type="submit"><i class="fas fa-search"></i></button>
 </form>

 <!-- 4. Account icon -->
 <a href="{{ route('shop.account') }}" class="account-link" title="My Account" aria-label="My Account">
 <i class="fas fa-user account-icon"></i>
 </a>

 <!-- 5. Cart (always visible on right) -->
 <a href="javascript:void(0)" class="cart-link" onclick="openCartSidebar(); return false;" title="Cart">
 <i class="fas fa-shopping-cart cart-icon"></i>
 <span class="cart-count" id="cart-count" style="display:none">0</span>
 </a>
 </div>
</header>

<!-- Mobile Menu (slide-in drawer) -->
<div class="mobile-menu-overlay" onclick="toggleMobileMenu()"></div>
<div class="mobile-menu">
  <div style="background: var(--amazon-charcoal); color:#fff; padding:16px 20px; display:flex; justify-content:space-between; align-items:center;">
    <span style="font-weight: 700; font-size: 16px; letter-spacing: 0.5px; text-transform: uppercase;">MENU</span>
    <button onclick="toggleMobileMenu()" style="background:none; border:none; color:#fff; font-size:22px; cursor:pointer;"><i class="fas fa-times"></i></button>
  </div>
  <div style="padding: 16px;">
      <!-- Home -->
      <a href="{{ url('/') }}" style="display:flex; align-items:center; gap:12px; padding:14px 10px; border-bottom: 1px solid #eee; color:#333; font-size:14px; font-weight: 500;">
          <i class="fas fa-home" style="width:20px; color: var(--medium-gray); font-size: 16px;"></i> Home
      </a>
      
      <!-- Shop -->
      <a href="{{ url('/products') }}" style="display:flex; align-items:center; gap:12px; padding:14px 10px; border-bottom: 1px solid #eee; color:#333; font-size:14px; font-weight: 500;">
          <i class="fas fa-shopping-bag" style="width:20px; color: var(--medium-gray); font-size: 16px;"></i> Shop
      </a>
      
      <!-- Cart -->
      <a href="{{ url('/cart') }}" style="display:flex; align-items:center; gap:12px; padding:14px 10px; border-bottom: 1px solid #eee; color:#333; font-size:14px; font-weight: 500;">
          <i class="fas fa-shopping-cart" style="width:20px; color: var(--medium-gray); font-size: 16px;"></i> Cart
      </a>
      
      <!-- Wishlist -->
      <a href="{{ route('shop.wishlist') }}" onclick="toggleMobileMenu();" style="display:flex; align-items:center; gap:12px; padding:14px 10px; border-bottom: 1px solid #eee; color:#333; font-size:14px; font-weight: 500;">
          <i class="fas fa-heart" style="width:20px; color: var(--medium-gray); font-size: 16px;"></i> Wishlist
      </a>
      
      <!-- Orders -->
      @auth('customer')
          <a href="{{ route('shop.orders.index') }}" style="display:flex; align-items:center; gap:12px; padding:14px 10px; border-bottom: 1px solid #eee; color:#333; font-size:14px; font-weight: 500;">
              <i class="fas fa-list-alt" style="width:20px; color: var(--medium-gray); font-size: 16px;"></i> Orders
          </a>
      @else
          <a href="{{ route('shop.login.email') }}" style="display:flex; align-items:center; gap:12px; padding:14px 10px; border-bottom: 1px solid #eee; color:#333; font-size:14px; font-weight: 500;">
              <i class="fas fa-list-alt" style="width:20px; color: var(--medium-gray); font-size: 16px;"></i> Orders
          </a>
      @endauth

      <!-- Help & Support -->
      <a href="{{ route('shop.help') }}" onclick="toggleMobileMenu();" style="display:flex; align-items:center; gap:12px; padding:14px 10px; border-bottom: 1px solid #eee; color:#333; font-size:14px; font-weight: 500;">
          <i class="fas fa-question-circle" style="width:20px; color: var(--medium-gray); font-size: 16px;"></i> Help &amp; Support
      </a>

      <!-- Account -->
      @auth('customer')
          <a href="{{ route('shop.account') }}" style="display:flex; align-items:center; gap:12px; padding:14px 10px; border-bottom: 1px solid #eee; color:#333; font-size:14px; font-weight: 500;">
              <i class="fas fa-user-circle" style="width:20px; color: var(--medium-gray); font-size: 16px;"></i> Account
          </a>
      @else
          <a href="{{ route('shop.login.email') }}" style="display:flex; align-items:center; gap:12px; padding:14px 10px; border-bottom: 1px solid #eee; color:#333; font-size:14px; font-weight: 500;">
              <i class="fas fa-user-circle" style="width:20px; color: var(--medium-gray); font-size: 16px;"></i> Account
          </a>
      @endauth
      
      <!-- Logout / Login -->
      @auth('customer')
          <a href="{{ route('shop.logout') }}" onclick="event.preventDefault(); document.getElementById('mobile-logout-form').submit();" style="display:flex; align-items:center; gap:12px; padding:14px 10px; color:#c7511f; font-size:14px; font-weight: 600;">
              <i class="fas fa-sign-out-alt" style="width:20px; color: #c7511f; font-size: 16px;"></i> Logout
          </a>
          <form id="mobile-logout-form" action="{{ route('shop.logout') }}" method="POST" style="display:none;">@csrf</form>
      @else
          <a href="{{ route('shop.login.email') }}" style="display:flex; align-items:center; gap:12px; padding:14px 10px; color:#007185; font-size:14px; font-weight: 600;">
              <i class="fas fa-sign-in-alt" style="width:20px; color: #007185; font-size: 16px;"></i> Login
          </a>
      @endauth
  </div>
</div>

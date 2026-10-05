@php
  $storeName = \App\Models\StoreSetting::getStoreName();
  $storeLogo = \App\Models\StoreSetting::getLogoUrl();
  $storePhone = \App\Models\StoreSetting::getValue('phone');
  $storeEmail = \App\Models\StoreSetting::getValue('email');
  $freeShippingMin = \App\Models\StoreSetting::getValue('free_shipping_min_amount');
  $categories = \Illuminate\Support\Facades\Cache::remember('shop_header_categories_v2', 1800, function () {
      return \App\Models\Category::active()->orderBy('sort_order')->take(12)->get();
  });
@endphp

<!-- 1. Main Header (Logo, Category Search, Actions) -->
<header class="header-wrap">
  <div class="container">
    <div class="header-wrap-inner">
      <!-- Left: Mobile Menu Toggle + Store Logo -->
      <div class="header-left">
        <button type="button" class="sidebar-menu-icon" onclick="toggleSidebarMenu()" aria-label="Toggle Menu">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 50 50">
            <path d="M 3 9 A 1.0001 1.0001 0 1 0 3 11 L 47 11 A 1.0001 1.0001 0 1 0 47 9 L 3 9 z M 3 24 A 1.0001 1.0001 0 1 0 3 26 L 47 26 A 1.0001 1.0001 0 1 0 47 24 L 3 24 z M 3 39 A 1.0001 1.0001 0 1 0 3 41 L 47 41 A 1.0001 1.0001 0 1 0 47 39 L 3 39 z" fill="currentColor"></path>
          </svg>
        </button>

        <a href="{{ url('/') }}" class="header-logo" aria-label="{{ $storeName }}">
          @if($storeLogo)
            <img src="{{ $storeLogo }}" alt="{{ $storeName }}" style="max-height: 42px; width: auto; object-fit: contain;">
          @else
            <span class="header-logo-text">{{ $storeName }}<span>.</span></span>
          @endif
        </a>
      </div>

      <!-- Center: Search with Category Dropdown from DB & Live Search -->
      <div class="header-search-container" id="desktopSearchContainer">
        <form class="header-search" action="{{ url('/search') }}" method="GET" role="search" id="desktopSearchForm">
          @if($categories->count() > 0)
            <select name="category" class="header-search-cat-select" id="desktopSearchCat" aria-label="Select Category">
              <option value="">All Categories</option>
              @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
              @endforeach
            </select>
          @endif
          <div class="header-search-input-wrap">
            <input type="text" name="q" class="header-search-input live-search-input" id="desktopSearchInput" placeholder="Search for products, brands and more…" value="{{ request('q') }}" autocomplete="off" spellcheck="false" aria-label="Search products" aria-expanded="false" aria-haspopup="listbox" aria-autocomplete="list">
            <button type="button" class="live-search-clear-btn" id="desktopSearchClear" aria-label="Clear search" style="display: none;">
              <i class="las la-times"></i>
            </button>
            <div class="live-search-spinner" id="desktopSearchSpinner" aria-hidden="true" style="display: none;">
              <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round" class="live-search-spinner-svg">
                <circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle>
                <path d="M12 2 a 10 10 0 0 1 10 10"></path>
              </svg>
            </div>
          </div>
          <button type="submit" class="header-search-btn" aria-label="Search">
            <i class="las la-search" style="font-size: 18px;"></i>
          </button>
        </form>

        <!-- Live Search Dropdown Panel -->
        <div class="live-search-dropdown" id="desktopSearchDropdown" role="listbox" aria-label="Search Results"></div>
      </div>

      <!-- Right: Compare, Wishlist, Cart -->
      <div class="header-actions">
        <!-- Compare Action -->
        <a href="{{ url('/products') }}" class="header-action-item" title="Compare" aria-label="Compare Products">
          <div class="header-action-icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
              <path d="M3.58 5.16H17.42C19.08 5.16 20.42 6.5 20.42 8.16V11.48" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M6.74 2L3.58 5.16L6.74 8.32" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M20.42 18.84H6.58C4.92 18.84 3.58 17.5 3.58 15.84V12.52" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M17.26 22L20.42 18.84L17.26 15.68" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </div>
        </a>

        <!-- Wishlist Action -->
        <a href="{{ route('shop.wishlist') }}" class="header-action-item" title="Wishlist" aria-label="My Wishlist">
          <div class="header-action-icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
              <path d="M12.62 20.81C12.28 20.93 11.72 20.93 11.38 20.81C8.48 19.82 2 15.69 2 8.69C2 5.6 4.49 3.1 7.56 3.1C9.38 3.1 10.99 3.98 12 5.34C13.01 3.98 14.63 3.1 16.44 3.1C19.51 3.1 22 5.6 22 8.69C22 15.69 15.52 19.82 12.62 20.81Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="header-action-badge wishlist-count-badge" style="display:none">0</span>
          </div>
        </a>

        <!-- User Account Action -->
        @auth('customer')
          @php $customer = auth('customer')->user(); @endphp
          <div class="header-action-item header-user-menu" style="position:relative; cursor:pointer;" onclick="this.classList.toggle('open');" title="{{ $customer->name ?? 'Account' }}">
            <div class="header-action-icon-wrap" style="width:34px; height:34px; display:flex; align-items:center; justify-content:center;">
              <img src="{{ $customer->avatar_url }}" alt="{{ $customer->name }}" style="width:30px; height:30px; border-radius:50%; object-fit:cover; border:2px solid var(--color-primary); box-shadow:0 2px 6px rgba(0,104,225,0.2);">
            </div>
            <div class="header-user-dropdown" style="display:none; position:absolute; right:0; top:calc(100% + 8px); background:#fff; border:1px solid var(--color-border); border-radius:var(--radius-sm); box-shadow:0 10px 25px rgba(0,0,0,0.08); padding:8px 0; min-width:180px; z-index:1000;">
              <div style="padding:8px 16px; border-bottom:1px solid var(--color-border); margin-bottom:4px; display:flex; align-items:center; gap:10px;">
                <img src="{{ $customer->avatar_url }}" alt="{{ $customer->name }}" style="width:34px; height:34px; border-radius:50%; object-fit:cover; border:1.5px solid var(--color-primary);">
                <div style="overflow:hidden;">
                  <div style="font-size:13px; font-weight:700; color:var(--color-heading); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $customer->name ?: 'Customer' }}</div>
                  <div style="font-size:11px; color:var(--color-muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $customer->email }}</div>
                </div>
              </div>
              <a href="{{ route('shop.account') }}" style="display:flex; align-items:center; gap:8px; padding:8px 16px; font-size:13px; color:var(--color-heading); font-weight:500; text-decoration:none;"><i class="las la-user"></i> Profile</a>
              <a href="{{ route('shop.orders.index') }}" style="display:flex; align-items:center; gap:8px; padding:8px 16px; font-size:13px; color:var(--color-heading); font-weight:500; text-decoration:none;"><i class="las la-box"></i> My Orders</a>
              <a href="{{ route('shop.wishlist') }}" style="display:flex; align-items:center; gap:8px; padding:8px 16px; font-size:13px; color:var(--color-heading); font-weight:500; text-decoration:none;"><i class="las la-heart"></i> My Wishlist</a>
              <a href="{{ route('shop.logout') }}" onclick="event.preventDefault(); document.getElementById('account-logout-form').submit();" style="display:flex; align-items:center; gap:8px; padding:8px 16px; font-size:13px; color:#ef4444; font-weight:500; text-decoration:none;"><i class="las la-sign-out-alt"></i> Logout</a>
              <form id="account-logout-form" action="{{ route('shop.logout') }}" method="POST" style="display:none;">@csrf</form>
            </div>
          </div>
        @else
          <a href="{{ route('shop.login.email') }}" class="header-action-item" title="Login / Register" aria-label="Login">
            <div class="header-action-icon-wrap">
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" width="22" height="22">
                <path d="M12 12C14.7614 12 17 9.76142 17 7C17 4.23858 14.7614 2 12 2C9.23858 2 7 4.23858 7 7C7 9.76142 9.23858 12 12 12Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M20.59 22C20.59 18.13 16.74 15 12 15C7.26 15 3.41 18.13 3.41 22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </div>
          </a>
        @endauth

        <!-- Cart Action with Subtotal Live Display -->
        <div class="header-action-item header-cart" onclick="openCartSidebar();" role="button" tabindex="0" title="Shopping Cart" aria-label="Shopping Cart">
          <div class="header-action-icon-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
              <path d="M7.5 7.67V6.7C7.5 4.45 9.31 2.24 11.56 2.03C14.24 1.77 16.5 3.88 16.5 6.51V7.89" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M9 22H15C19.02 22 19.74 20.39 19.95 18.43L20.7 12.43C20.97 9.99 20.27 8 16 8H8C3.73 8 3.03 9.99 3.3 12.43L4.05 18.43C4.26 20.39 4.98 22 9 22Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span class="header-action-badge cart-count-badge" style="display:none">0</span>
          </div>
          <div class="header-cart-info d-none d-sm-flex">
            <span class="header-cart-label">My Cart</span>
            <span class="header-cart-total">₹0.00</span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Mobile Search Bar -->
  <div class="container d-block d-lg-none" style="padding-bottom: 12px;">
    <div class="mobile-search-container" id="mobileSearchContainer">
      <form class="mobile-search-form" action="{{ url('/search') }}" method="GET" id="mobileSearchForm" style="display:flex; border:1.5px solid var(--color-primary); border-radius:var(--radius-sm); overflow:hidden; background:#fff; height:42px;">
        <div class="header-search-input-wrap mobile-search-input-wrap" style="flex:1; position:relative; display:flex; align-items:center;">
          <input type="text" name="q" class="live-search-input" id="mobileSearchInput" value="{{ request('q') }}" placeholder="Search products, brands and more…" autocomplete="off" spellcheck="false" aria-label="Search products" aria-expanded="false" aria-haspopup="listbox" aria-autocomplete="list" style="flex:1; border:none; padding:0 36px 0 12px; outline:none; font-size:13.5px; font-family:var(--font-base); width:100%; height:100%;">
          <button type="button" class="live-search-clear-btn" id="mobileSearchClear" aria-label="Clear search" style="display: none;">
            <i class="las la-times"></i>
          </button>
          <div class="live-search-spinner" id="mobileSearchSpinner" aria-hidden="true" style="display: none;">
            <svg viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round" stroke-linejoin="round" class="live-search-spinner-svg">
              <circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle>
              <path d="M12 2 a 10 10 0 0 1 10 10"></path>
            </svg>
          </div>
        </div>
        <button type="submit" style="background:var(--color-primary); color:#fff; border:none; padding:0 18px; cursor:pointer; display:flex; align-items:center; justify-content:center;" aria-label="Search"><i class="las la-search" style="font-size: 16px;"></i></button>
      </form>
      <div class="live-search-dropdown mobile-live-search-dropdown" id="mobileSearchDropdown" role="listbox" aria-label="Search Results"></div>
    </div>
  </div>
</header>

<!-- 4. Mobile Bottom Navigation Bar -->
<section class="bottom-navigation-wrap d-lg-none">
  <div class="container">
    <ul class="bottom-navigation-items">
      <li>
        <a href="{{ url('/') }}" class="{{ request()->is('/') ? 'active' : '' }}">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
            <path d="M20.83 8.01L14.28 2.77C13 1.75 11 1.74 9.73 2.76L3.18 8.01C2.24 8.76 1.67 10.26 1.87 11.44L3.13 18.98C3.42 20.67 4.99 22 6.7 22H17.3C18.99 22 20.59 20.64 20.88 18.97L22.14 11.43C22.32 10.26 21.75 8.76 20.83 8.01ZM12.75 18C12.75 18.41 12.41 18.75 12 18.75C11.59 18.75 11.25 18.41 11.25 18V15C11.25 14.59 11.59 14.25 12 14.25C12.41 14.25 12.75 14.59 12.75 15V18Z" fill="currentColor"/>
          </svg>
          <span>Home</span>
        </a>
      </li>

      <li>
        <a href="{{ url('/products') }}" class="{{ request()->is('products') ? 'active' : '' }}">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none">
            <path d="M3.58 5.16H17.42C19.08 5.16 20.42 6.5 20.42 8.16V11.48" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M6.74 2L3.58 5.16L6.74 8.32" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M20.42 18.84H6.58C4.92 18.84 3.58 17.5 3.58 15.84V12.52" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M17.26 22L20.42 18.84L17.26 15.68" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span>Compare</span>
        </a>
      </li>

      <li>
        <a href="javascript:void(0)" onclick="toggleSidebarMenu();">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 29 28" fill="none">
            <path d="M20.33 11.67H22.67C25 11.67 26.17 10.5 26.17 8.17V5.83C26.17 3.5 25 2.33 22.67 2.33H20.33C18 2.33 16.83 3.5 16.83 5.83V8.17C16.83 10.5 18 11.67 20.33 11.67Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M6.33 25.67H8.67C11 25.67 12.17 24.5 12.17 22.17V19.83C12.17 17.5 11 16.33 8.67 16.33H6.33C4 16.33 2.83 17.5 2.83 19.83V22.17C2.83 24.5 4 25.67 6.33 25.67Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M7.5 11.67C10.08 11.67 12.17 9.58 12.17 7C12.17 4.42 10.08 2.33 7.5 2.33C4.92 2.33 2.83 4.42 2.83 7C2.83 9.58 4.92 11.67 7.5 11.67Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M21.5 25.67C24.08 25.67 26.17 23.58 26.17 21C26.17 18.42 24.08 16.33 21.5 16.33C18.92 16.33 16.83 18.42 16.83 21C16.83 23.58 18.92 25.67 21.5 25.67Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span>Categories</span>
        </a>
      </li>

      <li>
        <a href="javascript:void(0)" onclick="openCartSidebar();" class="bottom-navigation-cart">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 29 28" fill="none">
            <path d="M9.25 8.95V7.82C9.25 5.19 11.36 2.61 13.99 2.37C17.11 2.07 19.75 4.53 19.75 7.6V9.21" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M11 25.67H18C22.69 25.67 23.53 23.79 23.78 21.5L24.65 14.5C24.97 11.66 24.15 9.33 19.17 9.33H9.83C4.85 9.33 4.04 11.66 4.35 14.5L5.23 21.5C5.47 23.79 6.31 25.67 11 25.67Z" stroke="currentColor" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span>Cart</span>
          <span class="count cart-count-badge" style="display:none">0</span>
        </a>
      </li>

      <li>
        @auth('customer')
          @php $mbCustomer = auth('customer')->user(); @endphp
          <a href="{{ route('shop.account') }}" class="{{ request()->is('account*') ? 'active' : '' }}" style="display:flex; flex-direction:column; align-items:center;">
            <img src="{{ $mbCustomer->avatar_url }}" alt="{{ $mbCustomer->name }}" style="width:22px; height:22px; border-radius:50%; object-fit:cover; border:1.5px solid var(--color-primary); margin-bottom:2px;">
            <span>Account</span>
          </a>
        @else
          <a href="{{ route('shop.login.email') }}" class="{{ request()->is('login*') ? 'active' : '' }}">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 29 28" fill="none">
              <path d="M14.5 14C17.72 14 20.33 11.39 20.33 8.17C20.33 4.95 17.72 2.33 14.5 2.33C11.28 2.33 8.67 4.95 8.67 8.17C8.67 11.39 11.28 14 14.5 14Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
              <path d="M24.52 25.67C24.52 21.15 20.03 17.5 14.5 17.5C8.97 17.5 4.48 21.15 4.48 25.67" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <span>Account</span>
          </a>
        @endauth
      </li>
    </ul>
  </div>
</section>

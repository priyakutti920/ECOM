@php
  $storeAddress = \App\Models\StoreSetting::getValue('address');
  $storeName = \App\Models\StoreSetting::getStoreName();
  $storePhone = \App\Models\StoreSetting::getValue('phone');
  $storeEmail = \App\Models\StoreSetting::getValue('email');
  $instagramUrl = \App\Models\StoreSetting::getValue('instagram_url') ?: \App\Models\StoreSetting::getValue('social_instagram');
  $waNumber = \App\Models\StoreSetting::getValue('whatsapp_number') ?: \App\Models\StoreSetting::getValue('wa_number');
@endphp

<footer class="footer-wrap">
  <div class="container">
    <div class="footer">
      <div class="footer-top">
        <div class="footer-grid">
          <!-- Col 1: Contact Us (Fully Dynamic from DB) -->
          <div class="footer-col contact-col">
            <h4 class="title">Contact Us</h4>
            <ul class="contact-info">
              @if(!empty($storePhone))
                <li>
                  <i class="las la-phone"></i>
                  <a href="tel:{{ preg_replace('/[^0-9+]/', '', $storePhone) }}" class="store-phone">{{ $storePhone }}</a>
                </li>
              @endif
              @if(!empty($storeEmail))
                <li>
                  <i class="las la-envelope"></i>
                  <a href="mailto:{{ $storeEmail }}" class="store-email">{{ $storeEmail }}</a>
                </li>
              @endif
              @if(!empty($storeAddress))
                <li>
                  <i class="las la-map-marker"></i>
                  <span>{{ $storeAddress }}</span>
                </li>
              @endif
            </ul>

            @if(!empty($instagramUrl) || !empty($waNumber))
              <div style="margin-top: 16px;">
                <ul style="display:flex; gap:12px; list-style:none; padding:0;">
                  @if(!empty($instagramUrl))
                    <li>
                      <a href="{{ $instagramUrl }}" title="Instagram" target="_blank" rel="noopener noreferrer" style="width:36px; height:36px; border-radius:50%; background:#f1f5f9; display:inline-flex; align-items:center; justify-content:center; color:#e1306c; font-size:18px; transition:all 0.2s ease;">
                        <i class="lab la-instagram"></i>
                      </a>
                    </li>
                  @endif
                  @if(!empty($waNumber))
                    <li>
                      <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $waNumber) }}" title="WhatsApp" target="_blank" rel="noopener noreferrer" style="width:36px; height:36px; border-radius:50%; background:#f1f5f9; display:inline-flex; align-items:center; justify-content:center; color:#25D366; font-size:18px; transition:all 0.2s ease;">
                        <i class="lab la-whatsapp"></i>
                      </a>
                    </li>
                  @endif
                </ul>
              </div>
            @endif
          </div>

          <!-- Col 2: My Account -->
          <div class="footer-col">
            <h4 class="title">My Account</h4>
            <ul>
              <li><a href="{{ route('shop.account') }}"><i class="las la-angle-right"></i> Dashboard</a></li>
              <li><a href="{{ route('shop.orders.index') }}"><i class="las la-angle-right"></i> My Orders</a></li>
              <li><a href="{{ route('shop.wishlist') }}"><i class="las la-angle-right"></i> My Wishlist</a></li>
              <li><a href="{{ route('shop.account') }}"><i class="las la-angle-right"></i> My Profile</a></li>
              @auth('customer')
                <li>
                  <a href="{{ route('shop.logout') }}" onclick="event.preventDefault(); document.getElementById('footer-logout-form').submit();" style="color:#ef4444;">
                    <i class="las la-sign-out-alt"></i> Logout
                  </a>
                  <form id="footer-logout-form" action="{{ route('shop.logout') }}" method="POST" style="display:none;">@csrf</form>
                </li>
              @else
                <li><a href="{{ route('shop.login.email') }}"><i class="las la-sign-in-alt"></i> Login / Register</a></li>
              @endauth
            </ul>
          </div>

          <!-- Col 3: Information -->
          <div class="footer-col">
            <h4 class="title">Information</h4>
            <ul>
              <li><a href="{{ url('/') }}"><i class="las la-angle-right"></i> Home</a></li>
              <li><a href="{{ url('/products') }}"><i class="las la-angle-right"></i> All Products</a></li>
              <li><a href="{{ url('/deals') }}"><i class="las la-angle-right"></i> Flash Deals</a></li>
              <li><a href="{{ route('shop.help') }}"><i class="las la-angle-right"></i> Help & Support</a></li>
              <li><a href="{{ route('shop.help') }}"><i class="las la-angle-right"></i> Contact Us</a></li>
            </ul>
          </div>

          <!-- Col 4: Customer Service & Policies -->
          <div class="footer-col">
            <h4 class="title">Customer Service</h4>
            <ul>
              <li><a href="{{ url('/track-order') }}"><i class="las la-angle-right"></i> Track Order</a></li>
              <li><a href="{{ route('shop.help') }}"><i class="las la-angle-right"></i> Shipping & Delivery</a></li>
              <li><a href="{{ route('shop.help') }}"><i class="las la-angle-right"></i> Easy Returns</a></li>
              <li><a href="{{ route('shop.help') }}"><i class="las la-angle-right"></i> Secure Payment</a></li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Footer Bottom Bar -->
      <div class="footer-bottom">
        <div class="footer-bottom-inner">
          <div class="footer-text">
            Copyright © <a href="{{ url('/') }}">{{ $storeName }}</a> {{ date('Y') }}. All rights reserved.
          </div>
          <div class="footer-payment-badges">
            <span style="font-size:12px; color:var(--color-muted); margin-right:6px;">Guaranteed Safe Checkout:</span>
            <i class="lab la-cc-visa" style="font-size:24px; color:#1a1f71;" title="Visa"></i>
            <i class="lab la-cc-mastercard" style="font-size:24px; color:#eb001b;" title="Mastercard"></i>
            <i class="las la-shield-alt" style="font-size:22px; color:#10b981;" title="SSL Secured"></i>
          </div>
        </div>
      </div>
    </div>
  </div>
</footer>

@include('shop.partials.whatsapp-float', ['storeName' => $storeName])

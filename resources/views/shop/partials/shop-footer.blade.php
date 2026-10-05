@php
  $footerSettings = \App\Models\StoreSetting::getFooterSettings();
  $storeName = \App\Models\StoreSetting::getStoreName();

  // Col 1: Contact Us
  $col1Title     = $footerSettings['col1_title'] ?? 'Contact Us';
  $storePhone    = $footerSettings['col1_phone'] ?? \App\Models\StoreSetting::getValue('phone', '+91 80560 81594');
  $storeEmail    = $footerSettings['col1_email'] ?? \App\Models\StoreSetting::getValue('email', 'noolcrop@gmail.com');
  $storeAddress  = $footerSettings['col1_address'] ?? \App\Models\StoreSetting::getValue('address', 'Tirunelveli, Tamil Nadu, India');
  $instagramUrl  = $footerSettings['col1_instagram'] ?? (\App\Models\StoreSetting::getValue('instagram_url') ?: \App\Models\StoreSetting::getValue('social_instagram'));
  $waNumber      = $footerSettings['col1_whatsapp'] ?? (\App\Models\StoreSetting::getValue('whatsapp_number') ?: \App\Models\StoreSetting::getValue('wa_number'));
  $col1ShowSocial= !empty($footerSettings['col1_show_social']);

  // Col 2: My Account
  $col2Title   = $footerSettings['col2_title'] ?? 'My Account';
  $col2Enabled = !empty($footerSettings['col2_enabled']);
  $col2Links   = $footerSettings['col2_links'] ?? [];

  // Col 3: Information
  $col3Title   = $footerSettings['col3_title'] ?? 'Information';
  $col3Enabled = !empty($footerSettings['col3_enabled']);
  $col3Links   = $footerSettings['col3_links'] ?? [];

  // Col 4: Customer Service
  $col4Title   = $footerSettings['col4_title'] ?? 'Customer Service';
  $col4Enabled = !empty($footerSettings['col4_enabled']);
  $col4Links   = $footerSettings['col4_links'] ?? [];

  // Bottom
  $copyrightRaw = $footerSettings['copyright_text'] ?? "Copyright © {store_name} {year}. All rights reserved.";
  $copyright = strip_tags(str_replace(
      ['{store_name}', '{year}', '{date}'],
      [$storeName, date('Y'), date('Y')],
      $copyrightRaw
  ), '<a><span><strong><b><em>');
  $showPaymentBadges = !empty($footerSettings['show_payment_badges']);
@endphp

<footer class="footer-wrap">
  <div class="container">
    <div class="footer">
      <div class="footer-top">
        <div class="footer-grid">
          <!-- Col 1: Contact Us (Fully Dynamic from DB) -->
          <div class="footer-col contact-col">
            <h4 class="title">{{ $col1Title }}</h4>
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

            @if($col1ShowSocial && (!empty($instagramUrl) || !empty($waNumber)))
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

          <!-- Col 2: My Account (Dynamic from DB) -->
          @if($col2Enabled)
            <div class="footer-col">
              <h4 class="title">{{ $col2Title }}</h4>
              <ul>
                @foreach($col2Links as $l)
                  @if(!empty($l['title']) && !empty($l['url']))
                    <li><a href="{{ url($l['url']) }}"><i class="las la-angle-right"></i> {{ $l['title'] }}</a></li>
                  @endif
                @endforeach
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
          @endif

          <!-- Col 3: Information (Dynamic from DB) -->
          @if($col3Enabled)
            <div class="footer-col">
              <h4 class="title">{{ $col3Title }}</h4>
              <ul>
                @foreach($col3Links as $l)
                  @if(!empty($l['title']) && !empty($l['url']))
                    <li><a href="{{ url($l['url']) }}"><i class="las la-angle-right"></i> {{ $l['title'] }}</a></li>
                  @endif
                @endforeach
              </ul>
            </div>
          @endif

          <!-- Col 4: Customer Service & Policies (Dynamic from DB) -->
          @if($col4Enabled)
            <div class="footer-col">
              <h4 class="title">{{ $col4Title }}</h4>
              <ul>
                @foreach($col4Links as $l)
                  @if(!empty($l['title']) && !empty($l['url']))
                    <li><a href="{{ url($l['url']) }}"><i class="las la-angle-right"></i> {{ $l['title'] }}</a></li>
                  @endif
                @endforeach
              </ul>
            </div>
          @endif
        </div>
      </div>

      <!-- Footer Bottom Bar (Dynamic from DB) -->
      <div class="footer-bottom">
        <div class="footer-bottom-inner">
          <div class="footer-text">
            {!! $copyright !!}
          </div>
          @if($showPaymentBadges)
            <div class="footer-payment-badges">
              <i class="lab la-cc-visa" style="font-size:24px; color:#1a1f71;" title="Visa"></i>
              <i class="lab la-cc-mastercard" style="font-size:24px; color:#eb001b;" title="Mastercard"></i>
              <i class="las la-shield-alt" style="font-size:22px; color:#10b981;" title="SSL Secured"></i>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
</footer>

@include('shop.partials.whatsapp-float', ['storeName' => $storeName])

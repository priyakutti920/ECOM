{{--
  Floating WhatsApp button.
  Renders when the admin has activated the WhatsApp Plugin
  in Admin > Settings > Plugins (`wa_enabled` and `wa_number`)
  or configured `whatsapp_number` in Store Settings.
--}}
@php
    $waEnabledSetting = \App\Models\StoreSetting::getValue('wa_enabled');
    // If wa_enabled is set, it must be truthy ('1', true, 'yes', 'on'). If not set at all, fallback to whether a number exists.
    $isEnabled = ($waEnabledSetting === null)
        ? !empty(\App\Models\StoreSetting::getValue('whatsapp_number'))
        : in_array((string)$waEnabledSetting, ['1', 'true', 'on', 'yes'], true);

    $whatsappNumber = \App\Models\StoreSetting::getValue('wa_number')
                   ?: \App\Models\StoreSetting::getValue('whatsapp_number');

    $whatsappMsg    = \App\Models\StoreSetting::getValue('wa_message')
                   ?: \App\Models\StoreSetting::getValue(
                        'whatsapp_default_message',
                        "Hi, I'm browsing " . ($storeName ?? \App\Models\StoreSetting::getStoreName()) . " and need some help."
                   );

    $showFloat = $isEnabled && !empty(trim((string)$whatsappNumber));
@endphp

@if($showFloat)
    @php
        // Strip non-digits for wa.me
        $waDigits = preg_replace('/\D+/', '', (string)$whatsappNumber);
        if (strlen($waDigits) === 10) {
            $waDigits = '91' . $waDigits;
        }
        $waHref = 'https://wa.me/' . $waDigits . '?text=' . urlencode($whatsappMsg);
    @endphp
    <a href="{{ $waHref }}" target="_blank" rel="noopener noreferrer" class="wa-float" id="wa-floating-btn" aria-label="Chat on WhatsApp" title="Chat on WhatsApp">
        <svg viewBox="0 0 32 32" width="28" height="28" aria-hidden="true">
            <path fill="currentColor" d="M19.11 17.27c-.27-.14-1.6-.79-1.85-.88-.25-.09-.43-.14-.61.14-.18.27-.7.88-.86 1.06-.16.18-.32.2-.59.07-.27-.14-1.14-.42-2.18-1.34-.81-.72-1.35-1.61-1.51-1.88-.16-.27-.02-.42.12-.55.12-.12.27-.32.41-.48.14-.16.18-.27.27-.45.09-.18.05-.34-.02-.48-.07-.14-.61-1.47-.84-2.01-.22-.53-.45-.46-.61-.47l-.52-.01c-.18 0-.48.07-.73.34-.25.27-.96.94-.96 2.29 0 1.35.99 2.66 1.13 2.84.14.18 1.95 2.97 4.72 4.16.66.29 1.18.46 1.58.59.66.21 1.26.18 1.74.11.53-.08 1.6-.66 1.83-1.29.23-.63.23-1.18.16-1.29-.07-.11-.25-.18-.52-.32zM16 4C9.38 4 4 9.38 4 16c0 2.29.64 4.41 1.76 6.21L4 28l5.91-1.55A11.93 11.93 0 0 0 16 28c6.62 0 12-5.38 12-12S22.62 4 16 4zm0 21.82c-1.96 0-3.79-.55-5.36-1.5l-.38-.23-3.5.92.93-3.41-.25-.4A9.85 9.85 0 0 1 6.18 16C6.18 10.6 10.6 6.18 16 6.18S25.82 10.6 25.82 16 21.4 25.82 16 25.82z"/>
        </svg>
        <span class="wa-float-label">Chat with Us</span>
    </a>
    <style>
    .wa-float {
        position: fixed !important;
        right: 20px !important;
        bottom: 84px !important;
        z-index: 99999 !important;
        background: #25D366 !important;
        color: #ffffff !important;
        width: 52px;
        height: 52px;
        border-radius: 50% !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        text-decoration: none !important;
        box-shadow: 0 4px 20px rgba(37, 211, 102, 0.5) !important;
        transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.2s ease !important;
        cursor: pointer !important;
    }
    .wa-float:hover {
        transform: scale(1.08) translateY(-2px) !important;
        color: #ffffff !important;
        box-shadow: 0 8px 25px rgba(37, 211, 102, 0.65) !important;
    }
    .wa-float svg {
        display: block;
        fill: currentColor;
    }
    .wa-float-label {
        display: none;
    }
    @media (min-width: 769px) {
        .wa-float {
            width: auto !important;
            height: auto !important;
            padding: 10px 18px !important;
            border-radius: 50px !important;
            gap: 8px !important;
        }
        .wa-float-label {
            display: inline-block !important;
            font-weight: 700 !important;
            font-size: 13px !important;
            letter-spacing: 0.2px !important;
            color: #ffffff !important;
        }
    }
    @media (max-width: 480px) {
        .wa-float {
            right: 14px !important;
            bottom: 74px !important;
            width: 48px !important;
            height: 48px !important;
        }
    }
    </style>
@endif

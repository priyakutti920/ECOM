<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    use \App\Traits\HasCustomAsset;

    protected $table = 'store_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    protected static ?array $memorySettings = null;

    public static function getAllSettings(): array
    {
        if (static::$memorySettings !== null) {
            return static::$memorySettings;
        }

        return static::$memorySettings = \Illuminate\Support\Facades\Cache::rememberForever(
            'store_settings_all',
            fn () => static::pluck('value', 'key')->all()
        );
    }

    public static function getValue(string $key, $default = null)
    {
        $settings = static::getAllSettings();
        $val = $settings[$key] ?? null;

        return ($val !== null && $val !== '') ? $val : $default;
    }

    public static function clearCache(): void
    {
        static::$memorySettings = null;
        \Illuminate\Support\Facades\Cache::forget('store_settings_all');
        \Illuminate\Support\Facades\Cache::forget('homepage_sections_prods');
        \Illuminate\Support\Facades\Cache::forget('home_categories_list');
        \Illuminate\Support\Facades\Cache::forget('home_banners_list');
        \Illuminate\Support\Facades\Cache::forget('home_flash_sale_banner');
        \Illuminate\Support\Facades\Cache::forget('shop_header_categories_v2');
    }

    public static function setValue(string $key, $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
        static::clearCache();
    }

    public static function getStoreName(): string
    {
        $name = static::getValue('store_name');
        if (!empty($name)) {
            return $name;
        }
        return config('app.name', 'Store');
    }

    public static function defaultSocialPlatforms(): array
    {
        return [
            'facebook' => [
                'platform' => 'facebook',
                'name'     => 'Facebook',
                'url'      => '',
                'icon'     => 'fab fa-facebook-f',
                'color'    => '#1877f2',
                'active'   => true,
            ],
            'instagram' => [
                'platform' => 'instagram',
                'name'     => 'Instagram',
                'url'      => '',
                'icon'     => 'fab fa-instagram',
                'color'    => '#e4405f',
                'active'   => true,
            ],
            'twitter' => [
                'platform' => 'twitter',
                'name'     => 'X (Twitter)',
                'url'      => '',
                'icon'     => 'fab fa-x-twitter',
                'color'    => '#111111',
                'active'   => true,
            ],
            'youtube' => [
                'platform' => 'youtube',
                'name'     => 'YouTube',
                'url'      => '',
                'icon'     => 'fab fa-youtube',
                'color'    => '#ff0000',
                'active'   => true,
            ],
            'whatsapp' => [
                'platform' => 'whatsapp',
                'name'     => 'WhatsApp',
                'url'      => '',
                'icon'     => 'fab fa-whatsapp',
                'color'    => '#25d366',
                'active'   => true,
            ],
            'linkedin' => [
                'platform' => 'linkedin',
                'name'     => 'LinkedIn',
                'url'      => '',
                'icon'     => 'fab fa-linkedin-in',
                'color'    => '#0a66c2',
                'active'   => false,
            ],
            'pinterest' => [
                'platform' => 'pinterest',
                'name'     => 'Pinterest',
                'url'      => '',
                'icon'     => 'fab fa-pinterest-p',
                'color'    => '#bd081c',
                'active'   => false,
            ],
            'telegram' => [
                'platform' => 'telegram',
                'name'     => 'Telegram',
                'url'      => '',
                'icon'     => 'fab fa-telegram-plane',
                'color'    => '#229ed9',
                'active'   => false,
            ],
        ];
    }

    public static function getSocialLinks(): array
    {
        $raw = static::getValue('social_links');
        if (!empty($raw)) {
            $decoded = is_array($raw) ? $raw : json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return static::defaultSocialPlatforms();
    }

    public static function isMapEnabled(): bool
    {
        return static::getValue('show_map', '1') === '1';
    }

    public static function getMapEmbedUrl(): string
    {
        $customIframe = trim(static::getValue('map_iframe', ''));
        if (!empty($customIframe)) {
            // If user pasted a full <iframe src="..."> code, extract the src URL
            if (preg_match('/src=[\"\']([^\"\']+)[\"\']/i', $customIframe, $match)) {
                return $match[1];
            }
            if (filter_var($customIframe, FILTER_VALIDATE_URL) || str_starts_with($customIframe, 'http')) {
                return $customIframe;
            }
        }

        // Fall back to query by map_location or address
        $location = trim(static::getValue('map_location', ''));
        if (empty($location)) {
            $location = trim(static::getValue('address', 'Tamil Nadu, India'));
        }

        $zoom = (int) static::getValue('map_zoom', '14');
        if ($zoom < 1 || $zoom > 21) {
            $zoom = 14;
        }

        return 'https://maps.google.com/maps?q=' . urlencode($location) . '&t=&z=' . $zoom . '&ie=UTF8&iwloc=&output=embed';
    }

    public static function getLogoUrl(): ?string
    {
        $logo = static::getValue('logo') ?: static::getValue('store_logo');
        if (!$logo) return null;
        return self::resolveMediaUrl($logo);
    }

    public static function getFaviconUrl(): string
    {
        $favicon = static::getValue('favicon') ?: static::getValue('store_favicon');
        if ($favicon) {
            $url = self::resolveMediaUrl($favicon);
            if ($url) return $url;
        }
        return asset('favicon.ico');
    }

    public static function getPrimaryColor(): string
    {
        $color = static::getValue('primary_color') ?: (static::getValue('theme_primary_color', '#0068e1') ?: '#0068e1');
        return str_starts_with($color, '#') ? $color : ('#' . $color);
    }

    public static function getSecondaryColor(): string
    {
        $color = static::getValue('secondary_color') ?: (static::getValue('theme_secondary_color', '#0f172a') ?: '#0f172a');
        return str_starts_with($color, '#') ? $color : ('#' . $color);
    }

    public static function getPrimaryColorRgb(): string
    {
        return static::hexToRgb(static::getPrimaryColor());
    }

    public static function getSecondaryColorRgb(): string
    {
        return static::hexToRgb(static::getSecondaryColor());
    }

    public static function getPrimaryColorHover(): string
    {
        return static::adjustBrightness(static::getPrimaryColor(), -14);
    }

    public static function getSecondaryColorHover(): string
    {
        return static::adjustBrightness(static::getSecondaryColor(), -14);
    }

    public static function getFontFamily(): string
    {
        $font = static::getValue('theme_font', 'Rubik');
        return match (strtolower(trim($font))) {
            'inter' => "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
            'plus jakarta sans' => "'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
            'outfit' => "'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
            'poppins' => "'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
            default => "'Rubik', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
        };
    }

    public static function getHeaderStyle(): string
    {
        return static::getValue('header_style', 'primary') ?: 'primary';
    }

    public static function hexToRgb(string $hex): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (strlen($hex) !== 6) {
            return '0, 104, 225';
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "{$r}, {$g}, {$b}";
    }

    public static function adjustBrightness(string $hex, int $percent): string
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        if (strlen($hex) !== 6) {
            return '#0055b8';
        }
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $factor = 1 + ($percent / 100);
        $r = max(0, min(255, (int) round($r * $factor)));
        $g = max(0, min(255, (int) round($g * $factor)));
        $b = max(0, min(255, (int) round($b * $factor)));

        return sprintf('#%02x%02x%02x', $r, $g, $b);
    }

    public static function getCurrencySymbol(): string
    {
        return static::getValue('currency_symbol', '₹') ?: '₹';
    }

    public static function getCurrencyCode(): string
    {
        return static::getValue('currency_code', 'INR') ?: 'INR';
    }

    public static function isStoreOpen(): bool
    {
        return static::getValue('store_status', 'open') === 'open';
    }

    public static function getFeatures(): array
    {
        $features = [];
        for ($i = 1; $i <= 4; $i++) {
            $title = static::getValue("feature_{$i}_title");
            if (empty($title)) continue;
            // Skip disabled features
            if (static::getValue("feature_{$i}_enabled", '1') !== '1') continue;
            $features[] = [
                'title' => $title,
                'desc'  => static::getValue("feature_{$i}_desc", ''),
                'icon'  => static::getValue("feature_{$i}_icon", 'las la-check-circle'),
            ];
        }
        return $features;
    }

    public static function defaultHomepageSections(): array
    {
        return [
            'featured' => [
                'key' => 'featured',
                'title' => 'Featured Products',
                'subtitle' => 'Handpicked premium items curated for you',
                'icon' => 'las la-star',
                'icon_color' => 'var(--color-primary)',
                'enabled' => true,
                'limit' => 10,
                'mode' => 'auto', // 'auto' or 'manual'
                'product_ids' => [],
                'view_all_url' => '/products',
                'view_all_text' => 'View All',
                'sort_order' => 1,
            ],
            'deals' => [
                'key' => 'deals',
                'title' => 'Hot Deals',
                'subtitle' => 'Special discounted prices for a limited time',
                'icon' => 'las la-fire',
                'icon_color' => '#ff3366',
                'enabled' => true,
                'limit' => 8,
                'mode' => 'auto',
                'product_ids' => [],
                'view_all_url' => '/deals',
                'view_all_text' => 'See All Deals',
                'sort_order' => 2,
            ],
            'bestsellers' => [
                'key' => 'bestsellers',
                'title' => 'Best Sellers',
                'subtitle' => 'Top selling products loved by our customers',
                'icon' => 'las la-award',
                'icon_color' => 'var(--color-primary)',
                'enabled' => true,
                'limit' => 10,
                'mode' => 'auto',
                'product_ids' => [],
                'view_all_url' => '/products',
                'view_all_text' => 'View All',
                'sort_order' => 3,
            ],
            'latest' => [
                'key' => 'latest',
                'title' => 'New Arrivals',
                'subtitle' => 'Explore fresh arrivals and newest additions',
                'icon' => 'las la-tshirt',
                'icon_color' => 'var(--color-primary)',
                'enabled' => true,
                'limit' => 10,
                'mode' => 'auto',
                'product_ids' => [],
                'view_all_url' => '/products',
                'view_all_text' => 'Discover More',
                'sort_order' => 4,
            ],
        ];
    }

    public static function getHomepageSectionsConfig(): array
    {
        $defaults = static::defaultHomepageSections();
        $storedRaw = static::getValue('home_product_sections');

        if (empty($storedRaw)) {
            return $defaults;
        }

        $decoded = is_array($storedRaw) ? $storedRaw : json_decode($storedRaw, true);
        if (!is_array($decoded)) {
            return $defaults;
        }

        $merged = [];
        foreach ($defaults as $key => $defaultData) {
            $data = isset($decoded[$key]) && is_array($decoded[$key]) ? $decoded[$key] : [];
            $merged[$key] = [
                'key' => $key,
                'title' => !empty($data['title']) ? (string)$data['title'] : $defaultData['title'],
                'subtitle' => isset($data['subtitle']) ? (string)$data['subtitle'] : $defaultData['subtitle'],
                'icon' => !empty($data['icon']) ? (string)$data['icon'] : $defaultData['icon'],
                'icon_color' => !empty($data['icon_color']) ? (string)$data['icon_color'] : $defaultData['icon_color'],
                'enabled' => isset($data['enabled']) ? (bool)$data['enabled'] : $defaultData['enabled'],
                'limit' => !empty($data['limit']) ? (int)$data['limit'] : $defaultData['limit'],
                'mode' => in_array($data['mode'] ?? 'auto', ['auto', 'manual'], true) ? $data['mode'] : 'auto',
                'product_ids' => isset($data['product_ids']) && is_array($data['product_ids']) ? array_map('intval', $data['product_ids']) : [],
                'view_all_url' => isset($data['view_all_url']) ? (string)$data['view_all_url'] : $defaultData['view_all_url'],
                'view_all_text' => isset($data['view_all_text']) ? (string)$data['view_all_text'] : $defaultData['view_all_text'],
                'sort_order' => isset($data['sort_order']) ? (int)$data['sort_order'] : $defaultData['sort_order'],
            ];
        }

        uasort($merged, fn($a, $b) => ($a['sort_order'] ?? 0) <=> ($b['sort_order'] ?? 0));
        return $merged;
    }

    public static function saveHomepageSectionsConfig(array $config): void
    {
        static::setValue('home_product_sections', json_encode($config));
        static::clearCache();
    }

    /**
     * Default configuration schema for the storefront footer
     */
    public static function defaultFooterSettings(): array
    {
        $storeName = static::getStoreName();
        $storePhone = static::getValue('phone', '+91 80560 81594');
        $storeEmail = static::getValue('email', 'noolcrop@gmail.com');
        $storeAddress = static::getValue('address', 'Tirunelveli, Tamil Nadu, India');
        $instagram = static::getValue('instagram_url') ?: static::getValue('social_instagram', 'https://www.instagram.com/noolcrop');
        $whatsapp = static::getValue('whatsapp_number') ?: static::getValue('wa_number', '8056081594');

        return [
            // Column 1: Contact Us
            'col1_title'        => 'Contact Us',
            'col1_phone'        => $storePhone,
            'col1_email'        => $storeEmail,
            'col1_address'      => $storeAddress,
            'col1_whatsapp'     => $whatsapp,
            'col1_instagram'    => $instagram,
            'col1_show_social'  => true,

            // Column 2: My Account
            'col2_title'        => 'My Account',
            'col2_enabled'      => true,
            'col2_links'        => [
                ['title' => 'Dashboard', 'url' => '/account'],
                ['title' => 'My Orders', 'url' => '/account/orders'],
                ['title' => 'My Wishlist', 'url' => '/wishlist'],
                ['title' => 'My Profile', 'url' => '/account'],
            ],

            // Column 3: Information
            'col3_title'        => 'Information',
            'col3_enabled'      => true,
            'col3_links'        => [
                ['title' => 'Home', 'url' => '/'],
                ['title' => 'All Products', 'url' => '/products'],
                ['title' => 'Flash Deals', 'url' => '/deals'],
                ['title' => 'Help & Support', 'url' => '/help'],
                ['title' => 'Contact Us', 'url' => '/help'],
            ],

            // Column 4: Customer Service
            'col4_title'        => 'Customer Service',
            'col4_enabled'      => true,
            'col4_links'        => [
                ['title' => 'Track Order', 'url' => '/track-order'],
                ['title' => 'Shipping & Delivery', 'url' => '/help'],
                ['title' => 'Easy Returns', 'url' => '/help'],
                ['title' => 'Secure Payment', 'url' => '/help'],
            ],

            // Bottom Bar
            'copyright_text'    => "Copyright © {$storeName} " . date('Y') . ". All rights reserved.",
            'safe_checkout_text'=> '',
            'show_payment_badges' => true,
        ];
    }

    /**
     * Get resolved footer settings from database with fallback defaults
     */
    public static function getFooterSettings(): array
    {
        $defaults = static::defaultFooterSettings();
        $stored = static::getValue('footer_settings_json');

        if (empty($stored)) {
            return $defaults;
        }

        $decoded = is_array($stored) ? $stored : json_decode($stored, true);
        if (!is_array($decoded)) {
            return $defaults;
        }

        return array_merge($defaults, $decoded);
    }

    /**
     * Save footer settings JSON and sync primary contact fields
     */
    public static function saveFooterSettings(array $settings): void
    {
        static::setValue('footer_settings_json', json_encode($settings));

        // Sync primary contact fields if provided
        if (isset($settings['col1_phone'])) {
            static::setValue('phone', $settings['col1_phone']);
        }
        if (isset($settings['col1_email'])) {
            static::setValue('email', $settings['col1_email']);
        }
        if (isset($settings['col1_address'])) {
            static::setValue('address', $settings['col1_address']);
        }
        if (isset($settings['col1_whatsapp'])) {
            static::setValue('whatsapp_number', $settings['col1_whatsapp']);
            static::setValue('wa_number', $settings['col1_whatsapp']);
        }
        if (isset($settings['col1_instagram'])) {
            static::setValue('instagram_url', $settings['col1_instagram']);
            static::setValue('social_instagram', $settings['col1_instagram']);
        }

        static::clearCache();
    }
}
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

    public static function getAllSettings(): array
    {
        return \Illuminate\Support\Facades\Cache::rememberForever(
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
        \Illuminate\Support\Facades\Cache::forget('store_settings_all');
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
        return static::getValue('primary_color') ?: (static::getValue('theme_primary_color', '#131921') ?: '#131921');
    }

    public static function getSecondaryColor(): string
    {
        return static::getValue('secondary_color') ?: (static::getValue('theme_secondary_color', '#febd69') ?: '#febd69');
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
}
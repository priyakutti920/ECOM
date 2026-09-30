<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait HasCustomAsset
{
    /**
     * Return a browser-loadable URL for the given relative asset path.
     *
     * Why a custom helper instead of asset()?
     * - On some shared-host / sub-folder setups, the Laravel document root is
     *   NOT the project's /public folder. The request URL contains /public/
     *   but asset() (which uses the script name) does not always add it.
     * - This helper inspects the actual incoming request and builds a URL
     *   that is guaranteed to be loadable by the browser, both for
     *   `images/foo.jpg` and `storage/foo.jpg` style paths.
     */
    public static function getCustomAssetUrl(?string $path): string
    {
        if (empty($path)) return '';

        // Full URL? leave it as-is, but strip our own host prefix.
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            $request = request();
            if ($request) {
                $host = $request->getSchemeAndHttpHost();
                if (str_starts_with($path, $host)) {
                    return substr($path, strlen($host));
                }
            }
            return $path;
        }

        // Normalize: no leading slash, no leading public/
        $path = ltrim($path, '/\\');
        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        }

        $request = request();

        // When the script name contains /public/ (e.g. /project/public/index.php)
        // the browser is reaching the app via the /public/ folder.
        if ($request) {
            $scriptName = $request->getScriptName();
            if (str_contains($scriptName, '/public/')) {
                $host = $request->getSchemeAndHttpHost();
                $projectFolder = substr($scriptName, 0, strpos($scriptName, '/public/'));
                $url = $host . $projectFolder . '/public/' . ltrim($path, '/');
                return $url;
            }
        }

        // Standard setup: asset() is reliable.
        return asset($path);
    }

    /**
     * Resolves any media path (absolute URL, public file, storage disk file,
     * uploads subfolder) into a guaranteed working browser-loadable URL.
     */
    public static function resolveMediaUrl(?string $path, ?string $fallback = null): ?string
    {
        if (empty($path)) {
            return $fallback;
        }

        $trimmed = trim($path);

        // 1. External URLs or Data URIs
        if (str_starts_with($trimmed, 'http://') ||
            str_starts_with($trimmed, 'https://') ||
            str_starts_with($trimmed, '//') ||
            str_starts_with($trimmed, 'data:')) {
            return $trimmed;
        }

        // 2. Strip leading slashes and public prefix
        $clean = ltrim($trimmed, '/\\');
        if (str_starts_with($clean, 'public/')) {
            $clean = substr($clean, 7);
        }

        // 3. If file exists directly in public/ folder (e.g. public/product/..., public/uploads/..., public/images/...)
        if (file_exists(public_path($clean))) {
            return self::getCustomAssetUrl($clean);
        }

        // 4. If path starts with 'storage/'
        if (str_starts_with($clean, 'storage/')) {
            $storageSub = substr($clean, 8);
            if (Storage::disk('public')->exists($storageSub) || file_exists(public_path($clean))) {
                return self::getCustomAssetUrl($clean);
            }
            if (file_exists(public_path($storageSub))) {
                return self::getCustomAssetUrl($storageSub);
            }
            return self::getCustomAssetUrl($clean);
        }

        // 5. Check if file exists in storage disk 'public' (storage/app/public/$clean or public/storage/$clean)
        if (Storage::disk('public')->exists($clean) || file_exists(public_path('storage/' . $clean))) {
            return self::getCustomAssetUrl('storage/' . $clean);
        }

        // 6. Check common subdirectories in public/
        foreach (['uploads/', 'images/', 'assets/', 'media/', 'product/'] as $prefix) {
            if (file_exists(public_path($prefix . $clean))) {
                return self::getCustomAssetUrl($prefix . $clean);
            }
        }

        // 7. If path contains '/' (e.g. 'settings/xyz.png' or 'media/xyz.png'), check if storage-backed
        if (str_contains($clean, '/')) {
            return self::getCustomAssetUrl('storage/' . $clean);
        }

        return self::getCustomAssetUrl($clean);
    }
}

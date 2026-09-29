<?php

namespace App\Traits;

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
        $path = ltrim($path, '/');
        if (str_starts_with($path, 'public/')) {
            $path = substr($path, 7);
        }

        $request = request();

        // When the script name contains /public/ (e.g. /project/public/index.php)
        // the browser is reaching the app via the /public/ folder. asset()
        // does NOT include the project sub-folder + /public/ in that case, so
        // we have to add them ourselves. We pull the project prefix straight
        // out of the script name so this works whether or not Laravel has set
        // the request base path (CLI, custom entry points, etc.).
        if ($request) {
            $scriptName = $request->getScriptName();
            if (str_contains($scriptName, '/public/')) {
                $host = $request->getSchemeAndHttpHost();
                // e.g. /project/public/index.php -> project folder is /project
                $projectFolder = substr($scriptName, 0, strpos($scriptName, '/public/'));
                $url = $host . $projectFolder . '/public/' . ltrim($path, '/');
                return $url;
            }
        }

        // Standard setup: asset() is reliable.
        return asset($path);
    }
}

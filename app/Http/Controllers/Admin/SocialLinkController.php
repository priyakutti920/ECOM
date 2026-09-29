<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Http\Request;

class SocialLinkController extends Controller
{
    /**
     * Render the Social Links settings page.
     */
    public function index()
    {
        return view('admin.settings.social');
    }

    /**
     * Return JSON data for social links configuration.
     */
    public function getData()
    {
        $defaultPlatforms = StoreSetting::defaultSocialPlatforms();
        $savedLinks = StoreSetting::getSocialLinks();

        // Merge saved data with default platforms so all platforms are present
        $links = [];
        foreach ($defaultPlatforms as $key => $default) {
            $saved = $savedLinks[$key] ?? [];
            $links[$key] = [
                'platform' => $key,
                'name'     => $saved['name'] ?? $default['name'],
                'url'      => $saved['url'] ?? '',
                'icon'     => $saved['icon'] ?? $default['icon'],
                'color'    => $saved['color'] ?? $default['color'],
                'active'   => isset($saved['active']) ? (bool) $saved['active'] : (bool) $default['active'],
            ];
        }

        $customLinksRaw = StoreSetting::getValue('social_custom_links', '[]');
        $customLinks = json_decode($customLinksRaw, true);
        if (!is_array($customLinks)) {
            $customLinks = [];
        }

        $openNewTab    = StoreSetting::getValue('social_open_new_tab', '1') === '1';
        $showInFooter  = StoreSetting::getValue('social_show_in_footer', '1') === '1';

        return response()->json([
            'success' => true,
            'data'    => [
                'links'          => $links,
                'custom_links'   => $customLinks,
                'open_new_tab'   => $openNewTab,
                'show_in_footer' => $showInFooter,
            ],
        ]);
    }

    /**
     * Save the social links configuration.
     */
    public function save(Request $request)
    {
        $defaultPlatforms = StoreSetting::defaultSocialPlatforms();
        $submittedLinks = $request->input('links', []);
        $links = [];

        foreach ($defaultPlatforms as $key => $default) {
            $submitted = $submittedLinks[$key] ?? [];
            $url = isset($submitted['url']) ? trim($submitted['url']) : '';
            $active = !empty($submitted['active']);

            $links[$key] = [
                'platform' => $key,
                'name'     => $default['name'],
                'url'      => $url,
                'icon'     => $default['icon'],
                'color'    => $default['color'],
                'active'   => $active,
            ];

            // Also save convenient individual keys: social_facebook, social_instagram, etc.
            StoreSetting::setValue('social_' . $key, $url);
        }

        StoreSetting::setValue('social_links', json_encode($links));

        // Process custom links
        $submittedCustom = $request->input('custom_links', []);
        $customLinks = [];
        if (is_array($submittedCustom)) {
            foreach ($submittedCustom as $item) {
                if (!empty($item['name']) && !empty($item['url'])) {
                    $customLinks[] = [
                        'name'   => trim($item['name']),
                        'url'    => trim($item['url']),
                        'icon'   => !empty($item['icon']) ? trim($item['icon']) : 'fas fa-link',
                        'active' => !empty($item['active']),
                    ];
                }
            }
        }
        StoreSetting::setValue('social_custom_links', json_encode($customLinks));

        // General settings
        StoreSetting::setValue('social_open_new_tab', $request->boolean('open_new_tab') ? '1' : '0');
        StoreSetting::setValue('social_show_in_footer', $request->boolean('show_in_footer') ? '1' : '0');

        return response()->json([
            'success' => true,
            'message' => 'Social links saved successfully.',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Http\Request;

class FooterSettingController extends Controller
{
    /**
     * Show the Footer settings manager page
     */
    public function index()
    {
        $footer = StoreSetting::getFooterSettings();
        return view('admin.appearance.footer', compact('footer'));
    }

    /**
     * Update footer settings in database
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'col1_title'          => 'nullable|string|max:100',
            'col1_phone'          => 'nullable|string|max:50',
            'col1_email'          => 'nullable|email|max:100',
            'col1_address'        => 'nullable|string|max:255',
            'col1_whatsapp'       => 'nullable|string|max:50',
            'col1_instagram'      => 'nullable|string|max:255',
            'col1_show_social'    => 'nullable|boolean',

            'col2_title'          => 'nullable|string|max:100',
            'col2_enabled'        => 'nullable|boolean',
            'col2_link_titles'    => 'nullable|array',
            'col2_link_titles.*'  => 'nullable|string|max:100',
            'col2_link_urls'      => 'nullable|array',
            'col2_link_urls.*'    => 'nullable|string|max:255',

            'col3_title'          => 'nullable|string|max:100',
            'col3_enabled'        => 'nullable|boolean',
            'col3_link_titles'    => 'nullable|array',
            'col3_link_titles.*'  => 'nullable|string|max:100',
            'col3_link_urls'      => 'nullable|array',
            'col3_link_urls.*'    => 'nullable|string|max:255',

            'col4_title'          => 'nullable|string|max:100',
            'col4_enabled'        => 'nullable|boolean',
            'col4_link_titles'    => 'nullable|array',
            'col4_link_titles.*'  => 'nullable|string|max:100',
            'col4_link_urls'      => 'nullable|array',
            'col4_link_urls.*'    => 'nullable|string|max:255',

            'copyright_text'      => 'nullable|string|max:255',
            'safe_checkout_text'  => 'nullable|string|max:100',
            'show_payment_badges' => 'nullable|boolean',
        ]);

        // Helper to compile link rows
        $compileLinks = function ($titles, $urls) {
            $links = [];
            if (is_array($titles) && is_array($urls)) {
                foreach ($titles as $idx => $t) {
                    $u = $urls[$idx] ?? '';
                    if (trim($t) !== '' && trim($u) !== '') {
                        $links[] = [
                            'title' => trim($t),
                            'url'   => trim($u),
                        ];
                    }
                }
            }
            return $links;
        };

        $settings = [
            'col1_title'          => $request->input('col1_title', 'Contact Us'),
            'col1_phone'          => $request->input('col1_phone'),
            'col1_email'          => $request->input('col1_email'),
            'col1_address'        => $request->input('col1_address'),
            'col1_whatsapp'       => $request->input('col1_whatsapp'),
            'col1_instagram'      => $request->input('col1_instagram'),
            'col1_show_social'    => $request->boolean('col1_show_social'),

            'col2_title'          => $request->input('col2_title', 'My Account'),
            'col2_enabled'        => $request->boolean('col2_enabled'),
            'col2_links'          => $compileLinks($request->input('col2_link_titles'), $request->input('col2_link_urls')),

            'col3_title'          => $request->input('col3_title', 'Information'),
            'col3_enabled'        => $request->boolean('col3_enabled'),
            'col3_links'          => $compileLinks($request->input('col3_link_titles'), $request->input('col3_link_urls')),

            'col4_title'          => $request->input('col4_title', 'Customer Service'),
            'col4_enabled'        => $request->boolean('col4_enabled'),
            'col4_links'          => $compileLinks($request->input('col4_link_titles'), $request->input('col4_link_urls')),

            'copyright_text'      => $request->input('copyright_text', 'Copyright © {store_name} {year}. All rights reserved.'),
            'safe_checkout_text'  => $request->input('safe_checkout_text', ''),
            'show_payment_badges' => $request->boolean('show_payment_badges'),
        ];

        StoreSetting::saveFooterSettings($settings);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Footer settings updated successfully in database.',
                'footer'  => StoreSetting::getFooterSettings(),
            ]);
        }

        return redirect()->route('admin.appearance.footer.index')->with('success', 'Footer settings successfully saved.');
    }

    /**
     * Reset footer settings to default configuration
     */
    public function reset(Request $request)
    {
        $defaults = StoreSetting::defaultFooterSettings();
        StoreSetting::saveFooterSettings($defaults);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Footer settings reset to default.',
                'footer'  => $defaults,
            ]);
        }

        return redirect()->route('admin.appearance.footer.index')->with('success', 'Footer settings reset to default values.');
    }
}

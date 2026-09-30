<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\StoreSetting;
use Illuminate\Http\Request;

class NavigationController extends Controller
{
    /**
     * Display the Storefront Navigation Manager
     */
    public function index()
    {
        $allCategories = Category::active()->orderBy('sort_order')->orderBy('name')->get();

        $navSettings = [
            'nav_show_all_categories'  => StoreSetting::getValue('nav_show_all_categories', '1') === '1',
            'nav_all_categories_label' => StoreSetting::getValue('nav_all_categories_label', 'All Categories'),
            'nav_show_home'            => StoreSetting::getValue('nav_show_home', '1') === '1',
            'nav_home_label'           => StoreSetting::getValue('nav_home_label', 'Home'),
            'nav_show_shop'            => StoreSetting::getValue('nav_show_shop', '1') === '1',
            'nav_shop_label'           => StoreSetting::getValue('nav_shop_label', 'Shop'),
            'nav_show_deals'           => StoreSetting::getValue('nav_show_deals', '1') === '1',
            'nav_deals_label'          => StoreSetting::getValue('nav_deals_label', 'Flash Deals'),
            'nav_category_ids'         => json_decode(StoreSetting::getValue('nav_category_ids', '[]'), true) ?: [],
            'nav_promo_enabled'        => StoreSetting::getValue('nav_promo_enabled', '1') === '1',
            'nav_promo_text'           => StoreSetting::getValue('nav_promo_text', 'Free shipping on all orders over ₹' . number_format((float)StoreSetting::getValue('free_shipping_min_amount', 499), 0)),
            'nav_custom_links'         => json_decode(StoreSetting::getValue('nav_custom_links', '[]'), true) ?: [],
        ];

        return view('admin.navigation.index', compact('allCategories', 'navSettings'));
    }

    /**
     * Update the Storefront Navigation Settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'nav_show_all_categories'  => 'nullable|boolean',
            'nav_all_categories_label' => 'nullable|string|max:100',
            'nav_show_home'            => 'nullable|boolean',
            'nav_home_label'           => 'nullable|string|max:100',
            'nav_show_shop'            => 'nullable|boolean',
            'nav_shop_label'           => 'nullable|string|max:100',
            'nav_show_deals'           => 'nullable|boolean',
            'nav_deals_label'          => 'nullable|string|max:100',
            'category_ids'             => 'nullable|array',
            'category_ids.*'           => 'integer|exists:categories,id',
            'nav_promo_enabled'        => 'nullable|boolean',
            'nav_promo_text'           => 'nullable|string|max:255',
            'custom_link_title'        => 'nullable|array',
            'custom_link_title.*'      => 'nullable|string|max:100',
            'custom_link_url'          => 'nullable|array',
            'custom_link_url.*'        => 'nullable|string|max:255',
            'custom_link_target'       => 'nullable|array',
            'custom_link_target.*'     => 'nullable|string|in:_self,_blank',
        ]);

        // Save toggles and labels
        StoreSetting::setValue('nav_show_all_categories', $request->has('nav_show_all_categories') ? '1' : '0');
        StoreSetting::setValue('nav_all_categories_label', trim($request->input('nav_all_categories_label', 'All Categories')) ?: 'All Categories');
        StoreSetting::setValue('nav_show_home', $request->has('nav_show_home') ? '1' : '0');
        StoreSetting::setValue('nav_home_label', trim($request->input('nav_home_label', 'Home')) ?: 'Home');
        StoreSetting::setValue('nav_show_shop', $request->has('nav_show_shop') ? '1' : '0');
        StoreSetting::setValue('nav_shop_label', trim($request->input('nav_shop_label', 'Shop')) ?: 'Shop');
        StoreSetting::setValue('nav_show_deals', $request->has('nav_show_deals') ? '1' : '0');
        StoreSetting::setValue('nav_deals_label', trim($request->input('nav_deals_label', 'Flash Deals')) ?: 'Flash Deals');

        // Save selected category IDs
        $selectedCatIds = array_map('intval', $request->input('category_ids', []));
        StoreSetting::setValue('nav_category_ids', json_encode(array_values(array_unique($selectedCatIds))));

        // Save Promo Notice
        StoreSetting::setValue('nav_promo_enabled', $request->has('nav_promo_enabled') ? '1' : '0');
        StoreSetting::setValue('nav_promo_text', trim($request->input('nav_promo_text', '')));

        // Save Custom Links
        $customLinks = [];
        $titles = $request->input('custom_link_title', []);
        $urls = $request->input('custom_link_url', []);
        $targets = $request->input('custom_link_target', []);

        foreach ($titles as $idx => $title) {
            $t = trim($title ?? '');
            $u = trim($urls[$idx] ?? '');
            $tgt = trim($targets[$idx] ?? '_self');
            if (!empty($t) && !empty($u)) {
                $customLinks[] = [
                    'title'  => $t,
                    'url'    => $u,
                    'target' => $tgt === '_blank' ? '_blank' : '_self',
                ];
            }
        }
        StoreSetting::setValue('nav_custom_links', json_encode($customLinks));

        StoreSetting::clearCache();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Navigation bar settings updated successfully!',
            ]);
        }

        return redirect()->route('admin.navigation.index')
            ->with('success', 'Storefront navigation bar settings updated successfully!');
    }
}

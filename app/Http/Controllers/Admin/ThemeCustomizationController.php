<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ThemeCustomizationController extends Controller
{
    /**
     * Available curated theme presets.
     */
    public static function getPresets(): array
    {
        return [
            [
                'id' => 'modern-indigo',
                'name' => 'Modern Indigo',
                'primary' => '#0068e1',
                'secondary' => '#0f172a',
                'font' => 'Rubik',
                'header' => 'primary',
                'desc' => 'Clean e-commerce blue with deep slate accents',
            ],
            [
                'id' => 'emerald-luxe',
                'name' => 'Emerald Luxe',
                'primary' => '#059669',
                'secondary' => '#064e3b',
                'font' => 'Inter',
                'header' => 'primary',
                'desc' => 'Vibrant botanical green with forest undertones',
            ],
            [
                'id' => 'electric-teal',
                'name' => 'Electric Teal',
                'primary' => '#00b4d8',
                'secondary' => '#03045e',
                'font' => 'Outfit',
                'header' => 'primary',
                'desc' => 'Fresh tropical cyan with midnight navy depth',
            ],
            [
                'id' => 'midnight-amber',
                'name' => 'Midnight Amber',
                'primary' => '#131921',
                'secondary' => '#f59e0b',
                'font' => 'Rubik',
                'header' => 'dark',
                'desc' => 'Amazon-style charcoal header with warm golden accents',
            ],
            [
                'id' => 'crimson-rose',
                'name' => 'Crimson Rose',
                'primary' => '#e11d48',
                'secondary' => '#881337',
                'font' => 'Plus Jakarta Sans',
                'header' => 'primary',
                'desc' => 'Bold ruby fashion aesthetic with burgundy elegance',
            ],
            [
                'id' => 'royal-purple',
                'name' => 'Royal Purple',
                'primary' => '#7c3aed',
                'secondary' => '#2e1065',
                'font' => 'Poppins',
                'header' => 'primary',
                'desc' => 'Rich violet luxury branding with royal purple contrast',
            ],
            [
                'id' => 'sunset-orange',
                'name' => 'Sunset Orange',
                'primary' => '#ea580c',
                'secondary' => '#7c2d12',
                'font' => 'Rubik',
                'header' => 'primary',
                'desc' => 'High-energy citrus orange for deals & streetwear',
            ],
            [
                'id' => 'dark-obsidian',
                'name' => 'Dark Obsidian',
                'primary' => '#18181b',
                'secondary' => '#3b82f6',
                'font' => 'Inter',
                'header' => 'dark',
                'desc' => 'Minimalist monochrome slate with electric blue accents',
            ],
        ];
    }

    /**
     * Display the Theme & Appearance Customization view.
     */
    public function index()
    {
        $current = [
            'primary_color'   => StoreSetting::getPrimaryColor(),
            'secondary_color' => StoreSetting::getSecondaryColor(),
            'theme_font'      => StoreSetting::getValue('theme_font', 'Rubik'),
            'header_style'    => StoreSetting::getHeaderStyle(),
            'currency_symbol' => StoreSetting::getCurrencySymbol(),
        ];

        $presets = self::getPresets();

        return view('admin.appearance.theme', compact('current', 'presets'));
    }

    /**
     * Fetch theme settings as JSON.
     */
    public function getData()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'primary_color'   => StoreSetting::getPrimaryColor(),
                'secondary_color' => StoreSetting::getSecondaryColor(),
                'theme_font'      => StoreSetting::getValue('theme_font', 'Rubik'),
                'header_style'    => StoreSetting::getHeaderStyle(),
                'currency_symbol' => StoreSetting::getCurrencySymbol(),
                'presets'         => self::getPresets(),
            ],
        ]);
    }

    /**
     * Save theme & appearance customization.
     */
    public function update(Request $request)
    {
        $request->validate([
            'primary_color'   => ['required', 'string', 'regex:/^#?([a-f0-9]{3}|[a-f0-9]{6})$/i'],
            'secondary_color' => ['required', 'string', 'regex:/^#?([a-f0-9]{3}|[a-f0-9]{6})$/i'],
            'theme_font'      => ['nullable', 'string', 'in:Rubik,Inter,Plus Jakarta Sans,Outfit,Poppins'],
            'header_style'    => ['nullable', 'string', 'in:light,dark,primary'],
            'currency_symbol' => ['nullable', 'string', 'max:10'],
        ]);

        $primary = $request->primary_color;
        if (!str_starts_with($primary, '#')) {
            $primary = '#' . $primary;
        }

        $secondary = $request->secondary_color;
        if (!str_starts_with($secondary, '#')) {
            $secondary = '#' . $secondary;
        }

        StoreSetting::setValue('primary_color', $primary);
        StoreSetting::setValue('secondary_color', $secondary);

        if ($request->filled('theme_font')) {
            StoreSetting::setValue('theme_font', $request->theme_font);
        }

        if ($request->filled('header_style')) {
            StoreSetting::setValue('header_style', $request->header_style);
        }

        if ($request->filled('currency_symbol')) {
            StoreSetting::setValue('currency_symbol', $request->currency_symbol);
        }

        // Invalidate settings cache so changes take effect immediately
        Cache::forget('store_settings_all');
        Cache::forget('store_primary_color');
        Cache::forget('store_secondary_color');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Theme & Appearance updated successfully!',
                'data' => [
                    'primary_color'   => StoreSetting::getPrimaryColor(),
                    'secondary_color' => StoreSetting::getSecondaryColor(),
                    'theme_font'      => StoreSetting::getValue('theme_font', 'Rubik'),
                    'header_style'    => StoreSetting::getHeaderStyle(),
                ],
            ]);
        }

        return redirect()->route('admin.appearance.theme.index')->with('success', 'Theme & Appearance updated successfully!');
    }

    /**
     * Reset theme to default settings.
     */
    public function reset(Request $request)
    {
        StoreSetting::setValue('primary_color', '#0068e1');
        StoreSetting::setValue('secondary_color', '#0f172a');
        StoreSetting::setValue('theme_font', 'Rubik');
        StoreSetting::setValue('header_style', 'primary');

        Cache::forget('store_settings_all');
        Cache::forget('store_primary_color');
        Cache::forget('store_secondary_color');
        Cache::forget('store_header_style');
        Cache::forget('store_theme_font');

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Theme reset to default successfully!',
                'data' => [
                    'primary_color'   => '#0068e1',
                    'secondary_color' => '#0f172a',
                    'theme_font'      => 'Rubik',
                    'header_style'    => 'primary',
                ],
            ]);
        }

        return redirect()->route('admin.appearance.theme.index')->with('success', 'Theme reset to default successfully!');
    }
}

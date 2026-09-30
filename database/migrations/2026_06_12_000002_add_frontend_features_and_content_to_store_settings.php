<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\StoreSetting;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            'feature_1_title' => 'Free Shipping',
            'feature_1_desc' => 'On orders over ₹499',
            'feature_1_icon' => 'las la-shipping-fast',

            'feature_2_title' => '24/7 Support',
            'feature_2_desc' => 'Dedicated customer assistance',
            'feature_2_icon' => 'las la-headset',

            'feature_3_title' => 'Easy Returns',
            'feature_3_desc' => '7-day hassle-free policy',
            'feature_3_icon' => 'las la-sync-alt',

            'feature_4_title' => '100% Secure Payment',
            'feature_4_desc' => 'UPI, Cards & NetBanking',
            'feature_4_icon' => 'las la-shield-alt',

            'pdp_feature_1' => '100% Secure & Encrypted Checkout',
            'pdp_feature_2' => 'Fast Express Delivery Eligible',
            'pdp_feature_3' => '100% Genuine Quality Guaranteed',

            'nav_promo_text' => 'Free shipping on all orders over ₹499',
        ];

        foreach ($settings as $key => $val) {
            StoreSetting::updateOrCreate(['key' => $key], ['value' => $val]);
        }
        StoreSetting::clearCache();
    }

    public function down(): void
    {
    }
};

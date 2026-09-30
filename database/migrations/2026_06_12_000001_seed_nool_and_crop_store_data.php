<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\StoreSetting;
use App\Models\Category;
use App\Models\Banner;
use App\Models\Product;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Migrate Store Settings
        $settings = [
            'store_name' => 'Nool & Crop',
            'phone' => '+91 80560 81594',
            'email' => 'noolcrop@gmail.com',
            'address' => 'Tirunelveli, Tamil Nadu, India',
            'free_shipping_min_amount' => '499',
            'instagram_url' => 'https://www.instagram.com/noolcrop',
            'whatsapp_number' => '8056081594',
            'wa_number' => '8056081594',
            'wa_enabled' => '1',
            'wa_message' => 'Hi Nool & Crop! I would like to inquire about your products.',
            'primary_color' => '#0068e1',
            'secondary_color' => '#0E1E3E',
            'meta_title' => 'Nool & Crop — Online Shopping Store',
            'meta_description' => 'Discover premium collections across Men\'s T-Shirts, Oversized Tees, and Daily Essentials at Nool & Crop.',
            'logo' => 'https://noolandcrop.in/storage/media/3fV1H1W0NAY6BrdVTkMTfJBs85zRPfzPTVfVZbcz.png',
            'favicon' => 'https://noolandcrop.in/storage/media/3fV1H1W0NAY6BrdVTkMTfJBs85zRPfzPTVfVZbcz.png',
        ];

        foreach ($settings as $key => $val) {
            StoreSetting::updateOrCreate(['key' => $key], ['value' => $val]);
        }
        StoreSetting::clearCache();

        // 2. Migrate Categories
        $categoriesData = [
            [
                'name' => "Men's T-Shirt",
                'slug' => 'mens-t-shirt',
                'sort_order' => 1,
                'status' => 0,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=300&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Oversized T-Shirts',
                'slug' => 'oversized-t-shirts',
                'sort_order' => 2,
                'status' => 0,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?w=300&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Polo T-Shirts',
                'slug' => 'polo-t-shirts',
                'sort_order' => 3,
                'status' => 0,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?w=300&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Hoodies & Sweatshirts',
                'slug' => 'hoodies-sweatshirts',
                'sort_order' => 4,
                'status' => 0,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=300&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Casual Shirts',
                'slug' => 'casual-shirts',
                'sort_order' => 5,
                'status' => 0,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=300&auto=format&fit=crop&q=80',
            ],
            [
                'name' => 'Bottomwear',
                'slug' => 'bottomwear',
                'sort_order' => 6,
                'status' => 0,
                'is_active' => true,
                'image' => 'https://images.unsplash.com/photo-1624378439575-d8705ad7ae80?w=300&auto=format&fit=crop&q=80',
            ],
        ];

        $catMap = [];
        foreach ($categoriesData as $c) {
            $cat = Category::firstOrCreate(['slug' => $c['slug']], $c);
            $cat->update([
                'name' => $c['name'],
                'sort_order' => $c['sort_order'],
                'status' => 0,
                'is_active' => true,
                'image' => $c['image'],
            ]);
            $catMap[$c['slug']] = $cat->id;
        }

        // 3. Migrate Banners
        Banner::truncate();
        Banner::create([
            'primary_text' => 'Handcrafted Menswear & Daily Essentials',
            'tagline' => 'New Season 2026',
            'image' => 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?w=1600&auto=format&fit=crop&q=80',
            'show_button' => true,
            'button_name' => 'Shop Now',
            'button_link' => '/products',
            'sort_order' => 1,
            'is_active' => true,
            'is_flash_sale' => false,
        ]);

        Banner::create([
            'primary_text' => '100% Combed Cotton Premium T-Shirts',
            'tagline' => 'Comfort Redefined',
            'image' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=1600&auto=format&fit=crop&q=80',
            'show_button' => true,
            'button_name' => 'Explore Collection',
            'button_link' => '/products',
            'sort_order' => 2,
            'is_active' => true,
            'is_flash_sale' => false,
        ]);

        // 4. Migrate Products (if catalog has fewer than 6 products)
        if (Product::whereNull('deleted_at')->count() < 6) {
            $tshirtCatId = $catMap['mens-t-shirt'] ?? null;
            $oversizedCatId = $catMap['oversized-t-shirts'] ?? null;
            $poloCatId = $catMap['polo-t-shirts'] ?? null;
            $hoodieCatId = $catMap['hoodies-sweatshirts'] ?? null;
            $shirtCatId = $catMap['casual-shirts'] ?? null;

            $productsData = [
                [
                    'name' => "Classic Solid Crew Neck T-Shirt (Navy Blue)",
                    'slug' => 'classic-solid-crew-neck-tshirt-navy',
                    'sku' => 'NC-TS-001',
                    'code' => 'NC-TS-001',
                    'price' => 599.00,
                    'special_price' => 399.00,
                    'image' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?w=800&auto=format&fit=crop&q=80',
                    'stock_status' => 'in_stock',
                    'qty' => 50,
                    'manage_inventory' => true,
                    'is_featured' => true,
                    'is_active' => true,
                    'is_returnable' => true,
                    'description' => 'Crafted from 100% combed cotton, this navy crew neck t-shirt offers a refined fit, breathability, and all-day comfort.',
                    'category_id' => $tshirtCatId,
                ],
                [
                    'name' => "Heavyweight Oversized Drop-Shoulder T-Shirt (Jet Black)",
                    'slug' => 'heavyweight-oversized-tshirt-black',
                    'sku' => 'NC-TS-002',
                    'code' => 'NC-TS-002',
                    'price' => 799.00,
                    'special_price' => 499.00,
                    'image' => 'https://images.unsplash.com/photo-1503342217505-b0a15ec3261c?w=800&auto=format&fit=crop&q=80',
                    'stock_status' => 'in_stock',
                    'qty' => 40,
                    'manage_inventory' => true,
                    'is_featured' => true,
                    'is_active' => true,
                    'is_returnable' => true,
                    'description' => 'Contemporary boxy silhouette featuring 240 GSM heavy cotton fabric for structured streetwear draping.',
                    'category_id' => $oversizedCatId,
                ],
                [
                    'name' => "Premium Pique Knit Polo T-Shirt (Cobalt Blue)",
                    'slug' => 'premium-pique-polo-cobalt-blue',
                    'sku' => 'NC-PL-003',
                    'code' => 'NC-PL-003',
                    'price' => 899.00,
                    'special_price' => 599.00,
                    'image' => 'https://images.unsplash.com/photo-1581655353564-df123a1eb820?w=800&auto=format&fit=crop&q=80',
                    'stock_status' => 'in_stock',
                    'qty' => 35,
                    'manage_inventory' => true,
                    'is_featured' => true,
                    'is_active' => true,
                    'is_returnable' => true,
                    'description' => 'Classic ribbed collar polo tailored from breathable pique cotton with matching two-button placket.',
                    'category_id' => $poloCatId,
                ],
                [
                    'name' => "Minimalist Typography Graphic T-Shirt (Off-White)",
                    'slug' => 'minimalist-graphic-tshirt-white',
                    'sku' => 'NC-TS-004',
                    'code' => 'NC-TS-004',
                    'price' => 649.00,
                    'special_price' => 449.00,
                    'image' => 'https://images.unsplash.com/photo-1529374255404-311a2a4f1fd9?w=800&auto=format&fit=crop&q=80',
                    'stock_status' => 'in_stock',
                    'qty' => 60,
                    'manage_inventory' => true,
                    'is_featured' => true,
                    'is_active' => true,
                    'is_returnable' => true,
                    'description' => 'Soft organic cotton graphic tee featuring subtle typographic chest artwork.',
                    'category_id' => $tshirtCatId,
                ],
                [
                    'name' => "Relaxed French Terry Cotton Hoodie (Charcoal)",
                    'slug' => 'relaxed-french-terry-hoodie-charcoal',
                    'sku' => 'NC-HD-005',
                    'code' => 'NC-HD-005',
                    'price' => 1499.00,
                    'special_price' => 999.00,
                    'image' => 'https://images.unsplash.com/photo-1556905055-8f358a7a47b2?w=800&auto=format&fit=crop&q=80',
                    'stock_status' => 'in_stock',
                    'qty' => 25,
                    'manage_inventory' => true,
                    'is_featured' => true,
                    'is_active' => true,
                    'is_returnable' => true,
                    'description' => 'Cozy fleece hoodie with kangaroo pocket, double-lined hood and sturdy drawcords.',
                    'category_id' => $hoodieCatId,
                ],
                [
                    'name' => "Linen Blend Casual Mandarin Collar Shirt (Olive)",
                    'slug' => 'linen-blend-casual-shirt-olive',
                    'sku' => 'NC-SH-006',
                    'code' => 'NC-SH-006',
                    'price' => 1199.00,
                    'special_price' => 799.00,
                    'image' => 'https://images.unsplash.com/photo-1596755094514-f87e34085b2c?w=800&auto=format&fit=crop&q=80',
                    'stock_status' => 'in_stock',
                    'qty' => 30,
                    'manage_inventory' => true,
                    'is_featured' => true,
                    'is_active' => true,
                    'is_returnable' => true,
                    'description' => 'Lightweight breathable linen blend shirt tailored for warm weather elegance.',
                    'category_id' => $shirtCatId,
                ],
            ];

            foreach ($productsData as $pData) {
                $catId = $pData['category_id'];
                $product = Product::firstOrCreate(['sku' => $pData['sku']], $pData);
                $product->update($pData);
                if ($catId) {
                    $product->categories()->syncWithoutDetaching([$catId => ['sort_order' => 1]]);
                }
            }
        }
    }

    public function down(): void
    {
        // Safe reversible migration
    }
};

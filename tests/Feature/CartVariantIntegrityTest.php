<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartVariantIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        StoreSetting::setValue('store_name', 'Noolandcrop');
    }

    public function test_cart_items_api_returns_variations_and_stock(): void
    {
        $cat = Category::create([
            'name' => 'Apparel',
            'slug' => 'apparel',
            'is_active' => true,
        ]);

        $product = Product::create([
            'name' => 'Linen T-Shirt',
            'slug' => 'linen-t-shirt',
            'price' => 999.00,
            'special_price' => 799.00,
            'is_active' => true,
            'stock_status' => 'in_stock',
            'qty' => 50,
            'category_id' => $cat->id,
        ]);

        $v1 = ProductVariation::create([
            'product_id' => $product->id,
            'name' => 'Size M',
            'price' => 799.00,
            'stock_status' => 'in_stock',
            'qty' => 20,
        ]);

        $v2 = ProductVariation::create([
            'product_id' => $product->id,
            'name' => 'Size XL',
            'price' => 899.00,
            'stock_status' => 'out_of_stock',
            'qty' => 0,
        ]);

        $res = $this->postJson('/api/cart/items', [
            'ids' => [$product->id],
        ]);

        $res->assertStatus(200);
        $res->assertJsonPath('success', true);
        $res->assertJsonCount(1, 'data');
        $res->assertJsonPath('data.0.id', $product->id);
        $res->assertJsonPath('data.0.variations.0.name', 'Size M');
        $res->assertJsonPath('data.0.variations.0.in_stock', true);
        $res->assertJsonPath('data.0.variations.1.name', 'Size XL');
        $res->assertJsonPath('data.0.variations.1.in_stock', false);
    }

    public function test_place_order_validates_cart_variations_and_authoritative_price(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);
        $this->actingAs($customer, 'customer');

        $product = Product::create([
            'name' => 'Premium Kurta',
            'slug' => 'premium-kurta',
            'price' => 1499.00,
            'is_active' => true,
            'stock_status' => 'in_stock',
            'manage_inventory' => true,
            'qty' => 15,
        ]);

        $v = ProductVariation::create([
            'product_id' => $product->id,
            'name' => 'Size L',
            'price' => 1299.00,
            'stock_status' => 'in_stock',
            'manage_inventory' => true,
            'qty' => 5,
        ]);

        $payload = [
            'name' => 'Ravi Kumar',
            'mobile' => '9876543210',
            'email' => 'ravi@example.com',
            'full_name' => 'Ravi Kumar',
            'address_line_1' => 'Plot 42, Cross St',
            'city' => 'Chennai',
            'state' => 'Tamil Nadu',
            'pincode' => '600001',
            'mobile_primary' => '9876543210',
            'address_type' => 'home',
            'payment_method' => 'cod',
            'items' => json_encode([
                [
                    'id' => $product->id,
                    'qty' => 2,
                    'variation_id' => $v->id,
                    'variation_name' => $v->name,
                    'price' => 10.00, // Client tries to spoof price to 10
                ],
            ]),
        ];

        $res = $this->post('/buy-now', $payload);
        $res->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'contact_mobile' => '9876543210',
            'subtotal' => 2598.00, // 2 * 1299.00 authoritative variation price
        ]);
    }
}

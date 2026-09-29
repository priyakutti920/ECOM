<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CustomerAddress;
use App\Models\Order;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EcommerceFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        StoreSetting::setValue('store_name', 'Test Store');
    }

    public function test_shop_and_products_catalog_accessible(): void
    {
        $category = Category::create([
            'name'      => 'Electronics',
            'slug'      => 'electronics',
            'is_active' => true,
            'status'    => 0,
        ]);

        $product = Product::create([
            'name'        => 'Wireless Headphones',
            'slug'        => 'wireless-headphones',
            'price'       => 1999.00,
            'is_active'   => true,
            'category_id' => $category->id,
        ]);

        $res = $this->get('/shop');
        $res->assertStatus(200);

        $resCat = $this->get('/category/electronics');
        $resCat->assertStatus(200);

        $resProd = $this->get('/product/wireless-headphones');
        $resProd->assertStatus(200);
        $resProd->assertSee('Wireless Headphones');
    }

    public function test_inactive_products_cannot_be_viewed(): void
    {
        $inactive = Product::create([
            'name'      => 'Hidden Product',
            'slug'      => 'hidden-product',
            'price'     => 500.00,
            'is_active' => false,
        ]);

        $res = $this->get('/product/hidden-product');
        $res->assertStatus(404);

        $resById = $this->get('/product/' . $inactive->id);
        $resById->assertStatus(404);
    }

    public function test_cart_and_track_order_pages(): void
    {
        $resCart = $this->get('/cart');
        $resCart->assertStatus(200);

        $resTrack = $this->get('/track-order');
        $resTrack->assertStatus(200);
    }

    public function test_admin_routes_require_admin_authentication(): void
    {
        $response = $this->get('/admin/orders');
        $response->assertRedirect('/admin/login');
    }

    public function test_customer_cannot_view_another_customer_order(): void
    {
        $customer1 = User::factory()->create(['is_admin' => false]);
        $customer2 = User::factory()->create(['is_admin' => false]);

        $order = Order::create([
            'order_code'          => 'NS0001',
            'customer_id'         => $customer1->id,
            'contact_name'        => 'Customer 1',
            'contact_mobile'      => '9876543210',
            'addr_full_name'      => 'Customer 1',
            'addr_line_1'         => '123 Street',
            'addr_city'           => 'City',
            'addr_state'          => 'State',
            'addr_pincode'        => '123456',
            'addr_mobile_primary' => '9876543210',
            'addr_type'           => 'home',
            'subtotal'            => 1000,
            'total'               => 1000,
            'payment_method'      => 'cod',
            'payment_status'      => 'pending',
            'status'              => 'placed',
        ]);

        // Customer 2 attempts to view customer 1's order
        $this->actingAs($customer2, 'customer');
        $res = $this->get('/account/orders/NS0001');
        $res->assertStatus(403);
    }
}

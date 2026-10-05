<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageProductSectionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_appearance_sections()
    {
        $response = $this->get('/admin/appearance/sections');
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_access_appearance_sections()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/admin/appearance/sections');
        $response->assertStatus(200);
        $response->assertSee('Home Product Sections Manager');
        $response->assertSee('Hot Deals');
        $response->assertSee('New Arrivals');
        $response->assertSee('Best Sellers');
        $response->assertSee('Featured Products');
    }

    public function test_admin_can_update_appearance_sections()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $payload = [
            'section_order' => ['deals', 'featured', 'bestsellers', 'latest'],
            'sections' => [
                'deals' => [
                    'title'        => 'Mega Flash Discounts',
                    'subtitle'     => 'Huge savings for 24h',
                    'enabled'      => '1',
                    'limit'        => 6,
                    'mode'         => 'auto',
                    'view_all_url' => '/deals',
                    'view_all_text'=> 'View Mega Deals',
                ],
                'featured' => [
                    'title'        => 'Curated Highlights',
                    'subtitle'     => 'Editor picks',
                    'enabled'      => '1',
                    'limit'        => 8,
                    'mode'         => 'auto',
                    'view_all_url' => '/products',
                    'view_all_text'=> 'Explore All',
                ],
                'bestsellers' => [
                    'title'        => 'Top Favorites',
                    'subtitle'     => 'Customer favorites',
                    'enabled'      => '0', // Disabled section
                    'limit'        => 4,
                    'mode'         => 'auto',
                    'view_all_url' => '/products',
                    'view_all_text'=> 'View All',
                ],
                'latest' => [
                    'title'        => 'Just Dropped',
                    'subtitle'     => 'Fresh stock',
                    'enabled'      => '1',
                    'limit'        => 10,
                    'mode'         => 'auto',
                    'view_all_url' => '/products',
                    'view_all_text'=> 'Discover More',
                ],
            ],
        ];

        $response = $this->actingAs($admin)->post('/admin/appearance/sections', $payload);
        $response->assertRedirect(route('admin.appearance.sections.index'));

        // Verify stored settings in StoreSetting
        $config = StoreSetting::getHomepageSectionsConfig();
        $this->assertEquals('Mega Flash Discounts', $config['deals']['title']);
        $this->assertTrue($config['deals']['enabled']);
        $this->assertEquals(6, $config['deals']['limit']);

        $this->assertFalse($config['bestsellers']['enabled']);
        $this->assertEquals('Just Dropped', $config['latest']['title']);
    }

    public function test_ajax_toggle_section()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->postJson('/admin/appearance/sections/toggle/deals');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $config = StoreSetting::getHomepageSectionsConfig();
        // Since default was true, toggling makes it false
        $this->assertFalse($config['deals']['enabled']);
    }

    public function test_ajax_toggle_product_featured()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $prod = Product::create([
            'name'        => 'Alpha Headphones',
            'code'        => 'AH-001',
            'slug'        => 'alpha-headphones',
            'price'       => 1999.00,
            'is_active'   => true,
            'is_featured' => false,
            'status'      => 0,
        ]);

        $response = $this->actingAs($admin)->postJson('/admin/appearance/sections/toggle-featured', [
            'product_id' => $prod->id,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'is_featured' => true]);
        $this->assertTrue($prod->fresh()->is_featured);
    }

    public function test_ajax_product_search()
    {
        $admin = User::factory()->create(['is_admin' => true]);
        Product::create([
            'name'        => 'Quantum Smart Watch',
            'code'        => 'QW-99',
            'slug'        => 'quantum-smart-watch',
            'price'       => 2499.00,
            'is_active'   => true,
            'status'      => 0,
        ]);

        $response = $this->actingAs($admin)->getJson('/admin/appearance/sections/search-products?q=Quantum');
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertCount(1, $response->json('products'));
        $this->assertEquals('Quantum Smart Watch', $response->json('products.0.name'));
    }

    public function test_homepage_reflects_custom_section_titles_and_hidden_status()
    {
        // Setup a product with special price so deals appear
        Product::create([
            'name'          => 'Deal Product Alpha',
            'code'          => 'DPA-10',
            'slug'          => 'deal-product-alpha',
            'price'         => 999.00,
            'special_price' => 799.00,
            'is_active'     => true,
            'status'        => 0,
        ]);

        // Configure deals with custom title
        $config = StoreSetting::defaultHomepageSections();
        $config['deals']['title'] = 'Lightning Deals Today';
        $config['deals']['enabled'] = true;
        $config['latest']['enabled'] = false; // Disabled section
        StoreSetting::saveHomepageSectionsConfig($config);

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Lightning Deals Today');
        $response->assertSee('Deal Product Alpha');
    }
}

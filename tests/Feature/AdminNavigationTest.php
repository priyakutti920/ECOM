<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_navigation()
    {
        $response = $this->get('/admin/navigation');
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_access_admin_navigation()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/admin/navigation');
        $response->assertStatus(200);
        $response->assertSee('Storefront Navigation Bar');
        $response->assertSee('Live Navigation Preview');
    }

    public function test_admin_can_update_navigation_settings()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $cat1 = Category::first() ?: Category::create(['name' => 'Test Cat', 'slug' => 'test-cat', 'is_active' => true, 'status' => 0]);

        $payload = [
            'nav_show_all_categories'  => '1',
            'nav_all_categories_label' => 'Explore Collections',
            'nav_show_home'            => '1',
            'nav_home_label'           => 'Start',
            'nav_show_shop'            => '1',
            'nav_shop_label'           => 'Catalog',
            'nav_show_deals'           => '1',
            'nav_deals_label'          => 'Hot Offers',
            'category_ids'             => [$cat1->id],
            'nav_promo_enabled'        => '1',
            'nav_promo_text'           => 'Special 50% Festive Promo Live Now',
            'custom_link_title'        => ['Track Now'],
            'custom_link_url'          => ['/track-order'],
            'custom_link_target'       => ['_self'],
        ];

        $response = $this->actingAs($admin)->post('/admin/navigation', $payload);
        $response->assertRedirect(route('admin.navigation.index'));

        // Verify values stored in DB
        $this->assertEquals('Explore Collections', StoreSetting::getValue('nav_all_categories_label'));
        $this->assertEquals('Start', StoreSetting::getValue('nav_home_label'));
        $this->assertEquals('Catalog', StoreSetting::getValue('nav_shop_label'));
        $this->assertEquals('Hot Offers', StoreSetting::getValue('nav_deals_label'));
        $this->assertEquals('Special 50% Festive Promo Live Now', StoreSetting::getValue('nav_promo_text'));

        // Verify storefront frontend loads successfully
        $shopResponse = $this->get('/');
        $shopResponse->assertStatus(200);
    }
}

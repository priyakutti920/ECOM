<?php

namespace Tests\Feature;

use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FooterSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_footer_settings()
    {
        $response = $this->get('/admin/appearance/footer');
        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_access_footer_settings()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/admin/appearance/footer');
        $response->assertStatus(200);
        $response->assertSee('Storefront Footer Settings');
        $response->assertSee('Live Footer Preview');
    }

    public function test_admin_can_update_footer_settings_and_reflects_on_storefront()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $payload = [
            'col1_title'          => 'Reach Out',
            'col1_phone'          => '+91 99999 88888',
            'col1_email'          => 'support@noolcrop.test',
            'col1_address'        => 'Chennai, Tamil Nadu, India',
            'col1_whatsapp'       => '9999988888',
            'col1_instagram'      => 'https://instagram.com/noolcrop_official',
            'col1_show_social'    => '1',

            'col2_title'          => 'Customer Hub',
            'col2_enabled'        => '1',
            'col2_link_titles'    => ['My Dashboard', 'Order Tracking'],
            'col2_link_urls'      => ['/account', '/track-order'],

            'col3_title'          => 'Company Info',
            'col3_enabled'        => '1',
            'col3_link_titles'    => ['Explore Catalog', 'Limited Offers'],
            'col3_link_urls'      => ['/products', '/deals'],

            'col4_title'          => 'Help & Policies',
            'col4_enabled'        => '1',
            'col4_link_titles'    => ['Return Policy', 'Payment FAQ'],
            'col4_link_urls'      => ['/help', '/help'],

            'copyright_text'      => 'Copyright © Nool & Crop Custom 2026. All rights reserved.',
            'safe_checkout_text'  => '100% Certified Safe Checkout:',
            'show_payment_badges' => '1',
        ];

        $response = $this->actingAs($admin)->post('/admin/appearance/footer', $payload);
        $response->assertRedirect(route('admin.appearance.footer.index'));

        // Check values in database
        $this->assertEquals('+91 99999 88888', StoreSetting::getValue('phone'));
        $this->assertEquals('support@noolcrop.test', StoreSetting::getValue('email'));
        $this->assertEquals('Chennai, Tamil Nadu, India', StoreSetting::getValue('address'));

        $footer = StoreSetting::getFooterSettings();
        $this->assertEquals('Reach Out', $footer['col1_title']);
        $this->assertEquals('Customer Hub', $footer['col2_title']);
        $this->assertEquals('Company Info', $footer['col3_title']);
        $this->assertEquals('Help & Policies', $footer['col4_title']);

        // Check storefront reflection
        $storeResponse = $this->get('/');
        $storeResponse->assertStatus(200);
        $storeResponse->assertSee('Reach Out');
        $storeResponse->assertSee('+91 99999 88888');
        $storeResponse->assertSee('support@noolcrop.test');
        $storeResponse->assertSee('Chennai, Tamil Nadu, India');
        $storeResponse->assertSee('Customer Hub');
        $storeResponse->assertSee('My Dashboard');
        $storeResponse->assertSee('Company Info');
        $storeResponse->assertSee('Explore Catalog');
        $storeResponse->assertSee('Help & Policies');
        $storeResponse->assertSee('Return Policy');
        $storeResponse->assertSee('Copyright © Nool & Crop Custom 2026. All rights reserved.', false);
        $storeResponse->assertDontSee('Guaranteed Safe Checkout:');
    }

    public function test_admin_can_reset_footer_settings()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post('/admin/appearance/footer/reset');
        $response->assertRedirect(route('admin.appearance.footer.index'));

        $footer = StoreSetting::getFooterSettings();
        $this->assertEquals('Contact Us', $footer['col1_title']);
        $this->assertEquals('My Account', $footer['col2_title']);
        $this->assertEquals('Information', $footer['col3_title']);
        $this->assertEquals('Customer Service', $footer['col4_title']);
    }
}

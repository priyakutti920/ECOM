<?php

namespace Tests\Feature;

use App\Models\StoreSetting;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_phone_number_normalization_prepends_country_code_for_10_digits(): void
    {
        $this->assertEquals('918056081594', StoreSetting::normalizePhoneNumber('8056081594'));
        $this->assertEquals('918056081594', StoreSetting::normalizePhoneNumber('+91 80560 81594'));
        $this->assertEquals('918056081594', StoreSetting::normalizePhoneNumber('08056081594'));
        $this->assertEquals('919876543210', StoreSetting::normalizePhoneNumber('9876-543-210'));
        $this->assertEquals('', StoreSetting::normalizePhoneNumber(''));
    }

    public function test_whatsapp_service_normalize_phone_and_url_builder(): void
    {
        StoreSetting::setValue('wa_number', '8056081594');
        StoreSetting::setValue('wa_enabled', '1');

        $url = StoreSetting::getWhatsAppUrl('Hello Store');
        $this->assertEquals('https://wa.me/918056081594?text=Hello%20Store', $url);

        $pluginUrl = WhatsAppService::getPluginWhatsAppUrl('Hi');
        $this->assertStringContainsString('https://api.whatsapp.com/send?phone=918056081594', $pluginUrl);
    }

    public function test_admin_can_save_plugin_whatsapp_settings(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin, 'web');

        $res = $this->postJson(route('admin.settings.plugin-save'), [
            'wa_enabled' => '1',
            'wa_number' => '9876543210',
            'wa_message' => 'Need help with product',
        ]);

        $res->assertStatus(200);
        $res->assertJson(['success' => true]);

        $this->assertEquals('9876543210', StoreSetting::getValue('wa_number'));
        $this->assertEquals('919876543210', StoreSetting::getWhatsAppNumber());
    }

    public function test_storefront_footer_and_help_render_normalized_whatsapp_url(): void
    {
        StoreSetting::setValue('whatsapp_number', '8056081594');
        StoreSetting::setValue('wa_number', '8056081594');

        $footerRes = $this->get(route('shop.home'));
        $footerRes->assertStatus(200);
        $footerRes->assertSee('https://wa.me/918056081594', false);

        $helpRes = $this->get('/help');
        $helpRes->assertStatus(200);
        $helpRes->assertSee('https://wa.me/918056081594', false);
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\StoreSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeAppearanceCustomizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        StoreSetting::setValue('store_name', 'Nool & Crop');
    }

    public function test_guest_cannot_access_theme_customization()
    {
        $response = $this->get(route('admin.appearance.theme.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_theme_customizer()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('admin.appearance.theme.index'));
        $response->assertStatus(200);
        $response->assertSee('Theme &amp; Appearance Customization', false);
        $response->assertSee('1-Click Curated Theme Palettes');
    }

    public function test_admin_can_update_theme_via_theme_customizer()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->postJson(route('admin.appearance.theme.update'), [
            'primary_color'   => '#00e0bb',
            'secondary_color' => '#0E1E3E',
            'theme_font'      => 'Outfit',
            'header_style'    => 'primary',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals('#00e0bb', StoreSetting::getPrimaryColor());
        $this->assertEquals('#0E1E3E', StoreSetting::getSecondaryColor());
        $this->assertEquals('Outfit', StoreSetting::getValue('theme_font'));
        $this->assertEquals('primary', StoreSetting::getHeaderStyle());
    }

    public function test_admin_can_update_colors_and_header_style_via_store_settings()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.settings.store-save'), [
            'store_name'      => 'Nool & Crop Fashion',
            'primary_color'   => '#e11d48',
            'secondary_color' => '#881337',
            'header_style'    => 'primary',
            'currency_symbol' => '₹',
        ]);

        $response->assertStatus(200);

        $this->assertEquals('#e11d48', StoreSetting::getPrimaryColor());
        $this->assertEquals('#881337', StoreSetting::getSecondaryColor());
        $this->assertEquals('primary', StoreSetting::getHeaderStyle());
    }

    public function test_storefront_renders_primary_color_and_header_style_css()
    {
        StoreSetting::setValue('primary_color', '#00e0bb');
        StoreSetting::setValue('secondary_color', '#0E1E3E');
        StoreSetting::setValue('header_style', 'primary');
        StoreSetting::setValue('theme_font', 'Plus Jakarta Sans');

        $response = $this->get(url('/'));
        $response->assertStatus(200);

        $html = $response->getContent();

        // Verifies CSS variable injection
        $this->assertStringContainsString('--color-primary: #00e0bb;', $html);
        $this->assertStringContainsString('--color-secondary: #0E1E3E;', $html);
        $this->assertStringContainsString("'Plus Jakarta Sans'", $html);

        // Verifies Bold Brand Header styling is active
        $this->assertStringContainsString('.header-wrap {', $html);
        $this->assertStringContainsString('background: var(--color-primary) !important;', $html);

        // Verifies Product Card button uses primary tint
        $this->assertStringContainsString('background: rgba(var(--color-primary-rgb), 0.08);', $html);
    }

    public function test_admin_can_reset_theme_to_defaults()
    {
        $admin = User::factory()->create(['is_admin' => true]);

        StoreSetting::setValue('primary_color', '#ea580c');
        StoreSetting::setValue('secondary_color', '#7c2d12');

        $response = $this->actingAs($admin)->postJson(route('admin.appearance.theme.reset'));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertEquals('#0068e1', StoreSetting::getPrimaryColor());
        $this->assertEquals('#0f172a', StoreSetting::getSecondaryColor());
        $this->assertEquals('primary', StoreSetting::getHeaderStyle());
    }
}

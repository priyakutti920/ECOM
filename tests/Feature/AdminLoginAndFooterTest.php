<?php

namespace Tests\Feature;

use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginAndFooterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@noolandcrop.com',
            'password' => Hash::make('Secret123!'),
            'is_admin' => true,
        ]);

        $response = $this->post(route('admin.login'), [
            'email' => 'admin@noolandcrop.com',
            'password' => 'Secret123!',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_admin_login_rejects_invalid_password(): void
    {
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@noolandcrop.com',
            'password' => Hash::make('Secret123!'),
            'is_admin' => true,
        ]);

        $response = $this->from(route('admin.login'))->post(route('admin.login'), [
            'email' => 'admin@noolandcrop.com',
            'password' => 'WrongPassword',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_login_rejects_non_admin_user(): void
    {
        User::create([
            'name' => 'Regular Customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('Secret123!'),
            'is_admin' => false,
        ]);

        $response = $this->from(route('admin.login'))->post(route('admin.login'), [
            'email' => 'customer@example.com',
            'password' => 'Secret123!',
        ]);

        $response->assertRedirect(route('admin.login'));
        $response->assertSessionHasErrors(['email' => 'You do not have admin access.']);
        $this->assertGuest();
    }

    public function test_admin_logout_invalidates_session(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@noolandcrop.com',
            'password' => Hash::make('Secret123!'),
            'is_admin' => true,
        ]);

        $this->actingAs($admin);
        $this->assertAuthenticatedAs($admin);

        $response = $this->post(route('admin.logout'));
        $response->assertRedirect(route('admin.login'));
        $this->assertGuest();
    }

    public function test_storefront_footer_displays_current_year_and_cloudaspire_credit(): void
    {
        StoreSetting::setValue('store_name', 'Nool & Crop');
        StoreSetting::setValue('cloudaspire_credit_enabled', '1');
        StoreSetting::setValue('cloudaspire_url', 'https://cloudaspire.in');

        $response = $this->get(route('shop.home'));
        $response->assertStatus(200);

        $currentYear = date('Y');
        $response->assertSee("Copyright © Nool & Crop {$currentYear}", false);
        $response->assertSee('Cloudaspire');
        $response->assertSee('https://cloudaspire.in');
    }
}

<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserAccessTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name'      => 'Super Admin',
            'email'     => 'admin@noolandcrop.com',
            'password'  => Hash::make('Password@123'),
            'mobile'    => '9876543210',
            'is_admin'  => true,
        ]);

        $this->customer = User::create([
            'name'      => 'Regular Customer',
            'email'     => 'customer@example.com',
            'password'  => Hash::make('Password@123'),
            'mobile'    => '9876543211',
            'is_admin'  => false,
        ]);
    }

    /**
     * Test admin users table displays Edit Access button and modal markup.
     */
    public function test_admin_users_table_displays_edit_access_button(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertSee('Edit Access', false);
        $response->assertSee('btn-edit-user-access', false);
        $response->assertSee('editUserAccessModal', false);
        $response->assertSee('editUserAccessForm', false);
        $response->assertSee('Grant full administrator privileges', false);
    }

    /**
     * Test unauthenticated or non-admin user cannot update user access.
     */
    public function test_guest_cannot_update_user_access(): void
    {
        $response = $this->put('/admin/users/' . $this->customer->id, [
            'name'     => 'Hacked Name',
            'email'    => 'hacked@example.com',
            'is_admin' => 1,
        ]);

        $response->assertRedirect('/admin/login');
        $this->customer->refresh();
        $this->assertFalse((bool) $this->customer->is_admin);
        $this->assertEquals('Regular Customer', $this->customer->name);
    }

    /**
     * Test admin can promote customer to administrator.
     */
    public function test_admin_can_grant_admin_access_to_user(): void
    {
        $response = $this->actingAs($this->admin)->put('/admin/users/' . $this->customer->id, [
            'name'     => 'Promoted Manager',
            'email'    => 'promoted@example.com',
            'mobile'   => '9876543299',
            'is_admin' => 1,
        ]);

        $response->assertSessionHas('success');
        $this->customer->refresh();
        $this->assertTrue((bool) $this->customer->is_admin);
        $this->assertEquals('Promoted Manager', $this->customer->name);
        $this->assertEquals('promoted@example.com', $this->customer->email);
        $this->assertEquals('9876543299', $this->customer->mobile);
    }

    /**
     * Test admin can revoke administrator access from another admin.
     */
    public function test_admin_can_revoke_admin_access_from_another_user(): void
    {
        $secondaryAdmin = User::create([
            'name'     => 'Secondary Admin',
            'email'    => 'secadmin@example.com',
            'password' => Hash::make('Password@123'),
            'is_admin' => true,
        ]);

        $response = $this->actingAs($this->admin)->put('/admin/users/' . $secondaryAdmin->id, [
            'name'     => 'Demoted User',
            'email'    => 'secadmin@example.com',
            'is_admin' => 0,
        ]);

        $response->assertSessionHas('success');
        $secondaryAdmin->refresh();
        $this->assertFalse((bool) $secondaryAdmin->is_admin);
    }

    /**
     * Test admin cannot revoke their own administrator access.
     */
    public function test_admin_cannot_revoke_own_admin_access(): void
    {
        $response = $this->actingAs($this->admin)->put('/admin/users/' . $this->admin->id, [
            'name'     => 'Super Admin',
            'email'    => 'admin@noolandcrop.com',
            'is_admin' => 0,
        ]);

        $response->assertSessionHas('error');
        $this->admin->refresh();
        $this->assertTrue((bool) $this->admin->is_admin);
    }

    /**
     * Test optional password update changes hash when provided.
     */
    public function test_admin_can_optionally_update_user_password(): void
    {
        $response = $this->actingAs($this->admin)->put('/admin/users/' . $this->customer->id, [
            'name'     => 'Regular Customer',
            'email'    => 'customer@example.com',
            'password' => 'NewSecretPassword123',
            'is_admin' => 0,
        ]);

        $response->assertSessionHas('success');
        $this->customer->refresh();
        $this->assertTrue(Hash::check('NewSecretPassword123', $this->customer->password));
    }

    /**
     * Test JSON API response when requested via AJAX.
     */
    public function test_ajax_update_returns_json_response(): void
    {
        $response = $this->actingAs($this->admin)->putJson('/admin/users/' . $this->customer->id, [
            'name'     => 'Ajax Updated',
            'email'    => 'ajax@example.com',
            'is_admin' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'user'    => [
                'id'       => $this->customer->id,
                'name'     => 'Ajax Updated',
                'email'    => 'ajax@example.com',
                'is_admin' => true,
            ],
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InstallerTest extends TestCase
{
    use RefreshDatabase;

    protected array $lockFiles;

    protected function setUp(): void
    {
        parent::setUp();
        $this->lockFiles = [
            base_path('install.lock'),
            storage_path('install.lock'),
            storage_path('installed'),
        ];
        foreach ($this->lockFiles as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->lockFiles as $file) {
            if (file_exists($file)) {
                @unlink($file);
            }
        }
        parent::tearDown();
    }

    public static function tearDownAfterClass(): void
    {
        // Re-lock the application after running installer tests so production lock remains active
        $data = json_encode([
            'installed_at' => date('c'),
            'version' => '1.0.0',
        ]);
        @file_put_contents(base_path('install.lock'), $data);
        @file_put_contents(storage_path('install.lock'), $data);
        @file_put_contents(storage_path('installed'), $data);
        parent::tearDownAfterClass();
    }

    public function test_installer_welcome_screen_loads(): void
    {
        $response = $this->get('/install');
        $response->assertStatus(200);
        $response->assertSee('Ready to launch your online store');
        $response->assertSee('Check Server Requirements');
    }

    public function test_requirements_screen_checks_php_and_permissions(): void
    {
        $response = $this->get('/install/requirements');
        $response->assertStatus(200);
        $response->assertSee('PHP Runtime');
        $response->assertSee('Required PHP Extensions');
        $response->assertSee('File Permissions');
    }

    public function test_database_screen_displays_form(): void
    {
        $response = $this->get('/install/database');
        $response->assertStatus(200);
        $response->assertSee('Database Driver');
        $response->assertSee('Test Connection');
        $response->assertSee('Proceed to Migrations');
    }

    public function test_database_connection_ajax_test_with_sqlite(): void
    {
        $response = $this->postJson('/install/database/test', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_migrations_screen_loads(): void
    {
        $response = $this->get('/install/migrations');
        $response->assertStatus(200);
        $response->assertSee('Execute Schema Migrations');
        $response->assertSee('Essential Gateway');
    }

    public function test_admin_screen_loads(): void
    {
        $response = $this->get('/install/admin');
        $response->assertStatus(200);
        $response->assertSee('Super Administrator Setup');
        $response->assertSee('Finalize Installation');
    }

    public function test_admin_save_creates_super_admin_and_logs_in(): void
    {
        $response = $this->post('/install/admin', [
            'store_name'            => 'Test Super Store',
            'name'                  => 'Master Admin',
            'email'                 => 'admin@installer-test.com',
            'password'              => 'SuperSecretPassword123!',
            'password_confirmation' => 'SuperSecretPassword123!',
            'store_phone'           => '+91 9999999999',
        ]);

        $response->assertRedirect('/install/complete');

        $this->assertDatabaseHas('users', [
            'email'    => 'admin@installer-test.com',
            'is_admin' => true,
        ]);

        $admin = User::where('email', 'admin@installer-test.com')->first();
        $this->assertTrue(Hash::check('SuperSecretPassword123!', $admin->password));
        $this->assertAuthenticatedAs($admin);

        $this->assertEquals('Test Super Store', StoreSetting::getValue('store_name'));
    }

    public function test_complete_screen_creates_lock_file(): void
    {
        $this->assertFalse(file_exists(base_path('install.lock')));

        $response = $this->get('/install/complete');
        $response->assertStatus(200);
        $response->assertSee('Setup Successfully Completed');
        $response->assertSee('install.lock active');

        $this->assertTrue(file_exists(base_path('install.lock')));
    }

    public function test_subsequent_visits_show_already_installed_when_locked(): void
    {
        // Lock the application
        file_put_contents(base_path('install.lock'), json_encode(['installed_at' => now()->toIso8601String()]));

        $response = $this->get('/install');
        $response->assertStatus(200);
        $response->assertSee('Application Already Installed');
        $response->assertSee('Installation Locked');
        $response->assertSee('php artisan app:reset-install');
    }

    public function test_cli_reset_install_command(): void
    {
        file_put_contents(base_path('install.lock'), 'locked');
        $this->assertTrue(file_exists(base_path('install.lock')));

        $this->artisan('app:reset-install', ['--force' => true])
            ->assertSuccessful();

        $this->assertFalse(file_exists(base_path('install.lock')));
    }

    public function test_automatically_redirects_to_installer_when_env_file_is_missing(): void
    {
        app()->instance('test_missing_env_redirect', true);

        $response = $this->get('/');
        $response->assertRedirect('/install');
    }

    public function test_automatically_redirects_to_installer_when_lock_file_is_missing(): void
    {
        // lockFiles are unlinked in setUp
        $response = $this->get('/');
        $response->assertRedirect('/install');
    }
}

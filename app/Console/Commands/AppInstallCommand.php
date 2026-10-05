<?php

namespace App\Console\Commands;

use App\Models\StoreSetting;
use App\Models\User;
use App\Services\Install\EnvironmentManager;
use App\Services\Install\RequirementsChecker;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

class AppInstallCommand extends Command
{
    protected $signature = 'app:install 
                            {--force : Force installation even if already installed}
                            {--fresh : Run migrate:fresh to wipe existing tables}
                            {--admin-name= : Initial Administrator full name}
                            {--admin-email= : Initial Administrator email}
                            {--admin-password= : Initial Administrator password}
                            {--store-name= : E-Commerce Store Name}';

    protected $description = 'Clean automated installer command for the E-Commerce application';

    public function handle(RequirementsChecker $checker, EnvironmentManager $envManager): int
    {
        $this->info('====================================================');
        $this->info('  E-COMMERCE STORE — CLI INSTALLATION WIZARD       ');
        $this->info('====================================================');

        if ($envManager->isInstalled() && !$this->option('force')) {
            $this->warn('Application is already installed! Use --force to reinstall or run `php artisan app:reset-install`.');
            return self::FAILURE;
        }

        // 1. Requirements Check
        $this->line('');
        $this->line('1. Checking Server & PHP Requirements...');
        $php = $checker->checkPhpVersion();
        if (!$php['passed']) {
            $this->error('✗ ' . $php['message']);
            return self::FAILURE;
        }
        $this->info('  ✓ PHP Version: ' . $php['current']);

        $extensions = $checker->checkExtensions();
        $missingReq = false;
        foreach ($extensions as $ext) {
            if ($ext['required'] && !$ext['passed']) {
                $this->error("  ✗ Missing required extension: {$ext['name']}");
                $missingReq = true;
            }
        }
        if ($missingReq) {
            return self::FAILURE;
        }
        $this->info('  ✓ All required PHP extensions loaded.');

        $permissions = $checker->checkPermissions();
        $missingPerms = false;
        foreach ($permissions as $perm) {
            if (!$perm['writable']) {
                $this->error("  ✗ Path not writable: {$perm['name']}");
                $missingPerms = true;
            }
        }
        if ($missingPerms) {
            return self::FAILURE;
        }
        $this->info('  ✓ Storage & cache directory permissions OK.');

        // 2. Database Migrations
        $this->line('');
        $this->line('2. Running Database Migrations...');
        $fresh = $this->option('fresh');
        $command = $fresh ? 'migrate:fresh' : 'migrate';

        $exitCode = Artisan::call($command, ['--force' => true], $this->output);
        if ($exitCode !== 0) {
            $this->error('✗ Database migration failed.');
            return self::FAILURE;
        }
        $this->info('  ✓ Database schema synchronized successfully.');

        // 3. Seed Essential Data
        $this->line('');
        $this->line('3. Seeding Essential Gateway & Store Data...');
        try {
            Artisan::call('db:seed', ['--class' => 'PaymentGatewaySeeder', '--force' => true]);
            Artisan::call('db:seed', ['--class' => 'BillingTemplateSeeder', '--force' => true]);
            $this->info('  ✓ Payment gateways & billing templates seeded.');
        } catch (Exception $e) {
            $this->warn('  Note: ' . $e->getMessage());
        }

        // 4. Admin Account
        $this->line('');
        $this->line('4. Setting up Super Administrator Account...');
        $storeName = $this->option('store-name') ?: ($this->input->isInteractive() ? $this->ask('Store Name', 'My Online Store') : 'My Online Store');
        $adminName = $this->option('admin-name') ?: ($this->input->isInteractive() ? $this->ask('Admin Full Name', 'Store Administrator') : 'Store Administrator');
        $adminEmail = $this->option('admin-email') ?: ($this->input->isInteractive() ? $this->ask('Admin Email Address', 'admin@example.com') : 'admin@example.com');
        $adminPassword = $this->option('admin-password');

        if (empty($adminPassword)) {
            if ($this->input->isInteractive()) {
                $adminPassword = $this->secret('Admin Password (min 8 characters)');
            } else {
                $adminPassword = 'AdminPassword123!';
            }
        }

        $user = User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name'     => $adminName,
                'password' => Hash::make($adminPassword),
            ]
        );
        $user->is_admin = true;
        $user->save();

        StoreSetting::setValue('store_name', $storeName);
        StoreSetting::setValue('email', $adminEmail);

        $this->info("  ✓ Super Administrator created: {$adminEmail}");

        // 5. Finalize Lock File
        $envManager->createLockFile([
            'admin_user' => $adminEmail,
            'installed_at' => now()->toIso8601String(),
        ]);
        Artisan::call('optimize:clear');

        $this->line('');
        $this->info('====================================================');
        $this->info('  🎉 INSTALLATION COMPLETE! APPLICATION READY!     ');
        $this->info('====================================================');
        $this->info('Storefront: ' . url('/'));
        $this->info('Admin Panel: ' . url('/admin'));
        $this->info("Admin Login: {$adminEmail}");
        $this->line('');

        return self::SUCCESS;
    }
}

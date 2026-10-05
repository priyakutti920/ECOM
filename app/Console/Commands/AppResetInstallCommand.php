<?php

namespace App\Console\Commands;

use App\Services\Install\EnvironmentManager;
use Illuminate\Console\Command;

class AppResetInstallCommand extends Command
{
    protected $signature = 'app:reset-install {--force : Force the operation without confirmation}';
    protected $description = 'Remove the installation lock file to allow re-running the installation wizard';

    public function handle(EnvironmentManager $envManager): int
    {
        if (!$envManager->isInstalled()) {
            $this->info('Application is not currently locked as installed.');
            return self::SUCCESS;
        }

        if (!$this->option('force')) {
            if (!$this->confirm('This will unlock the web installer (/install). Do you wish to continue?', false)) {
                $this->warn('Operation cancelled.');
                return self::SUCCESS;
            }
        }

        if ($envManager->removeLockFile()) {
            $this->call('optimize:clear');
            $this->info('✓ Installation lock file removed successfully.');
            $this->info('You may now visit http://your-domain/install to run the setup wizard.');
            return self::SUCCESS;
        }

        $this->error('Failed to remove lock file at ' . storage_path('installed'));
        return self::FAILURE;
    }
}

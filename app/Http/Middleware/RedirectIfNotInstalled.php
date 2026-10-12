<?php

namespace App\Http\Middleware;

use App\Services\Install\EnvironmentManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfNotInstalled
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var EnvironmentManager $envManager */
        $envManager = app(EnvironmentManager::class);
        $isInstalled = $envManager->isInstalled();

        // Prevent session errors if database does not exist yet (only in non-testing environments)
        if (!app()->environment('testing')) {
            if (!$isInstalled || !file_exists(base_path('.env'))) {
                config([
                    'session.driver' => 'file',
                ]);
            }
        }

        // Always allow installer routes, aliases, and health checks to pass through
        if (
            $request->is('install') || 
            $request->is('install/*') || 
            $request->is('api/install/*') || 
            $request->is('installer') || 
            $request->is('installer/*') || 
            $request->is('setup') || 
            $request->is('setup/*') || 
            $request->is('up')
        ) {
            if (!file_exists(base_path('.env'))) {
                $this->initializeBaselineEnv();
            }
            return $next($request);
        }

        // If install.lock does NOT exist or .env does not exist, automatically redirect to installer
        if (!$isInstalled || !file_exists(base_path('.env')) || app()->bound('test_missing_env_redirect')) {
            if (!app()->bound('test_missing_env_redirect') && !file_exists(base_path('.env'))) {
                $this->initializeBaselineEnv();
            }
            return redirect()->route('install.index');
        }

        return $next($request);
    }

    protected function initializeBaselineEnv(): void
    {
        try {
            /** @var EnvironmentManager $envManager */
            $envManager = app(EnvironmentManager::class);
            $envManager->ensureEnvExists();

            if (empty(config('app.key')) && empty($envManager->getEnvValue('APP_KEY'))) {
                Artisan::call('key:generate', ['--force' => true]);
            }
        } catch (\Throwable $e) {
            if (empty(config('app.key'))) {
                config(['app.key' => 'base64:' . base64_encode(Str::random(32))]);
            }
        }
    }
}

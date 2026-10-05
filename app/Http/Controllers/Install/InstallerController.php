<?php

namespace App\Http\Controllers\Install;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\Install\EnvironmentManager;
use App\Services\Install\RequirementsChecker;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class InstallerController extends Controller
{
    protected RequirementsChecker $checker;
    protected EnvironmentManager $envManager;

    public function __construct(RequirementsChecker $checker, EnvironmentManager $envManager)
    {
        $this->checker = $checker;
        $this->envManager = $envManager;
    }

    public function index(): View|RedirectResponse
    {
        if ($this->envManager->isInstalled()) {
            return view('install.already-installed');
        }

        return view('install.welcome');
    }

    public function requirements(): View|RedirectResponse
    {
        if ($this->envManager->isInstalled()) {
            return redirect()->route('install.index');
        }

        $php = $this->checker->checkPhpVersion();
        $extensions = $this->checker->checkExtensions();
        $pdo = $this->checker->checkPdoDrivers();
        $permissions = $this->checker->checkPermissions();
        $allMet = $this->checker->meetsAllCriticalRequirements();

        return view('install.requirements', compact(
            'php',
            'extensions',
            'pdo',
            'permissions',
            'allMet'
        ));
    }

    public function database(): View|RedirectResponse
    {
        if ($this->envManager->isInstalled()) {
            return redirect()->route('install.index');
        }

        $currentConfig = [
            'app_name'      => config('app.name', 'E-Commerce Store'),
            'app_url'       => config('app.url', url('/')),
            'app_env'       => config('app.env', 'production'),
            'app_debug'     => config('app.debug', false),
            'db_connection' => config('database.default', 'mysql'),
            'db_host'       => config('database.connections.mysql.host', '127.0.0.1'),
            'db_port'       => config('database.connections.mysql.port', '3306'),
            'db_database'   => config('database.connections.mysql.database', 'e-com'),
            'db_username'   => config('database.connections.mysql.username', 'root'),
            'db_password'   => config('database.connections.mysql.password', ''),
        ];

        return view('install.database', compact('currentConfig'));
    }

    public function testDatabase(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'driver'   => 'required|in:mysql,sqlite,pgsql',
            'host'     => 'nullable|string',
            'port'     => 'nullable|numeric',
            'database' => 'required|string',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
        ]);

        $result = $this->envManager->testDatabaseConnection($validated);

        return response()->json($result);
    }

    public function createDatabase(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'driver'   => 'required|in:mysql',
            'host'     => 'nullable|string',
            'port'     => 'nullable|numeric',
            'database' => 'required|string',
            'username' => 'nullable|string',
            'password' => 'nullable|string',
        ]);

        $result = $this->envManager->createDatabase($validated);

        return response()->json($result);
    }

    public function saveDatabase(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'app_name'      => 'required|string|max:100',
            'app_url'       => 'required|url',
            'app_env'       => 'required|in:local,production',
            'app_debug'     => 'nullable|boolean',
            'db_connection' => 'required|in:mysql,sqlite,pgsql',
            'db_host'       => 'nullable|string',
            'db_port'       => 'nullable|numeric',
            'db_database'   => 'required|string',
            'db_username'   => 'nullable|string',
            'db_password'   => 'nullable|string',
        ]);

        // Verify connection before saving
        $testResult = $this->envManager->testDatabaseConnection([
            'driver'   => $validated['db_connection'],
            'host'     => $validated['db_host'] ?? '127.0.0.1',
            'port'     => $validated['db_port'] ?? 3306,
            'database' => $validated['db_database'],
            'username' => $validated['db_username'] ?? '',
            'password' => $validated['db_password'] ?? '',
        ]);

        if (!$testResult['success']) {
            return back()->withInput()->withErrors([
                'database' => $testResult['message'],
            ]);
        }

        // Update .env file
        $envData = [
            'APP_NAME'      => $validated['app_name'],
            'APP_URL'       => $validated['app_url'],
            'APP_ENV'       => $validated['app_env'],
            'APP_DEBUG'     => $request->boolean('app_debug') ? 'true' : 'false',
            'DB_CONNECTION' => $validated['db_connection'],
            'DB_HOST'       => $validated['db_host'] ?? '127.0.0.1',
            'DB_PORT'       => $validated['db_port'] ?? '3306',
            'DB_DATABASE'   => $validated['db_database'],
            'DB_USERNAME'   => $validated['db_username'] ?? '',
            'DB_PASSWORD'   => $validated['db_password'] ?? '',
        ];

        $this->envManager->updateEnv($envData);

        // Ensure APP_KEY exists
        if (empty(config('app.key')) && empty($this->envManager->getEnvValue('APP_KEY'))) {
            try {
                Artisan::call('key:generate', ['--force' => true]);
            } catch (Exception $e) {
                // Ignore if in restricted environment
            }
        }

        return redirect()->route('install.migrations')->with('success', 'Environment and Database settings saved successfully.');
    }

    public function migrations(): View|RedirectResponse
    {
        if ($this->envManager->isInstalled()) {
            return redirect()->route('install.index');
        }

        return view('install.migrations');
    }

    public function runMigrations(Request $request): JsonResponse|RedirectResponse
    {
        if ($this->envManager->isInstalled()) {
            return redirect()->route('install.index');
        }

        @set_time_limit(300);

        $fresh = $request->boolean('fresh', false);
        $seedDemo = $request->boolean('seed_demo', true);

        try {
            // Reconfigure database connection dynamically from current environment
            config([
                'database.default' => env('DB_CONNECTION', config('database.default')),
                'database.connections.' . env('DB_CONNECTION', 'mysql') . '.database' => env('DB_DATABASE', config('database.connections.mysql.database')),
                'database.connections.' . env('DB_CONNECTION', 'mysql') . '.host' => env('DB_HOST', config('database.connections.mysql.host')),
                'database.connections.' . env('DB_CONNECTION', 'mysql') . '.username' => env('DB_USERNAME', config('database.connections.mysql.username')),
                'database.connections.' . env('DB_CONNECTION', 'mysql') . '.password' => env('DB_PASSWORD', config('database.connections.mysql.password')),
            ]);
            DB::purge();

            $command = $fresh ? 'migrate:fresh' : 'migrate';
            $exitCode = Artisan::call($command, ['--force' => true]);
            $migrationOutput = Artisan::output();

            if ($exitCode !== 0) {
                throw new Exception("Migration failed with code {$exitCode}: {$migrationOutput}");
            }

            // Seed default payment gateways and template seeders if needed
            try {
                Artisan::call('db:seed', [
                    '--class' => 'PaymentGatewaySeeder',
                    '--force' => true,
                ]);
            } catch (Exception $e) {
                // Non-fatal if already seeded by migration
            }

            try {
                Artisan::call('db:seed', [
                    '--class' => 'BillingTemplateSeeder',
                    '--force' => true,
                ]);
            } catch (Exception $e) {
                // Non-fatal
            }

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Database tables and essential schema created successfully.',
                    'output'  => $migrationOutput,
                ]);
            }

            return redirect()->route('install.admin')->with('success', 'Database migrations completed successfully.');
        } catch (Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Migration error: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withErrors(['migration' => 'Migration error: ' . $e->getMessage()]);
        }
    }

    public function admin(): View|RedirectResponse
    {
        if ($this->envManager->isInstalled()) {
            return redirect()->route('install.index');
        }

        $storeName = config('app.name', 'My Online Store');

        return view('install.admin', compact('storeName'));
    }

    public function saveAdmin(Request $request): RedirectResponse
    {
        if ($this->envManager->isInstalled()) {
            return redirect()->route('install.index');
        }

        $validated = $request->validate([
            'store_name'            => 'required|string|max:100',
            'name'                  => 'required|string|max:100',
            'email'                 => 'required|email|max:150',
            'password'              => 'required|min:8|confirmed',
            'store_phone'           => 'nullable|string|max:30',
        ]);

        try {
            // Create or update Super Admin user
            $user = User::updateOrCreate(
                ['email' => $validated['email']],
                [
                    'name'     => $validated['name'],
                    'password' => Hash::make($validated['password']),
                ]
            );

            $user->is_admin = true;
            $user->save();

            // Store Settings
            StoreSetting::setValue('store_name', $validated['store_name']);
            StoreSetting::setValue('email', $validated['email']);
            if (!empty($validated['store_phone'])) {
                StoreSetting::setValue('phone', $validated['store_phone']);
            }

            // Auto-login newly created admin
            Auth::login($user);

            return redirect()->route('install.complete');
        } catch (Exception $e) {
            return back()->withInput()->withErrors([
                'admin' => 'Failed to initialize administrator: ' . $e->getMessage(),
            ]);
        }
    }

    public function complete(): View|RedirectResponse
    {
        // Write installation lock file
        $this->envManager->createLockFile([
            'admin_user' => Auth::user()?->email ?? 'configured',
            'installed_at' => now()->toIso8601String(),
        ]);

        // Clear optimize caches so new configuration takes effect
        try {
            Artisan::call('optimize:clear');
        } catch (Exception $e) {
            // non-fatal
        }

        return view('install.complete');
    }
}

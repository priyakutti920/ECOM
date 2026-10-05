<?php

namespace App\Services\Install;

use PDO;
use Exception;
use Illuminate\Support\Str;

class EnvironmentManager
{
    protected string $envPath;
    protected string $envExamplePath;
    protected string $lockFilePath;

    public function __construct()
    {
        $this->envPath = base_path('.env');
        $this->envExamplePath = base_path('.env.example');
        $this->lockFilePath = storage_path('installed');
    }

    public function isInstalled(): bool
    {
        return file_exists($this->lockFilePath);
    }

    public function createLockFile(array $metadata = []): bool
    {
        $data = array_merge([
            'installed_at'    => now()->toIso8601String(),
            'laravel_version' => app()->version(),
            'php_version'     => PHP_VERSION,
        ], $metadata);

        return file_put_contents($this->lockFilePath, json_encode($data, JSON_PRETTY_PRINT)) !== false;
    }

    public function removeLockFile(): bool
    {
        if (file_exists($this->lockFilePath)) {
            return unlink($this->lockFilePath);
        }
        return true;
    }

    public function ensureEnvExists(): void
    {
        if (!file_exists($this->envPath)) {
            if (file_exists($this->envExamplePath)) {
                copy($this->envExamplePath, $this->envPath);
            } else {
                file_put_contents($this->envPath, "APP_NAME=\"Laravel\"\nAPP_ENV=local\nAPP_KEY=\nAPP_DEBUG=true\nAPP_URL=http://localhost\n");
            }

            $this->updateEnv([
                'SESSION_DRIVER' => 'file',
                'CACHE_STORE'    => 'file',
            ]);
        }
    }

    public function getEnvValue(string $key, $default = null): ?string
    {
        if (!file_exists($this->envPath)) {
            return $default;
        }

        $lines = file($this->envPath, FILE_IGNORE_NEW_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$k, $v] = explode('=', $line, 2);
            if (trim($k) === $key) {
                return trim(trim($v), '"\'');
            }
        }

        return $default;
    }

    public function updateEnv(array $data): bool
    {
        $this->ensureEnvExists();
        $content = file_get_contents($this->envPath);

        foreach ($data as $key => $val) {
            $key = strtoupper(trim($key));
            $formattedValue = $this->formatEnvValue($val);

            // Regex match for existing key
            $pattern = "/^({$key}\s*=\s*)(.*)$/m";

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, "{$key}={$formattedValue}", $content);
            } else {
                // If not found, append to content
                $content .= "\n{$key}={$formattedValue}";
            }
        }

        return file_put_contents($this->envPath, $content) !== false;
    }

    protected function formatEnvValue($val): string
    {
        if (is_bool($val)) {
            return $val ? 'true' : 'false';
        }

        if ($val === null) {
            return '';
        }

        $val = (string) $val;

        // If string contains spaces, quotes, or special shell characters, wrap in double quotes
        if (str_contains($val, ' ') || str_contains($val, '#') || str_contains($val, '$') || str_contains($val, '"') || $val === '') {
            $escaped = str_replace('"', '\"', $val);
            return "\"{$escaped}\"";
        }

        return $val;
    }

    public function testDatabaseConnection(array $config): array
    {
        $driver = $config['driver'] ?? 'mysql';

        try {
            if ($driver === 'sqlite') {
                $database = $config['database'] ?? database_path('database.sqlite');
                if (!file_exists($database) && $database !== ':memory:') {
                    $dir = dirname($database);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    touch($database);
                }
                $pdo = new PDO("sqlite:{$database}");
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return [
                    'success' => true,
                    'message' => 'SQLite connection verified successfully.',
                ];
            }

            $host = $config['host'] ?? '127.0.0.1';
            $port = $config['port'] ?? ($driver === 'pgsql' ? 5432 : 3306);
            $database = $config['database'] ?? '';
            $username = $config['username'] ?? 'root';
            $password = $config['password'] ?? '';

            if ($driver === 'pgsql') {
                $dsn = "pgsql:host={$host};port={$port};dbname={$database}";
                $pdo = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 5,
                ]);
                return [
                    'success' => true,
                    'message' => 'PostgreSQL connection verified successfully.',
                ];
            }

            // MySQL
            try {
                $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
                $pdo = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_TIMEOUT => 5,
                ]);

                $version = $pdo->query('select version()')->fetchColumn();

                return [
                    'success' => true,
                    'message' => "MySQL connected successfully (Server: {$version}).",
                ];
            } catch (Exception $e) {
                // If the error code indicates database doesn't exist (1049)
                if (str_contains($e->getMessage(), 'Unknown database') || $e->getCode() == 1049) {
                    return [
                        'success'            => false,
                        'can_create_database'=> true,
                        'database'           => $database,
                        'message'            => "Database '{$database}' does not exist on this MySQL server.",
                        'detail'             => 'You can click "Create Database Automatically" to have the installer create it for you.',
                    ];
                }
                throw $e;
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }
    }

    public function createDatabase(array $config): array
    {
        $driver = $config['driver'] ?? 'mysql';
        if ($driver !== 'mysql') {
            return [
                'success' => false,
                'message' => 'Auto-creation is currently only supported for MySQL.',
            ];
        }

        $host = $config['host'] ?? '127.0.0.1';
        $port = $config['port'] ?? 3306;
        $database = $config['database'] ?? '';
        $username = $config['username'] ?? 'root';
        $password = $config['password'] ?? '';

        if (empty($database)) {
            return ['success' => false, 'message' => 'Database name is required.'];
        }

        // Validate database name characters to prevent SQL injection
        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $database)) {
            return ['success' => false, 'message' => 'Invalid database name characters.'];
        }

        try {
            $dsn = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);

            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            return [
                'success' => true,
                'message' => "Database '{$database}' created successfully!",
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to create database: ' . $e->getMessage(),
            ];
        }
    }
}

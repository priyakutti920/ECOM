<?php

namespace App\Services\Install;

class RequirementsChecker
{
    protected string $minPhpVersion = '8.2.0';

    protected array $requiredExtensions = [
        'bcmath'    => 'BCMath arbitrary precision mathematics',
        'ctype'     => 'Ctype character classification',
        'curl'      => 'cURL HTTP client support',
        'fileinfo'  => 'File Information detection',
        'json'      => 'JSON serialization and parsing',
        'mbstring'  => 'Multibyte String processing',
        'openssl'   => 'OpenSSL cryptographic functions',
        'pdo'       => 'PHP Data Objects (PDO)',
        'tokenizer' => 'Tokenizer source code parsing',
        'xml'       => 'XML document parsing',
    ];

    protected array $recommendedExtensions = [
        'gd'        => 'GD Library for image resizing and thumbnail generation',
        'zip'       => 'ZIP archive handling for data exports',
        'intl'      => 'Internationalization extension for currencies and dates',
    ];

    protected array $pdoDrivers = [
        'pdo_mysql'  => 'MySQL Database Driver',
        'pdo_sqlite' => 'SQLite Database Driver',
        'pdo_pgsql'  => 'PostgreSQL Database Driver',
    ];

    public function checkPhpVersion(): array
    {
        $current = PHP_VERSION;
        $passed = version_compare($current, $this->minPhpVersion, '>=');

        return [
            'current'  => $current,
            'minimum'  => $this->minPhpVersion,
            'passed'   => $passed,
            'message'  => $passed
                ? "PHP {$current} meets the minimum requirement (>= {$this->minPhpVersion})"
                : "PHP {$current} is too old. Laravel 12 requires PHP >= {$this->minPhpVersion}",
        ];
    }

    public function checkExtensions(): array
    {
        $results = [];

        foreach ($this->requiredExtensions as $ext => $description) {
            $loaded = extension_loaded($ext);
            $results[$ext] = [
                'name'        => $ext,
                'description' => $description,
                'required'    => true,
                'passed'      => $loaded,
            ];
        }

        foreach ($this->recommendedExtensions as $ext => $description) {
            $loaded = extension_loaded($ext);
            $results[$ext] = [
                'name'        => $ext,
                'description' => $description,
                'required'    => false,
                'passed'      => $loaded,
            ];
        }

        return $results;
    }

    public function checkPdoDrivers(): array
    {
        $results = [];
        $hasAny = false;

        foreach ($this->pdoDrivers as $driver => $label) {
            $loaded = extension_loaded($driver);
            if ($loaded) {
                $hasAny = true;
            }
            $results[$driver] = [
                'name'   => $driver,
                'label'  => $label,
                'passed' => $loaded,
            ];
        }

        return [
            'drivers' => $results,
            'has_any' => $hasAny,
        ];
    }

    public function checkPermissions(): array
    {
        $directories = [
            'storage'           => storage_path(),
            'storage/app'       => storage_path('app'),
            'storage/framework' => storage_path('framework'),
            'storage/logs'      => storage_path('logs'),
            'bootstrap/cache'   => base_path('bootstrap/cache'),
            '.env'              => file_exists(base_path('.env')) ? base_path('.env') : base_path(),
        ];

        $results = [];

        foreach ($directories as $name => $path) {
            $exists = file_exists($path);
            $isWritable = $exists ? is_writable($path) : is_writable(dirname($path));

            $results[$name] = [
                'name'     => $name,
                'path'     => $path,
                'exists'   => $exists,
                'writable' => $isWritable,
            ];
        }

        return $results;
    }

    public function meetsAllCriticalRequirements(): bool
    {
        $php = $this->checkPhpVersion();
        if (!$php['passed']) {
            return false;
        }

        $extensions = $this->checkExtensions();
        foreach ($extensions as $ext) {
            if ($ext['required'] && !$ext['passed']) {
                return false;
            }
        }

        $pdo = $this->checkPdoDrivers();
        if (!$pdo['has_any']) {
            return false;
        }

        $permissions = $this->checkPermissions();
        foreach ($permissions as $perm) {
            if (!$perm['writable']) {
                return false;
            }
        }

        return true;
    }
}

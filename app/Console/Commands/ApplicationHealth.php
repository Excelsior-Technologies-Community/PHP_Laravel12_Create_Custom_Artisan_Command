<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class ApplicationHealth extends Command
{
    protected $signature = 'app:health
                            {--json : Display results as JSON}
                            {--detailed : Show additional diagnostic information}';

    protected $description = 'Check the health and configuration of the Laravel application';

    public function handle(): int
    {
        $checks = [];

        // PHP Version
        $phpVersion = PHP_VERSION;
        $checks[] = [
            'Check' => 'PHP Version',
            'Status' => version_compare($phpVersion, '8.2.0', '>=') ? 'PASS' : 'FAIL',
            'Details' => $phpVersion,
        ];

        // Laravel Version
        $laravelVersion = app()->version();

        $checks[] = [
            'Check' => 'Laravel Version',
            'Status' => 'PASS',
            'Details' => $laravelVersion,
        ];

        // Environment
        $checks[] = [
            'Check' => 'Environment',
            'Status' => 'PASS',
            'Details' => app()->environment(),
        ];

        // Debug Mode
        $checks[] = [
            'Check' => 'Debug Mode',
            'Status' => Config::get('app.debug') ? 'WARNING' : 'PASS',
            'Details' => Config::get('app.debug') ? 'Enabled' : 'Disabled',
        ];

        // Database
        try {
            DB::connection()->getPdo();

            $checks[] = [
                'Check' => 'Database',
                'Status' => 'PASS',
                'Details' => Config::get('database.default'),
            ];
        } catch (\Throwable $e) {
            $checks[] = [
                'Check' => 'Database',
                'Status' => 'FAIL',
                'Details' => $e->getMessage(),
            ];
        }

        // Cache
        try {
            $key = 'app_health_check_' . uniqid();
            Cache::put($key, 'ok', 10);
            $cacheWorking = Cache::get($key) === 'ok';
            Cache::forget($key);

            $checks[] = [
                'Check' => 'Cache',
                'Status' => $cacheWorking ? 'PASS' : 'FAIL',
                'Details' => Config::get('cache.default'),
            ];
        } catch (\Throwable $e) {
            $checks[] = [
                'Check' => 'Cache',
                'Status' => 'FAIL',
                'Details' => $e->getMessage(),
            ];
        }

        // Storage
        try {
            $testFile = 'health-check-' . uniqid() . '.txt';

            Storage::put($testFile, 'Laravel health check');
            $storageWorking = Storage::exists($testFile);
            Storage::delete($testFile);

            $checks[] = [
                'Check' => 'Storage',
                'Status' => $storageWorking ? 'PASS' : 'FAIL',
                'Details' => Config::get('filesystems.default'),
            ];
        } catch (\Throwable $e) {
            $checks[] = [
                'Check' => 'Storage',
                'Status' => 'FAIL',
                'Details' => $e->getMessage(),
            ];
        }

        // Storage Directory
        $storagePath = storage_path();

        $checks[] = [
            'Check' => 'Storage Writable',
            'Status' => is_writable($storagePath) ? 'PASS' : 'FAIL',
            'Details' => $storagePath,
        ];

        // Cache Directory
        $cachePath = storage_path('framework/cache');

        $checks[] = [
            'Check' => 'Cache Directory',
            'Status' => is_dir($cachePath) && is_writable($cachePath) ? 'PASS' : 'FAIL',
            'Details' => $cachePath,
        ];

        // Bootstrap Cache Directory
        $bootstrapCachePath = base_path('bootstrap/cache');

        $checks[] = [
            'Check' => 'Bootstrap Cache',
            'Status' => is_dir($bootstrapCachePath) && is_writable($bootstrapCachePath) ? 'PASS' : 'FAIL',
            'Details' => $bootstrapCachePath,
        ];

        // Migration Status
        try {
            $migrationTableExists = DB::getSchemaBuilder()->hasTable('migrations');

            if ($migrationTableExists) {
                $totalMigrations = DB::table('migrations')->count();

                $checks[] = [
                    'Check' => 'Migrations',
                    'Status' => 'PASS',
                    'Details' => "{$totalMigrations} migrations recorded",
                ];
            } else {
                $checks[] = [
                    'Check' => 'Migrations',
                    'Status' => 'WARNING',
                    'Details' => 'Migrations table does not exist',
                ];
            }
        } catch (\Throwable $e) {
            $checks[] = [
                'Check' => 'Migrations',
                'Status' => 'FAIL',
                'Details' => $e->getMessage(),
            ];
        }

        // Queue
        $checks[] = [
            'Check' => 'Queue',
            'Status' => 'PASS',
            'Details' => Config::get('queue.default'),
        ];

        // Session
        $checks[] = [
            'Check' => 'Session',
            'Status' => 'PASS',
            'Details' => Config::get('session.driver'),
        ];

        if ($this->option('json')) {
            $this->line(json_encode([
                'status' => $this->overallStatus($checks),
                'checks' => $checks,
            ], JSON_PRETTY_PRINT));

            return $this->overallStatus($checks) === 'HEALTHY'
                ? Command::SUCCESS
                : Command::FAILURE;
        }

        $this->displayHeader();

        $rows = [];

        foreach ($checks as $check) {
            $status = match ($check['Status']) {
                'PASS' => '✅ PASS',
                'WARNING' => '⚠️ WARNING',
                'FAIL' => '❌ FAIL',
                default => $check['Status'],
            };

            $rows[] = [
                $check['Check'],
                $status,
                $check['Details'],
            ];
        }

        $this->table(
            ['Check', 'Status', 'Details'],
            $rows
        );

        $this->newLine();

        $overallStatus = $this->overallStatus($checks);

        if ($overallStatus === 'HEALTHY') {
            $this->info('🟢 Overall Status: HEALTHY');
        } elseif ($overallStatus === 'WARNING') {
            $this->warn('🟡 Overall Status: WARNING');
        } else {
            $this->error('🔴 Overall Status: UNHEALTHY');
        }

        if ($this->option('detailed')) {
            $this->newLine();
            $this->info('📋 Detailed Application Information');

            $this->table([
                'Property',
                'Value',
            ], [
                ['PHP', PHP_VERSION],
                ['Laravel', app()->version()],
                ['Environment', app()->environment()],
                ['Application URL', Config::get('app.url')],
                ['Database', Config::get('database.default')],
                ['Cache Driver', Config::get('cache.default')],
                ['Queue Driver', Config::get('queue.default')],
                ['Session Driver', Config::get('session.driver')],
                ['Filesystem', Config::get('filesystems.default')],
                ['Timezone', Config::get('app.timezone')],
            ]);
        }

return $this->overallStatus($checks) === 'UNHEALTHY'
    ? Command::FAILURE
    : Command::SUCCESS;
    }

    private function displayHeader(): void
    {
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║       🩺 Application Health Check       ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->newLine();
    }

    private function overallStatus(array $checks): string
    {
        foreach ($checks as $check) {
            if ($check['Status'] === 'FAIL') {
                return 'UNHEALTHY';
            }
        }

        foreach ($checks as $check) {
            if ($check['Status'] === 'WARNING') {
                return 'WARNING';
            }
        }

        return 'HEALTHY';
    }
}
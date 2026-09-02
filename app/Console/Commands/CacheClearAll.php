<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class CacheClearAll extends Command
{
    protected $signature = 'cache:clear-all
                            {--force : Skip confirmation prompt}
                            {--only= : Clear only specific caches (comma-separated: application,config,route,view,event)}';

    protected $description = 'Clear all application caches';

    public function handle(): int
    {
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║   🧹 Cache Clear All Command            ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->newLine();

        $only = $this->option('only');
        $force = $this->option('force');

        $availableCaches = ['application', 'config', 'route', 'view', 'event'];

        $cachesToClear = $only
            ? array_values(array_intersect(explode(',', $only), $availableCaches))
            : $availableCaches;

        if (empty($cachesToClear)) {
            $this->warn('⚠️  No valid cache types specified.');
            $this->line('💡 Available options: application, config, route, view, event');
            return Command::SUCCESS;
        }

        if (!$force && !$this->confirm("Are you sure you want to clear the following caches?\n" . implode(', ', $cachesToClear), false)) {
            $this->info('🚫 Operation cancelled.');
            return Command::SUCCESS;
        }

        $results = [];

        $commandMap = [
            'application' => 'cache:clear',
            'config' => 'config:clear',
            'route' => 'route:clear',
            'view' => 'view:clear',
            'event' => 'event:clear',
        ];

        foreach ($cachesToClear as $cache) {
            try {
                $this->line("🧹 Clearing {$cache} cache...");
                $commandName = $commandMap[$cache] ?? "cache:{$cache}-clear";
                Artisan::call($commandName);
                $results[] = [$cache, '✅ Cleared'];
            } catch (\Throwable $e) {
                $results[] = [$cache, '❌ Failed: ' . $e->getMessage()];
            }
        }

        $this->newLine();
        $this->info('📊 Cache Clear Summary');
        $this->table(['Cache Type', 'Status'], $results);

        return Command::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseStats extends Command
{
    protected $signature = 'db:stats
                            {--users : Show detailed user statistics}
                            {--today : Show today\'s statistics}
                            {--json : Return statistics as JSON}';

    protected $description = 'Display database and user statistics';

    public function handle(): int
    {
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $this->error('❌ Database connection failed.');
            $this->error($e->getMessage());

            return Command::FAILURE;
        }

        $this->displayHeader();

        $statistics = [];

        // Total users
        if (Schema::hasTable('users')) {
            $totalUsers = User::count();

            $adminUsers = User::where('role', 'admin')->count();

            $moderatorUsers = User::where('role', 'moderator')->count();

            $normalUsers = User::where(function ($query) {
                $query->where('role', 'user')
                    ->orWhereNull('role');
            })->count();

            $verifiedUsers = User::whereNotNull('email_verified_at')->count();

            $unverifiedUsers = User::whereNull('email_verified_at')->count();

            $todayUsers = User::whereDate('created_at', today())->count();

            $weekUsers = User::whereBetween(
                'created_at',
                [
                    now()->startOfWeek(),
                    now()->endOfWeek(),
                ]
            )->count();

            $monthUsers = User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            $statistics = [
                'total_users' => $totalUsers,
                'admin_users' => $adminUsers,
                'moderator_users' => $moderatorUsers,
                'normal_users' => $normalUsers,
                'verified_users' => $verifiedUsers,
                'unverified_users' => $unverifiedUsers,
                'today_users' => $todayUsers,
                'week_users' => $weekUsers,
                'month_users' => $monthUsers,
            ];
        }

        // JSON output
        if ($this->option('json')) {
            $this->line(json_encode([
                'database' => config('database.default'),
                'generated_at' => now()->toDateTimeString(),
                'statistics' => $statistics,
            ], JSON_PRETTY_PRINT));

            return Command::SUCCESS;
        }

        // Database information
        $this->info('🗄️ Database Information');

        $this->table([
            'Property',
            'Value',
        ], [
            ['Connection', config('database.default')],
            ['Driver', config('database.connections.' . config('database.default') . '.driver')],
            ['Host', config('database.connections.' . config('database.default') . '.host')],
            ['Database', config('database.connections.' . config('database.default') . '.database')],
        ]);

        $this->newLine();

        // User statistics
        if (!empty($statistics)) {
            $this->info('👥 User Statistics');

            $this->table([
                'Metric',
                'Count',
            ], [
                ['Total Users', $statistics['total_users']],
                ['Admin Users', $statistics['admin_users']],
                ['Moderator Users', $statistics['moderator_users']],
                ['Normal Users', $statistics['normal_users']],
                ['Verified Users', $statistics['verified_users']],
                ['Unverified Users', $statistics['unverified_users']],
            ]);

            if ($this->option('users')) {
                $this->newLine();

                $this->info('🔐 User Verification Breakdown');

                $verifiedPercentage = $statistics['total_users'] > 0
                    ? round(
                        ($statistics['verified_users'] / $statistics['total_users']) * 100,
                        2
                    )
                    : 0;

                $unverifiedPercentage = $statistics['total_users'] > 0
                    ? round(
                        ($statistics['unverified_users'] / $statistics['total_users']) * 100,
                        2
                    )
                    : 0;

                $this->table([
                    'Status',
                    'Count',
                    'Percentage',
                ], [
                    [
                        'Verified',
                        $statistics['verified_users'],
                        $verifiedPercentage . '%',
                    ],
                    [
                        'Unverified',
                        $statistics['unverified_users'],
                        $unverifiedPercentage . '%',
                    ],
                ]);
            }

            if ($this->option('today')) {
                $this->newLine();

                $this->info('📅 User Registration Statistics');

                $this->table([
                    'Period',
                    'New Users',
                ], [
                    ['Today', $statistics['today_users']],
                    ['This Week', $statistics['week_users']],
                    ['This Month', $statistics['month_users']],
                ]);
            }
        }

        // Tables
        try {
            $tables = $this->getDatabaseTables();

            $this->newLine();
            $this->info('📋 Database Tables');

            $this->table([
                'Table',
            ], array_map(
                fn ($table) => [$table],
                $tables
            ));

            $this->line(
                '📊 Total Tables: <info>' . count($tables) . '</info>'
            );
        } catch (\Throwable $e) {
            $this->warn('⚠️ Unable to read database table information.');
        }

        $this->newLine();
        $this->info('✅ Database statistics generated successfully.');

        return Command::SUCCESS;
    }

    private function displayHeader(): void
    {
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║       📊 Database Statistics            ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->newLine();
    }

    private function getDatabaseTables(): array
    {
        $connection = DB::connection();
        $driver = $connection->getDriverName();

        return match ($driver) {
            'mysql' => collect(
                DB::select('SHOW TABLES')
            )->map(function ($table) {
                return array_values((array) $table)[0];
            })->toArray(),

            'sqlite' => collect(
                DB::select(
                    "SELECT name FROM sqlite_master
                     WHERE type = 'table'
                     AND name NOT LIKE 'sqlite_%'"
                )
            )->pluck('name')->toArray(),

            'pgsql' => collect(
                DB::select(
                    "SELECT tablename
                     FROM pg_catalog.pg_tables
                     WHERE schemaname = 'public'"
                )
            )->pluck('tablename')->toArray(),

            default => [],
        };
    }
}
<?php

namespace App\Console\Commands;

use App\Models\CommandLog;
use Illuminate\Console\Command;

class CommandCleanup extends Command
{
    protected $signature = 'command:cleanup
                            {--days=30 : Delete records older than this number of days}
                            {--force : Skip confirmation}';

    protected $description = 'Delete old Artisan command history';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        if ($days < 1) {
            $this->error(
                '❌ Days must be at least 1.'
            );

            return Command::FAILURE;
        }

        $query = CommandLog::where(
            'executed_at',
            '<',
            now()->subDays($days)
        );

        $count = $query->count();

        if ($count === 0) {
            $this->info(
                "✅ No records older than {$days} days."
            );

            return Command::SUCCESS;
        }

        $this->warn(
            "⚠️ {$count} record(s) are older than {$days} days."
        );

        if (
            !$this->option('force') &&
            !$this->confirm(
                'Do you want to delete them?',
                false
            )
        ) {
            $this->info(
                '🚫 Operation cancelled.'
            );

            return Command::SUCCESS;
        }

        $query->delete();

        $this->info(
            "✅ Deleted {$count} old command history record(s)."
        );

        return Command::SUCCESS;
    }
}

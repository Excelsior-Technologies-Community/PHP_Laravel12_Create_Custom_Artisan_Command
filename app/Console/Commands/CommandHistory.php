<?php

namespace App\Console\Commands;

use App\Models\CommandLog;
use Illuminate\Console\Command;

class CommandHistory extends Command
{
    protected $signature = 'command:history
                            {--failed : Show only failed commands}
                            {--command= : Filter by command name}
                            {--limit=20 : Number of records to display}
                            {--clear : Delete command execution history}';

    protected $description = 'Display Artisan command execution history';

    public function handle(): int
    {
        if ($this->option('clear')) {
            return $this->clearHistory();
        }

        $limit = (int) $this->option('limit');

        if ($limit < 1) {
            $this->error('❌ Limit must be at least 1.');

            return Command::FAILURE;
        }

        $query = CommandLog::query()
            ->orderByDesc('executed_at');

        if ($this->option('failed')) {
            $query->where('status', 'failed');
        }

        if ($this->option('command')) {
            $query->where('command', $this->option('command'));
        }

        $logs = $query->limit($limit)->get();

        $this->displayHeader();

        if ($logs->isEmpty()) {
            $this->warn('⚠️ No command execution history found.');

            return Command::SUCCESS;
        }

        $rows = [];

        foreach ($logs as $log) {
            $rows[] = [
                $log->command,
                $log->status === 'success'
                    ? '✅ SUCCESS'
                    : '❌ FAILED',
                $log->exit_code,
                $log->duration !== null
                    ? $log->duration . 's'
                    : '-',
                $log->executed_at
                    ? $log->executed_at->format('Y-m-d H:i:s')
                    : '-',
            ];
        }

        $this->table(
            [
                'Command',
                'Status',
                'Exit Code',
                'Duration',
                'Executed At',
            ],
            $rows
        );

        $this->newLine();

        $this->info(
            '📊 Showing <info>' .
            $logs->count() .
            '</info> command execution(s).'
        );

        return Command::SUCCESS;
    }

    private function clearHistory(): int
    {
        $count = CommandLog::count();

        if ($count === 0) {
            $this->warn('⚠️ No command history to delete.');

            return Command::SUCCESS;
        }

        if (!$this->confirm(
            "Are you sure you want to delete {$count} command history records?",
            false
        )) {
            $this->info('🚫 Operation cancelled.');

            return Command::SUCCESS;
        }

        CommandLog::query()->delete();

        $this->info(
            "✅ Deleted {$count} command history records."
        );

        return Command::SUCCESS;
    }

    private function displayHeader(): void
    {
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║       📜 Artisan Command History       ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->newLine();
    }
}
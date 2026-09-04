<?php

namespace App\Console\Commands;

use App\Models\CommandLog;
use Illuminate\Console\Command;

class CommandStats extends Command
{
    protected $signature = 'command:stats';

    protected $description = 'Display Artisan command execution statistics';

    public function handle(): int
    {
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║       📊 Artisan Command Statistics      ║');
        $this->info('╚══════════════════════════════════════════╝');

        $this->newLine();

        $total = CommandLog::count();

        $successful = CommandLog::where(
            'status',
            'success'
        )->count();

        $failed = CommandLog::where(
            'status',
            'failed'
        )->count();

        $today = CommandLog::whereDate(
            'executed_at',
            today()
        )->count();

        $averageDuration = CommandLog::avg('duration');

        $successRate = $total > 0
            ? round(($successful / $total) * 100, 2)
            : 0;

        $this->table(
            [
                'Metric',
                'Value',
            ],
            [
                [
                    'Total Executions',
                    $total,
                ],
                [
                    'Successful',
                    $successful,
                ],
                [
                    'Failed',
                    $failed,
                ],
                [
                    'Today',
                    $today,
                ],
                [
                    'Success Rate',
                    $successRate . '%',
                ],
                [
                    'Average Duration',
                    $averageDuration !== null
                        ? round($averageDuration, 3) . 's'
                        : '-',
                ],
            ]
        );

        $this->newLine();

        $this->info('🏆 Most Used Commands');

        $commands = CommandLog::selectRaw(
            'command, COUNT(*) as total'
        )
            ->groupBy('command')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        if ($commands->isEmpty()) {
            $this->warn('No command statistics available.');

            return Command::SUCCESS;
        }

        $rows = [];

        foreach ($commands as $item) {
            $rows[] = [
                $item->command,
                $item->total,
            ];
        }

        $this->table(
            [
                'Command',
                'Executions',
            ],
            $rows
        );

        return Command::SUCCESS;
    }
}

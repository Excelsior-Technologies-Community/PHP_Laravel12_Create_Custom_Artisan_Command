<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Artisan;

class ScheduleList extends Command
{
    protected $signature = 'schedule:list';

    protected $description = 'List all scheduled events';

    public function handle(Schedule $schedule): int
    {
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║   📅 Scheduled Events List              ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->newLine();

        $events = $schedule->events();

        if (empty($events)) {
            $this->warn('⚠️  No scheduled events found.');
            $this->line('💡 Add scheduled events in bootstrap/app.php using withSchedule()');
            return Command::SUCCESS;
        }

        $rows = [];

        foreach ($events as $event) {
            $rows[] = [
                $event->command ?: class_basename($event),
                $event->expression,
                $event->description ?: 'No description',
            ];
        }

        $this->info('📋 Scheduled Events');
        $this->table(['Event / Command', 'Expression (Cron)', 'Description'], $rows);
        $this->newLine();
        $this->line("📊 Total events: <info>" . count($events) . "</info>");

        return Command::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DatabaseCleanupBackups extends Command
{
    protected $signature = 'db:cleanup-backups
                            {--days=7 : Delete backups older than this number of days}
                            {--path=database/backups : Backup directory}
                            {--force : Skip confirmation}';

    protected $description = 'Delete old database backup files';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        if ($days < 1) {
            $this->error(
                '❌ Days must be at least 1.'
            );

            return Command::FAILURE;
        }

        $directory = base_path(
            $this->option('path')
        );

        if (!File::exists($directory)) {
            $this->warn(
                '⚠️ Backup directory does not exist.'
            );

            return Command::SUCCESS;
        }

        $files = File::files($directory);

        $oldFiles = [];

        $cutoff = now()->subDays($days)->timestamp;

        foreach ($files as $file) {
            if ($file->getMTime() < $cutoff) {
                $oldFiles[] = $file;
            }
        }

        if (empty($oldFiles)) {
            $this->info(
                "✅ No backups older than {$days} days."
            );

            return Command::SUCCESS;
        }

        $this->warn(
            '⚠️ Old backups found: ' .
                count($oldFiles)
        );

        if (
            !$this->option('force') &&
            !$this->confirm(
                'Delete these backup files?',
                false
            )
        ) {
            $this->info(
                '🚫 Operation cancelled.'
            );

            return Command::SUCCESS;
        }

        $deleted = 0;

        foreach ($oldFiles as $file) {
            if (File::delete($file->getPathname())) {
                $deleted++;

                $this->line(
                    '🗑️ Deleted: ' . $file->getFilename()
                );
            }
        }

        $this->newLine();

        $this->info(
            "✅ Deleted {$deleted} old backup(s)."
        );

        return Command::SUCCESS;
    }
}

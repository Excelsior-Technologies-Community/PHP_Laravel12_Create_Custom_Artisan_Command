<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class DatabaseBackups extends Command
{
    protected $signature = 'db:backups
                            {--path=database/backups : Backup directory}';

    protected $description = 'List available database backups';

    public function handle(): int
    {
        $directory = base_path(
            $this->option('path')
        );

        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║        💾 Database Backups               ║');
        $this->info('╚══════════════════════════════════════════╝');

        $this->newLine();

        if (!File::exists($directory)) {
            $this->warn(
                '⚠️ Backup directory does not exist.'
            );

            return Command::SUCCESS;
        }

        $files = File::files($directory);

        if (empty($files)) {
            $this->warn(
                '⚠️ No database backups found.'
            );

            return Command::SUCCESS;
        }

        $rows = [];

        foreach ($files as $file) {
            $size = $file->getSize();

            $rows[] = [
                $file->getFilename(),
                $this->formatBytes($size),
                date(
                    'Y-m-d H:i:s',
                    $file->getMTime()
                ),
            ];
        }

        usort(
            $rows,
            fn($a, $b) => strcmp($b[2], $a[2])
        );

        $this->table(
            [
                'Filename',
                'Size',
                'Created',
            ],
            $rows
        );

        $this->newLine();

        $this->info(
            '📊 Total backups: ' . count($files)
        );

        return Command::SUCCESS;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 2) . ' KB';
        }

        if ($bytes < 1024 * 1024 * 1024) {
            return round(
                $bytes / (1024 * 1024),
                2
            ) . ' MB';
        }

        return round(
            $bytes / (1024 * 1024 * 1024),
            2
        ) . ' GB';
    }
}

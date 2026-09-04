<?php

namespace App\Console\Commands;

use App\Models\CommandLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CommandExport extends Command
{
    protected $signature = 'command:export
                            {--path=storage/app : Export directory}
                            {--filename=command-history.csv : CSV filename}';

    protected $description = 'Export Artisan command history to CSV';

    public function handle(): int
    {
        $directory = base_path(
            $this->option('path')
        );

        $filename = $this->option('filename');

        if (!File::exists($directory)) {
            File::makeDirectory(
                $directory,
                0777,
                true
            );
        }

        $filePath = $directory . DIRECTORY_SEPARATOR . $filename;

        $logs = CommandLog::orderByDesc(
            'executed_at'
        )->get();

        $file = fopen($filePath, 'w');

        if (!$file) {
            $this->error(
                '❌ Unable to create CSV file.'
            );

            return Command::FAILURE;
        }

        fputcsv($file, [
            'ID',
            'Command',
            'Arguments',
            'Options',
            'Exit Code',
            'Status',
            'Duration',
            'Executed At',
        ]);

        foreach ($logs as $log) {
            fputcsv($file, [
                $log->id,
                $log->command,
                json_encode($log->arguments),
                json_encode($log->options),
                $log->exit_code,
                $log->status,
                $log->duration,
                $log->executed_at,
            ]);
        }

        fclose($file);

        $this->info(
            '✅ Command history exported successfully.'
        );

        $this->line(
            "📂 File: {$filePath}"
        );

        $this->line(
            "📊 Records: {$logs->count()}"
        );

        return Command::SUCCESS;
    }
}

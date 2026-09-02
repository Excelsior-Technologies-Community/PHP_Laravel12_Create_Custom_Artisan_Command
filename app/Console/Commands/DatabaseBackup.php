<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class DatabaseBackup extends Command
{
    protected $signature = 'db:backup
                            {--path=database/backups : Backup directory}
                            {--filename= : Custom backup filename}';

    protected $description = 'Backup the database';

    public function handle(): int
    {
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║   💾 Database Backup Command            ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->newLine();

        $connection = Config::get('database.default');
        $driver = Config::get("database.connections.{$connection}.driver");

        $backupDir = $this->option('path');
        $customFilename = $this->option('filename');

        $this->line("🔌 Database Connection: <info>{$connection}</info>");
        $this->line("🛠️  Driver: <info>{$driver}</info>");
        $this->newLine();

        if (!is_dir($backupDir)) {
            $this->line("📁 Creating backup directory: <info>{$backupDir}</info>");
            if (!mkdir($backupDir, 0777, true) && !is_dir($backupDir)) {
                $this->error("❌ Unable to create backup directory: {$backupDir}");
                return Command::FAILURE;
            }
            $this->info("✅ Directory created successfully.");
            $this->newLine();
        } else {
            $this->line("📁 Backup directory: <info>{$backupDir}</info>");
        }

        $filename = $customFilename ?: date('Y-m-d_H-i-s') . '_' . $connection . '.sql';
        $backupPath = rtrim($backupDir, '/') . '/' . $filename;

        $this->info("🔄 Backing up database [{$connection}]...");
        $this->newLine();

        try {
            $this->withProgressBar(100, function () use ($driver, $connection, $backupPath) {
                if ($driver === 'sqlite') {
                    $dbPath = Config::get("database.connections.{$connection}.database");
                    if ($dbPath === ':memory:' || !file_exists($dbPath)) {
                        throw new \RuntimeException("SQLite database not found or in-memory: {$dbPath}");
                    }
                    copy($dbPath, $backupPath);
                } elseif ($driver === 'mysql') {
                    $host = Config::get("database.connections.{$connection}.host");
                    $port = Config::get("database.connections.{$connection}.port");
                    $database = Config::get("database.connections.{$connection}.database");
                    $username = Config::get("database.connections.{$connection}.username");
                    $password = Config::get("database.connections.{$connection}.password");

                    $command = sprintf(
                        'mysqldump --host=%s --port=%s --user=%s %s > %s 2>&1',
                        escapeshellarg($host),
                        escapeshellarg($port),
                        escapeshellarg($username),
                        escapeshellarg($database),
                        escapeshellarg($backupPath)
                    );

                    if ($password) {
                        $command = sprintf(
                            'mysqldump --host=%s --port=%s --user=%s --password=%s %s > %s 2>&1',
                            escapeshellarg($host),
                            escapeshellarg($port),
                            escapeshellarg($username),
                            escapeshellarg($password),
                            escapeshellarg($database),
                            escapeshellarg($backupPath)
                        );
                    }

                    exec($command, $output, $returnVar);

                    if ($returnVar !== 0) {
                        throw new \RuntimeException("mysqldump failed with exit code {$returnVar}");
                    }
                } elseif ($driver === 'pgsql') {
                    $host = Config::get("database.connections.{$connection}.host");
                    $port = Config::get("database.connections.{$connection}.port");
                    $database = Config::get("database.connections.{$connection}.database");
                    $username = Config::get("database.connections.{$connection}.username");
                    $password = Config::get("database.connections.{$connection}.password");

                    $command = sprintf(
                        'PGPASSWORD=%s pg_dump --host=%s --port=%s --username=%s %s > %s 2>&1',
                        escapeshellarg($password),
                        escapeshellarg($host),
                        escapeshellarg($port),
                        escapeshellarg($username),
                        escapeshellarg($database),
                        escapeshellarg($backupPath)
                    );

                    exec($command, $output, $returnVar);

                    if ($returnVar !== 0) {
                        throw new \RuntimeException("pg_dump failed with exit code {$returnVar}");
                    }
                } else {
                    throw new \RuntimeException("Unsupported database driver: {$driver}");
                }
            });
        } catch (\Throwable $e) {
            $this->newLine();
            $this->error("❌ Backup failed: " . $e->getMessage());
            return Command::FAILURE;
        }

        $this->newLine();
        $this->info("✅ Backup completed successfully!");
        $this->line("📂 Backup saved to: <info>{$backupPath}</info>");
        $this->line('💾 Your database is safe!');

        return Command::SUCCESS;
    }
}

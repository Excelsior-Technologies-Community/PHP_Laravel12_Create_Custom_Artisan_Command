<?php

namespace Tests\Feature\Commands;

use Tests\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatabaseBackupTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    /** @test */
    public function it_creates_sqlite_backup(): void
    {
        $dbFile = database_path('database.sqlite');
        if (!file_exists($dbFile)) {
            touch($dbFile);
        }

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', $dbFile);

        $backupPath = 'database/backups/test_sqlite_backup_' . uniqid() . '.sql';

        $this->artisan("db:backup --path=database/backups --filename=" . basename($backupPath))
            ->expectsOutputToContain('Backing up database')
            ->expectsOutputToContain('Backup completed successfully!')
            ->expectsOutputToContain('Backup saved to:')
            ->assertExitCode(0);

        $this->assertFileExists($backupPath);
        @unlink($backupPath);
    }

    /** @test */
    public function it_creates_backup_with_custom_path(): void
    {
        $dbFile = database_path('database.sqlite');
        if (!file_exists($dbFile)) {
            touch($dbFile);
        }

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', $dbFile);

        $customDir = 'storage/test_backups_' . uniqid();

        $this->artisan("db:backup --path={$customDir} --filename=test.sql")
            ->expectsOutputToContain('Backing up database')
            ->expectsOutputToContain('Backup completed successfully!')
            ->expectsOutputToContain('Backup saved to:')
            ->assertExitCode(0);

        $this->assertFileExists($customDir . '/test.sql');
        @unlink($customDir . '/test.sql');
        @rmdir($customDir);
    }

    /** @test */
    public function it_creates_backup_directory_if_not_exists(): void
    {
        $dbFile = database_path('database.sqlite');
        if (!file_exists($dbFile)) {
            touch($dbFile);
        }

        Config::set('database.default', 'sqlite');
        Config::set('database.connections.sqlite.database', $dbFile);

        $newDir = 'storage/test_backups_' . uniqid();
        $this->artisan("db:backup --path={$newDir}")
            ->expectsOutputToContain('Backing up database')
            ->expectsOutputToContain('Backup completed successfully!')
            ->assertExitCode(0);

        $this->assertDirectoryExists($newDir);
        $files = glob($newDir . '/*.sql');
        $this->assertGreaterThan(0, count($files));
        @unlink($files[0]);
        @rmdir($newDir);
    }
}

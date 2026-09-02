<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class DeleteUsers extends Command
{
    protected $signature = 'delete:users
                            {--email= : Delete user by email}
                            {--id= : Delete user by ID}
                            {--all : Delete all users}
                            {--force : Skip confirmation prompt}';

    protected $description = 'Delete users from the database';

    public function handle(): int
    {
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║   🗑️  Delete Users Command              ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->newLine();

        $email = $this->option('email');
        $id = $this->option('id');
        $all = $this->option('all');
        $force = $this->option('force');

        if (!$email && !$id && !$all) {
            $this->error('❌ Please specify --email=, --id=, or --all.');
            return Command::FAILURE;
        }

        if ($email && $id) {
            $this->error('❌ Cannot use --email and --id together.');
            return Command::FAILURE;
        }

        if ($all && ($email || $id)) {
            $this->error('❌ Cannot use --all with --email or --id.');
            return Command::FAILURE;
        }

        $deletedCount = 0;

        if ($email) {
            $this->line("🔍 Searching for user with email: <info>{$email}</info>");
            $user = User::where('email', $email)->first();
            if (!$user) {
                $this->warn("⚠️  No user found with email: {$email}");
                return Command::FAILURE;
            }
            $user->delete();
            $deletedCount = 1;
            $this->info("✅ Deleted user with email: {$email}");
        } elseif ($id) {
            $this->line("🔍 Searching for user with ID: <info>{$id}</info>");
            $user = User::find($id);
            if (!$user) {
                $this->error("❌ No user found with ID: {$id}");
                return Command::FAILURE;
            }
            $user->delete();
            $deletedCount = 1;
            $this->info("✅ Deleted user with ID: {$id}");
        } elseif ($all) {
            $count = User::count();
            if ($count === 0) {
                $this->warn('⚠️  No users to delete.');
                return Command::SUCCESS;
            }

            if (!$force && !$this->confirm("Are you sure you want to delete all {$count} users?", false)) {
                $this->info('🚫 Operation cancelled.');
                return Command::SUCCESS;
            }

            User::query()->delete();
            $deletedCount = $count;
            $this->error("🗑️  Deleted {$deletedCount} users.");
        }

        $this->newLine();
        $this->info("📊 Total deleted: {$deletedCount}");
        return Command::SUCCESS;
    }
}

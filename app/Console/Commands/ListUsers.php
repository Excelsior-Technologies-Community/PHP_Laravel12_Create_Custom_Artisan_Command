<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ListUsers extends Command
{
    protected $signature = 'list:users
                            {--per-page=15 : Number of users per page}
                            {--search= : Search users by name or email}
                            {--verified : Only show verified users}';

    protected $description = 'List all users in a paginated table';

    public function handle(): int
    {
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║   📋 List Users Command                 ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->newLine();

        $perPage = (int) $this->option('per-page');
        $search = $this->option('search');
        $verified = $this->option('verified');

        if ($perPage < 1) {
            $this->error('❌ Per-page must be at least 1.');
            return Command::FAILURE;
        }

        $query = User::query();

        if ($search) {
            $this->line("🔍 Searching for: <info>{$search}</info>");
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($verified) {
            $this->line('✅ Filtering verified users only');
            $query->whereNotNull('email_verified_at');
        }

        $total = $query->count();
        $page = 1;

        if ($total === 0) {
            $this->warn('⚠️  No users found.');
            return Command::SUCCESS;
        }

        $this->info("📊 Total Users: <info>{$total}</info>");
        $this->newLine();

        $users = $query->orderByDesc('id')->paginate($perPage);
        $totalPages = $users->lastPage();
        $currentPage = 1;

        do {
            $this->line("📄 Page {$currentPage} of {$totalPages}");
            $this->newLine();

            $rows = $users->items();
            $tableRows = [];

            foreach ($rows as $user) {
                $tableRows[] = [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->email_verified_at ? '✅ Yes' : '❌ No',
                    $user->created_at->format('Y-m-d H:i:s'),
                ];
            }

            $this->table(['ID', 'Name', 'Email', 'Verified', 'Created At'], $tableRows);

            $currentPage++;

            if ($currentPage <= $totalPages) {
                $this->newLine();
                if (defined('STDIN') && $this->input->isInteractive() && !$this->confirm('Show next page?', true)) {
                    break;
                }
                $users = $query->orderByDesc('id')->paginate($perPage, ['*'], 'page', $currentPage);
            }
        } while ($currentPage <= $totalPages);

        $this->newLine();
        $this->info('✅ Finished listing users.');
        return Command::SUCCESS;
    }
}

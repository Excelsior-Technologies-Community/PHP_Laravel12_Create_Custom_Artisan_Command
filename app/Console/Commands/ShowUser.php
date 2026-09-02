<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ShowUser extends Command
{
    protected $signature = 'user:info {id : The ID of the user}';

    protected $description = 'Show detailed information about a user';

    public function handle(): int
    {
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║   👤 User Information Command           ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->newLine();

        $id = $this->argument('id');
        $this->line("🔍 Looking up user with ID: <info>{$id}</info>");
        $user = User::find($id);

        if (!$user) {
            $this->error("❌ User with ID {$id} not found.");
            return Command::FAILURE;
        }

        $this->info('✅ User Found!');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line("🆔 ID            : <info>{$user->id}</info>");
        $this->line("👤 Name          : <info>{$user->name}</info>");
        $this->line("📧 Email         : <info>{$user->email}</info>");
        $this->line("✔️  Email Verified: <info>" . ($user->email_verified_at ? 'Yes (at ' . $user->email_verified_at->format('Y-m-d H:i:s') . ')' : 'No') . "</info>");
        $this->line("📅 Created At    : <info>{$user->created_at->format('Y-m-d H:i:s')}</info>");
        $this->line("🔄 Updated At    : <info>{$user->updated_at->format('Y-m-d H:i:s')}</info>");

        if ($user->role) {
            $this->line("🔑 Role          : <info>{$user->role}</info>");
        }

        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        return Command::SUCCESS;
    }
}

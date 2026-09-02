<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CreateUserInteractive extends Command
{
    protected $signature = 'create:user-interactive';

    protected $description = 'Interactive wizard to create a new user';

    public function handle(): int
    {
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║   🧙 Interactive User Creation Wizard   ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->newLine();

        $this->line('✨ Let\'s create a new user! Follow the prompts below.');
        $this->newLine();

        $name = $this->ask('👤 What is the user\'s name?');
        $email = $this->ask('📧 What is the user\'s email?');

        $role = $this->choice(
            '🔑 What role should this user have?',
            ['user', 'admin', 'moderator'],
            0
        );

        $verifyEmail = $this->confirm('✔️  Should the email be marked as verified?', true);

        $this->newLine();
        $this->line('🔒 Suggested passwords: "password123", "secret", "changeMe"');
        $password = $this->anticipate('Enter a password (or choose from suggestions):', ['password123', 'secret', 'changeMe', 'letmein']);

        $this->newLine();
        $this->info('📋 Summary');
        $this->line('──────────');

        $summary = [
            ['Field', 'Value'],
            ['Name', $name],
            ['Email', $email],
            ['Role', $role],
            ['Email Verified', $verifyEmail ? '✅ Yes' : '❌ No'],
            ['Password', str_repeat('*', strlen($password))],
        ];

        $this->table(['Field', 'Value'], $summary);

        if (!$this->confirm('Create this user?', true)) {
            $this->warn('🚫 User creation cancelled.');
            return Command::SUCCESS;
        }

        User::factory()->create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'email_verified_at' => $verifyEmail ? now() : null,
        ]);

        $this->newLine();
        $this->info("✅ User '{$name}' created successfully!");
        $this->line('💾 User has been saved to the database.');
        return Command::SUCCESS;
    }
}

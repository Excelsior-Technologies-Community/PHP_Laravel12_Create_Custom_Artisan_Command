<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CreateUsers extends Command
{
    protected $signature = 'create:users {count}
                            {--role= : Assign a role (admin, user, moderator)}
                            {--verified : Make email verified}
                            {--unverified : Make email unverified}
                            {--factory-state= : Pass a factory state method name}';

    protected $description = 'Create Dummy Users for your Application';

    public function handle(): int
    {
        $this->info('╔══════════════════════════════════════════╗');
        $this->info('║   👥 Create Dummy Users Command         ║');
        $this->info('╚══════════════════════════════════════════╝');
        $this->newLine();

        $numberOfUsers = (int) $this->argument('count');
        $role = $this->option('role');
        $verified = $this->option('verified');
        $unverified = $this->option('unverified');
        $factoryState = $this->option('factory-state');

        if ($numberOfUsers < 1) {
            $this->error('❌ Count must be at least 1.');
            return Command::FAILURE;
        }

        if ($numberOfUsers > 1000) {
            $this->warn("⚠️  Creating {$numberOfUsers} users. This may take a while...");
        }

        $options = [];
        if ($role) {
            $options[] = "Role: <info>{$role}</info>";
        }
        if ($verified) {
            $options[] = "Verified: <info>Yes</info>";
        }
        if ($unverified) {
            $options[] = "Verified: <info>No</info>";
        }
        if ($factoryState) {
            $options[] = "Factory State: <info>{$factoryState}</info>";
        }

        if (!empty($options)) {
            $this->line('📋 Options: ' . implode(' | ', $options));
            $this->newLine();
        }

        $this->info("✨ Creating {$numberOfUsers} users...");

        $bar = $this->output->createProgressBar($numberOfUsers);
        $bar->start();

        for ($i = 0; $i < $numberOfUsers; $i++) {
            $factory = User::factory();

            if ($role) {
                $factory = $factory->state(fn (array $attributes) => ['role' => $role]);
            }

            if ($verified) {
                $factory = $factory->state(fn (array $attributes) => ['email_verified_at' => now()]);
            } elseif ($unverified) {
                $factory = $factory->state(fn (array $attributes) => ['email_verified_at' => null]);
            }

            if ($factoryState) {
                try {
                    $factory = $factory->{$factoryState}();
                } catch (\Throwable) {
                    // ignore invalid state and continue
                }
            }

            $factory->create();
            $bar->advance();
        }

        $bar->finish();

        $this->newLine();
        $this->info("✅ {$numberOfUsers} Dummy Users Created Successfully!");
        $this->line('💾 Users have been saved to the database.');

        return Command::SUCCESS;
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CreateUsers extends Command
{
    /**
     * The name and signature of the Artisan command.
     * Example: php artisan create:users 10
     */
    protected $signature = 'create:users {count}';

    /**
     * The description of the command.
     */
    protected $description = 'Create Dummy Users for your Application';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get the number of users to create from the command argument
        $numberOfUsers = $this->argument('count');

        // Loop and create dummy users using the User Factory
        for ($i = 0; $i < $numberOfUsers; $i++) {
            User::factory()->create();
        }

        // Display success message in the console
        $this->info("$numberOfUsers Dummy Users Created Successfully!");

        return Command::SUCCESS;
    }
}

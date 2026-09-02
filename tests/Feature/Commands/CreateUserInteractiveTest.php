<?php

namespace Tests\Feature\Commands;

use Tests\TestCase;
use App\Models\User;

class CreateUserInteractiveTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    /** @test */
    public function it_creates_user_interactively(): void
    {
        $this->artisan('create:user-interactive')
            ->expectsQuestion('👤 What is the user\'s name?', 'Test User')
            ->expectsQuestion('📧 What is the user\'s email?', 'test@example.com')
            ->expectsQuestion('🔑 What role should this user have?', 'user')
            ->expectsConfirmation('✔️  Should the email be marked as verified?', 'yes')
            ->expectsQuestion('Enter a password (or choose from suggestions):', 'password123')
            ->expectsConfirmation('Create this user?', 'yes')
            ->expectsOutputToContain("User 'Test User' created successfully!")
            ->assertExitCode(0);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'user',
        ]);
    }

    /** @test */
    public function it_cancels_interactive_creation(): void
    {
        $this->artisan('create:user-interactive')
            ->expectsQuestion('👤 What is the user\'s name?', 'Test User')
            ->expectsQuestion('📧 What is the user\'s email?', 'test@example.com')
            ->expectsQuestion('🔑 What role should this user have?', 'user')
            ->expectsConfirmation('✔️  Should the email be marked as verified?', 'yes')
            ->expectsQuestion('Enter a password (or choose from suggestions):', 'password123')
            ->expectsConfirmation('Create this user?', 'no')
            ->expectsOutputToContain('User creation cancelled.')
            ->assertExitCode(0);

        $this->assertDatabaseCount('users', 0);
    }
}

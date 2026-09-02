<?php

namespace Tests\Feature\Commands;

use Tests\TestCase;
use App\Models\User;

class ShowUserTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->count(3)->create();
    }

    /** @test */
    public function it_shows_user_details(): void
    {
        $user = User::first();

        $this->artisan("user:info {$user->id}")
            ->expectsOutputToContain('User Found!')
            ->expectsOutputToContain("ID            : {$user->id}")
            ->expectsOutputToContain("Name          : {$user->name}")
            ->expectsOutputToContain("Email         : {$user->email}")
            ->expectsOutputToContain("Created At    : {$user->created_at->format('Y-m-d H:i:s')}")
            ->assertExitCode(0);
    }

    /** @test */
    public function it_returns_failure_for_nonexistent_user(): void
    {
        $this->artisan('user:info 9999')
            ->expectsOutputToContain('User with ID 9999 not found.')
            ->assertExitCode(1);
    }
}

<?php

namespace Tests\Feature\Commands;

use Tests\TestCase;
use App\Models\User;

class ListUsersTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->count(5)->create();
    }

    /** @test */
    public function it_lists_users_successfully(): void
    {
        $this->artisan('list:users', ['--per-page' => 10])
            ->expectsOutputToContain('Total Users: 5')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_shows_warning_when_no_users_found(): void
    {
        User::query()->delete();

        $this->artisan('list:users')
            ->expectsOutputToContain('No users found.')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_filters_verified_users(): void
    {
        $user = User::first();
        $user->update(['email_verified_at' => now()]);

        User::factory()->count(2)->create(['email_verified_at' => null]);

        $this->artisan('list:users --verified')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_searches_users_by_name_or_email(): void
    {
        $user = User::first();

        $this->artisan('list:users', ['--search' => $user->name])
            ->assertExitCode(0);
    }
}

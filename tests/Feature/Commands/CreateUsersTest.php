<?php

namespace Tests\Feature\Commands;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUsersTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    /** @test */
    public function it_creates_users_successfully(): void
    {
        $this->artisan('create:users 5')
            ->expectsOutputToContain('Creating 5 users')
            ->expectsOutputToContain('5 Dummy Users Created Successfully!')
            ->assertExitCode(0);

        $this->assertDatabaseCount('users', 5);
    }

    /** @test */
    public function it_fails_with_zero_count(): void
    {
        $this->artisan('create:users 0')
            ->expectsOutputToContain('Count must be at least 1.')
            ->assertExitCode(1);
    }

    /** @test */
    public function it_warns_when_creating_more_than_1000_users(): void
    {
        $this->artisan('create:users 1001')
            ->expectsOutputToContain('Creating 1001 users')
            ->expectsOutputToContain('1001 Dummy Users Created Successfully!')
            ->assertExitCode(0);

        $this->assertDatabaseCount('users', 1001);
    }

    /** @test */
    public function it_creates_user_with_role(): void
    {
        $this->artisan('create:users 3 --role=admin')
            ->assertExitCode(0);

        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseHas('users', ['role' => 'admin']);
    }

    /** @test */
    public function it_creates_user_as_unverified(): void
    {
        $this->artisan('create:users 2 --unverified')
            ->assertExitCode(0);

        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseHas('users', ['email_verified_at' => null]);
    }

    /** @test */
    public function it_creates_user_with_factory_state(): void
    {
        $this->artisan('create:users 2 --factory-state=unverified')
            ->assertExitCode(0);

        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseHas('users', ['email_verified_at' => null]);
    }
}

<?php

namespace Tests\Feature\Commands;

use Tests\TestCase;
use App\Models\User;

class DeleteUsersTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->count(3)->create();
    }

    /** @test */
    public function it_deletes_user_by_id(): void
    {
        $user = User::first();

        $this->artisan("delete:users --id={$user->id} --force")
            ->expectsOutputToContain("Deleted user with ID: {$user->id}")
            ->assertExitCode(0);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /** @test */
    public function it_deletes_user_by_email(): void
    {
        $user = User::first();

        $this->artisan("delete:users --email={$user->email} --force")
            ->expectsOutputToContain("Deleted user with email: {$user->email}")
            ->assertExitCode(0);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    /** @test */
    public function it_fails_when_user_not_found_by_id(): void
    {
        $this->artisan('delete:users --id=9999 --force')
            ->expectsOutputToContain('No user found with ID: 9999')
            ->assertExitCode(1);
    }

    /** @test */
    public function it_fails_when_user_not_found_by_email(): void
    {
        $this->artisan('delete:users --email=nonexistent@example.com --force')
            ->expectsOutputToContain('No user found with email: nonexistent@example.com')
            ->assertExitCode(1);
    }

    /** @test */
    public function it_deletes_all_users_with_confirmation(): void
    {
        $this->artisan('delete:users --all --force')
            ->expectsOutputToContain('Deleted 3 users.')
            ->assertExitCode(0);

        $this->assertDatabaseCount('users', 0);
    }

    /** @test */
    public function it_cancels_deletion_when_not_confirmed(): void
    {
        $this->artisan('delete:users --all')
            ->expectsConfirmation('Are you sure you want to delete all 3 users?', 'no')
            ->expectsOutputToContain('Operation cancelled.')
            ->assertExitCode(0);

        $this->assertDatabaseCount('users', 3);
    }
}

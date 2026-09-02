<?php

namespace Tests\Feature\Commands;

use Tests\TestCase;

class CacheClearAllTest extends TestCase
{
    /** @test */
    public function it_clears_all_caches_with_confirmation(): void
    {
        $this->artisan('cache:clear-all')
            ->expectsConfirmation("Are you sure you want to clear the following caches?\napplication, config, route, view, event", 'yes')
            ->expectsOutputToContain('Clearing application cache...')
            ->expectsOutputToContain('Clearing config cache...')
            ->expectsOutputToContain('Clearing route cache...')
            ->expectsOutputToContain('Clearing view cache...')
            ->expectsOutputToContain('Clearing event cache...')
            ->expectsOutputToContain('Cache Clear Summary')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_clears_specific_caches_with_force(): void
    {
        $this->artisan('cache:clear-all --only=config,route --force')
            ->expectsOutputToContain('Clearing config cache...')
            ->expectsOutputToContain('Clearing route cache...')
            ->expectsOutputToContain('Cache Clear Summary')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_cancels_when_not_confirmed(): void
    {
        $this->artisan('cache:clear-all')
            ->expectsConfirmation("Are you sure you want to clear the following caches?\napplication, config, route, view, event", 'no')
            ->expectsOutputToContain('Operation cancelled.')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_warns_with_invalid_cache_type(): void
    {
        $this->artisan('cache:clear-all --only=invalid --force')
            ->expectsOutputToContain('No valid cache types specified.')
            ->assertExitCode(0);
    }
}

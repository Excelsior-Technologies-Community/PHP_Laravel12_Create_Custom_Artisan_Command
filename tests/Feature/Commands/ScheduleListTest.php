<?php

namespace Tests\Feature\Commands;

use Tests\TestCase;

class ScheduleListTest extends TestCase
{
    /** @test */
    public function it_lists_scheduled_events(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('Scheduled Events')
            ->expectsOutputToContain('create:users')
            ->assertSuccessful();
    }
}

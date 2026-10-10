<?php

namespace Pulseline\JalaliEvents\Tests;

use Mockery;
use Pulseline\JalaliEvents\Services\CalendarUpdater;

class SyncCalendarCommandTest extends TestCase
{
    public function test_it_syncs_requested_year(): void
    {
        $updater = Mockery::mock(CalendarUpdater::class);

        $updater
            ->shouldReceive('syncYear')
            ->once()
            ->with(1405)
            ->andReturn(365);

        $this->app->instance(
            CalendarUpdater::class,
            $updater
        );

        $this->artisan(
            'jalali-events:sync',
            [
                'year' => 1405,
            ]
        )
            ->expectsOutput(
                'Syncing Jalali calendar year 1405...'
            )
            ->expectsOutput(
                'Calendar year 1405 synced successfully. 365 days saved.'
            )
            ->assertSuccessful();
    }

    public function test_it_rejects_invalid_year(): void
    {
        $this->artisan(
            'jalali-events:sync',
            [
                'year' => 1000,
            ]
        )
            ->expectsOutput(
                'Invalid Jalali year.'
            )
            ->assertFailed();
    }

    public function test_it_uses_current_jalali_year_when_year_is_not_provided(): void
    {
        $currentYear = verta()->year;

        $updater = Mockery::mock(CalendarUpdater::class);

        $updater
            ->shouldReceive('syncYear')
            ->once()
            ->with($currentYear)
            ->andReturn(365);

        $this->app->instance(
            CalendarUpdater::class,
            $updater
        );

        $this->artisan(
            'jalali-events:sync'
        )
            ->expectsOutput(
                "Syncing Jalali calendar year {$currentYear}..."
            )
            ->expectsOutput(
                "Calendar year {$currentYear} synced successfully. 365 days saved."
            )
            ->assertSuccessful();
    }
}

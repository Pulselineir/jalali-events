<?php

namespace Pulseline\JalaliEvents\Tests;

use Illuminate\Console\Scheduling\Schedule;

class WeeklySchedulerTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set(
            'jalali-events.update.enabled',
            true
        );

        $app['config']->set(
            'jalali-events.update.frequency',
            'weekly'
        );
    }

    public function test_sync_command_is_scheduled_weekly(): void
    {
        $event = $this->findSyncEvent();

        $this->assertNotNull($event);

        $this->assertSame(
            '0 0 * * 0',
            $event->expression
        );
    }

    private function findSyncEvent(): ?object
    {
        $schedule = app(Schedule::class);

        return collect($schedule->events())
            ->first(
                fn($event): bool =>
                str_contains(
                    $event->command ?? '',
                    'jalali-events:sync'
                )
            );
    }
}

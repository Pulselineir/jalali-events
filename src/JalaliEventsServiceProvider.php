<?php

namespace Pulseline\JalaliEvents;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Pulseline\JalaliEvents\Sources\TimeIrSource;
use Pulseline\JalaliEvents\Services\CalendarService;
use Pulseline\JalaliEvents\Commands\SyncCalendarCommand;
use Pulseline\JalaliEvents\Contracts\CalendarSourceInterface;
use Pulseline\JalaliEvents\Commands\FetchTimeIrFixtureCommand;
use Pulseline\JalaliEvents\Repositories\JsonCalendarRepository;
use Pulseline\JalaliEvents\Contracts\CalendarRepositoryInterface;
use Pulseline\JalaliEvents\Repositories\DatabaseCalendarRepository;

class JalaliEventsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/jalali-events.php',
            'jalali-events'
        );

        $this->registerRepository();

        $this->registerSource();

        $this->registerCalendarService();
    }

    public function boot(): void
    {
        $this->loadPackageMigrations();

        $this->publishPackageConfig();

        $this->registerCommands();

        $this->registerScheduler();
    }

    private function registerRepository(): void
    {
        $this->app->bind(
            CalendarRepositoryInterface::class,
            function () {
                return match (config('jalali-events.storage', 'database')) {
                    'json' => new JsonCalendarRepository(),

                    default => new DatabaseCalendarRepository(),
                };
            }
        );
    }

    private function registerSource(): void
    {
        $this->app->bind(
            CalendarSourceInterface::class,
            TimeIrSource::class
        );
    }

    private function registerCalendarService(): void
    {
        $this->app->singleton(
            'jalali-events',
            function ($app) {
                return new CalendarService(
                    $app->make(
                        CalendarRepositoryInterface::class
                    )
                );
            }
        );

        $this->app->alias(
            'jalali-events',
            CalendarService::class
        );
    }

    private function loadPackageMigrations(): void
    {
        $this->loadMigrationsFrom(
            __DIR__ . '/../database/migrations'
        );
    }

    private function publishPackageConfig(): void
    {
        $this->publishes(
            [
                __DIR__ . '/../config/jalali-events.php'
                => config_path('jalali-events.php'),
            ],
            'jalali-events-config'
        );
    }

    private function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            FetchTimeIrFixtureCommand::class,
            SyncCalendarCommand::class,
        ]);
    }

    private function registerScheduler(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        if (
            ! config(
                'jalali-events.update.enabled',
                true
            )
        ) {
            return;
        }

        $this->app->booted(
            function (): void {
                $schedule = $this->app->make(
                    Schedule::class
                );

                $event = $schedule->command(
                    'jalali-events:sync'
                );

                $frequency = config(
                    'jalali-events.update.frequency',
                    'monthly'
                );

                match ($frequency) {
                    'daily' => $event->daily(),

                    'weekly' => $event->weekly(),

                    'monthly' => $event->monthly(),

                    default => $event->monthly(),
                };
            }
        );
    }
}

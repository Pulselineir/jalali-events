<?php

namespace Pulseline\JalaliEvents\Commands;

use Illuminate\Console\Command;
use Pulseline\JalaliEvents\Services\CalendarUpdater;

class SyncCalendarCommand extends Command
{
    protected $signature = 'jalali-events:sync {year?}';

    protected $description = 'Sync Jalali calendar events and holidays for a given year.';

    public function handle(CalendarUpdater $updater): int
    {
        $yearArgument = $this->argument('year');

        $year = $yearArgument !== null
            ? (int) $yearArgument
            : verta()->year;

        if ($year < 1200 || $year > 1600) {
            $this->error('Invalid Jalali year.');

            return self::FAILURE;
        }

        $this->info(
            sprintf(
                'Syncing Jalali calendar year %d...',
                $year
            )
        );

        try {
            $count = $updater->syncYear($year);
        } catch (\Throwable $exception) {
            $this->error(
                sprintf(
                    'Calendar sync failed: %s',
                    $exception->getMessage()
                )
            );

            return self::FAILURE;
        }

        $this->info(
            sprintf(
                'Calendar year %d synced successfully. %d days saved.',
                $year,
                $count
            )
        );

        return self::SUCCESS;
    }
}

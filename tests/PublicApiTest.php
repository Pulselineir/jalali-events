<?php

namespace Pulseline\JalaliEvents\Tests;

use Pulseline\JalaliEvents\Facades\JalaliEvents;
use Pulseline\JalaliEvents\Parsers\TimeIrParser;
use Pulseline\JalaliEvents\Services\CalendarUpdater;
use Pulseline\JalaliEvents\Contracts\CalendarSourceInterface;
use Pulseline\JalaliEvents\Contracts\CalendarRepositoryInterface;

class PublicApiTest extends TestCase
{
    public function test_public_api_can_read_calendar_data(): void
    {
        $this->syncFixtureYear(1405);

        $day = JalaliEvents::date(
            '1405/04/03'
        );

        $this->assertNotNull($day);

        $this->assertSame(
            '1405/04/03',
            $day['jalali_date']
        );

        $this->assertSame(
            '2026-06-24',
            $day['gregorian_date']
        );

        $this->assertTrue(
            JalaliEvents::isHoliday(
                '1405/04/03'
            )
        );

        $events = JalaliEvents::events(
            '1405/04/03'
        );

        $this->assertNotEmpty(
            $events
        );

        $this->assertSame(
            'تاسوعای حسینی',
            $events[0]['title']
        );
    }

    public function test_public_api_can_query_date_ranges(): void
    {
        $this->syncFixtureYear(1405);

        $days = JalaliEvents::between(
            '1405/04/01',
            '1405/04/10'
        );

        $this->assertCount(
            10,
            $days
        );

        $holidays = JalaliEvents::holidaysBetween(
            '1405/04/01',
            '1405/04/10'
        );

        $this->assertNotEmpty(
            $holidays
        );

        $holidayCount = JalaliEvents::holidayCount(
            '1405/04/01',
            '1405/04/10'
        );

        $this->assertSame(
            count($holidays),
            $holidayCount
        );

        $holidayDates = array_column(
            $holidays,
            'jalali_date'
        );

        $this->assertContains(
            '1405/04/03',
            $holidayDates
        );

        $events = JalaliEvents::events(
            '1405/04/03'
        );

        $this->assertNotEmpty(
            $events
        );

        $this->assertSame(
            'تاسوعای حسینی',
            $events[0]['title']
        );
    }

    private function syncFixtureYear(int $year): void
    {
        $html = file_get_contents(
            __DIR__ . "/Fixtures/time-ir-{$year}.html"
        );

        $this->assertNotFalse($html);

        $source = new class($html) implements CalendarSourceInterface
        {
            public function __construct(
                private readonly string $html
            ) {}

            public function fetchYear(int $year): array
            {
                return [
                    'year' => $year,
                    'html' => $this->html,
                ];
            }
        };

        $updater = new CalendarUpdater(
            $source,
            $this->app->make(
                CalendarRepositoryInterface::class
            ),
            new TimeIrParser()
        );

        $count = $updater->syncYear(
            $year
        );

        $this->assertContains(
            $count,
            [365, 366]
        );
    }
}

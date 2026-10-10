<?php

namespace Pulseline\JalaliEvents\Tests;

use Pulseline\JalaliEvents\Parsers\TimeIrParser;
use Pulseline\JalaliEvents\Services\CalendarUpdater;
use Pulseline\JalaliEvents\Contracts\CalendarSourceInterface;
use Pulseline\JalaliEvents\Contracts\CalendarRepositoryInterface;

class CalendarUpdaterTest extends TestCase
{
    public function test_it_syncs_a_full_year_into_repository(): void
    {
        $html = file_get_contents(
            __DIR__ . '/Fixtures/time-ir-1405.html'
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

        $repository = new class implements CalendarRepositoryInterface
        {
            public array $days = [];

            public array $deletedYears = [];

            public function findByDate(string $jalaliDate): ?array
            {
                return $this->days[$jalaliDate] ?? null;
            }

            public function between(string $from, string $to): array
            {
                return [];
            }

            public function saveDay(array $day): void
            {
                $this->days[$day['jalali_date']] = $day;
            }

            public function deleteYear(int $year): void
            {
                $this->deletedYears[] = $year;

                $this->days = array_filter(
                    $this->days,
                    fn(array $day): bool =>
                    (int) $day['jalali_year'] !== $year
                );
            }
        };

        $updater = new CalendarUpdater(
            $source,
            $repository,
            new TimeIrParser()
        );

        $count = $updater->syncYear(1405);

        $this->assertContains(
            $count,
            [365, 366]
        );

        $this->assertCount(
            $count,
            $repository->days
        );

        $this->assertSame(
            [1405],
            $repository->deletedYears
        );

        $this->assertArrayHasKey(
            '1405/01/01',
            $repository->days
        );

        $this->assertArrayHasKey(
            'gregorian_date',
            $repository->days['1405/01/01']
        );

        $this->assertArrayHasKey(
            'events',
            $repository->days['1405/01/01']
        );
    }
}

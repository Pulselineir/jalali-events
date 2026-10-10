<?php

namespace Pulseline\JalaliEvents\Repositories;

use Pulseline\JalaliEvents\Models\CalendarDay;
use Pulseline\JalaliEvents\Contracts\CalendarRepositoryInterface;

class DatabaseCalendarRepository implements CalendarRepositoryInterface
{
    public function findByDate(string $jalaliDate): ?array
    {
        $day = CalendarDay::query()
            ->with('events')
            ->where('jalali_date', $jalaliDate)
            ->first();

        return $day?->toArray();
    }

    public function between(string $from, string $to): array
    {
        return CalendarDay::query()
            ->with('events')
            ->whereBetween('jalali_date', [$from, $to])
            ->orderBy('jalali_date')
            ->get()
            ->toArray();
    }

    public function saveDay(array $day): void
    {
        $events = $day['events'] ?? [];

        unset($day['events']);

        $calendarDay = CalendarDay::query()->updateOrCreate(
            ['jalali_date' => $day['jalali_date']],
            $day
        );

        $calendarDay->events()->delete();

        foreach ($events as $event) {
            $calendarDay->events()->create($event);
        }
    }

    public function deleteYear(int $year): void
    {
        CalendarDay::query()
            ->where('jalali_year', $year)
            ->delete();
    }
}

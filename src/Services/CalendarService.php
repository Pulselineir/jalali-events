<?php

namespace Pulseline\JalaliEvents\Services;

use InvalidArgumentException;
use Pulseline\JalaliEvents\Contracts\CalendarRepositoryInterface;

class CalendarService
{
    public function __construct(
        protected CalendarRepositoryInterface $repository
    ) {}

    public function date(string $jalaliDate): ?array
    {
        $this->validateDate($jalaliDate);

        return $this->repository->findByDate($jalaliDate);
    }

    public function between(string $from, string $to): array
    {
        $this->validateDate($from);
        $this->validateDate($to);

        if ($from > $to) {
            throw new InvalidArgumentException(
                'The start date must be before or equal to the end date.'
            );
        }

        return $this->repository->between($from, $to);
    }

    public function isHoliday(string $jalaliDate): bool
    {
        $day = $this->date($jalaliDate);

        return (bool) ($day['is_holiday'] ?? false);
    }

    public function events(string $jalaliDate): array
    {
        $day = $this->date($jalaliDate);

        return $day['events'] ?? [];
    }

    public function holidaysBetween(string $from, string $to): array
    {
        return array_values(
            array_filter(
                $this->between($from, $to),
                fn(array $day) => (bool) ($day['is_holiday'] ?? false)
            )
        );
    }

    public function holidayCount(string $from, string $to): int
    {
        return count(
            $this->holidaysBetween($from, $to)
        );
    }

    private function validateDate(string $jalaliDate): void
    {
        if (! preg_match(
            '/^\d{4}\/(0[1-9]|1[0-2])\/(0[1-9]|[12]\d|3[01])$/',
            $jalaliDate
        )) {
            throw new InvalidArgumentException(
                'Invalid Jalali date format. Expected format: YYYY/MM/DD.'
            );
        }
    }
}

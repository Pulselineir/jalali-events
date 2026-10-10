<?php

namespace Pulseline\JalaliEvents\Contracts;

interface CalendarRepositoryInterface
{
    public function findByDate(string $jalaliDate): ?array;

    public function between(string $from, string $to): array;

    public function saveDay(array $day): void;

    public function deleteYear(int $year): void;
}

<?php

namespace Pulseline\JalaliEvents\Contracts;

interface CalendarSourceInterface
{
    public function fetchYear(int $year): array;
}

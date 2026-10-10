<?php

namespace Pulseline\JalaliEvents\Services;

use Pulseline\JalaliEvents\Parsers\TimeIrParser;
use Pulseline\JalaliEvents\Contracts\CalendarSourceInterface;
use Pulseline\JalaliEvents\Contracts\CalendarRepositoryInterface;

class CalendarUpdater
{
    public function __construct(
        private readonly CalendarSourceInterface $source,
        private readonly CalendarRepositoryInterface $repository,
        private readonly TimeIrParser $parser,
    ) {}

    public function syncYear(int $year): int
    {
        $result = $this->source->fetchYear($year);

        $days = $this->parser->normalize(
            $result['html'],
            $year
        );

        $this->repository->deleteYear($year);

        foreach ($days as $day) {
            $this->repository->saveDay($day);
        }

        return count($days);
    }
}

<?php

namespace Pulseline\JalaliEvents\Parsers;

use RuntimeException;
use Hekmatinasser\Verta\Verta;
use Symfony\Component\DomCrawler\Crawler;

class TimeIrParser
{
    public function parse(string $html, int $year): array
    {
        $payload = $this->extractPayload($html);

        $months = $this->extractMonths(
            $payload,
            $year
        );

        if ($months === []) {
            throw new RuntimeException(
                sprintf(
                    'No calendar data found for Jalali year %d.',
                    $year
                )
            );
        }

        return $months;
    }

    public function normalize(string $html, int $year): array
    {
        $months = $this->parse($html, $year);

        $days = [];

        foreach ($months as $monthData) {
            $month = (int) $monthData['month'];

            $eventsByDay = [];

            foreach ($monthData['event_list'] ?? [] as $event) {
                if (
                    ! isset(
                        $event['jalali_year'],
                        $event['jalali_month'],
                        $event['jalali_day']
                    )
                ) {
                    continue;
                }

                if ((int) $event['jalali_year'] !== $year) {
                    continue;
                }

                if ((int) $event['jalali_month'] !== $month) {
                    continue;
                }

                $dayNumber = (int) $event['jalali_day'];

                $eventsByDay[$dayNumber][] = [
                    'title' => $event['title'] ?? '',
                    'category' => $this->resolveEventCategory(
                        $event['base'] ?? null
                    ),
                    'is_holiday' => (bool) ($event['is_holiday'] ?? false),
                ];
            }

            foreach ($monthData['day_list'] ?? [] as $dayData) {
                if (($dayData['enabled'] ?? false) !== true) {
                    continue;
                }

                $day = (int) ($dayData['index_in_base1'] ?? 0);

                if ($day < 1) {
                    continue;
                }

                $jalaliDate = sprintf(
                    '%04d/%02d/%02d',
                    $year,
                    $month,
                    $day
                );

                $verta = Verta::parse($jalaliDate);

                $gregorian = $verta->datetime();

                $days[] = [
                    'jalali_year' => $year,
                    'jalali_month' => $month,
                    'jalali_day' => $day,
                    'jalali_date' => $jalaliDate,

                    'gregorian_date' => $gregorian->format('Y-m-d'),

                    'weekday' => (int) $gregorian->format('w'),

                    'is_holiday' => (bool) (
                        $dayData['is_holiday'] ?? false
                    ),

                    'is_weekend' => (bool) (
                        $dayData['is_weekend'] ?? false
                    ),

                    'source' => 'time.ir',

                    'events' => $eventsByDay[$day] ?? [],
                ];
            }
        }

        usort(
            $days,
            fn(array $a, array $b): int =>
            strcmp(
                $a['jalali_date'],
                $b['jalali_date']
            )
        );

        return $days;
    }

    private function resolveEventCategory(mixed $base): ?string
    {
        return match ((int) $base) {
            0 => 'jalali',
            1 => 'gregorian',
            2 => 'hijri',
            default => null,
        };
    }

    private function extractPayload(string $content): string
    {
        if (
            str_contains($content, '"year":')
            && str_contains($content, '"day_list":')
            && str_contains($content, '"event_list":')
        ) {
            return $content;
        }

        $crawler = new Crawler($content);

        $payload = '';

        $crawler->filter('script')->each(
            function (Crawler $script) use (&$payload): void {
                $node = $script->getNode(0);

                if ($node === null) {
                    return;
                }

                $scriptContent = trim(
                    $node->textContent
                );

                $prefix = 'self.__next_f.push(';

                if (! str_starts_with($scriptContent, $prefix)) {
                    return;
                }

                $start = strlen($prefix);

                $end = strrpos(
                    $scriptContent,
                    ')'
                );

                if ($end === false || $end <= $start) {
                    return;
                }

                $json = substr(
                    $scriptContent,
                    $start,
                    $end - $start
                );

                try {
                    $chunk = json_decode(
                        $json,
                        true,
                        512,
                        JSON_THROW_ON_ERROR
                    );
                } catch (\JsonException) {
                    return;
                }

                if (
                    ! is_array($chunk)
                    || ! array_key_exists(1, $chunk)
                    || ! is_string($chunk[1])
                ) {
                    return;
                }

                $payload .= $chunk[1];
            }
        );

        if ($payload === '') {
            throw new RuntimeException(
                'Unable to extract Next.js payload from time.ir response.'
            );
        }

        return $payload;
    }
    
    private function extractMonths(string $payload, int $year): array
    {
        $months = [];

        for ($month = 1; $month <= 12; $month++) {
            $monthData = $this->extractMonth(
                $payload,
                $year,
                $month
            );

            if ($monthData !== null) {
                $months[] = $monthData;
            }
        }

        return $months;
    }

    private function extractMonth(
        string $payload,
        int $year,
        int $month
    ): ?array {
        $needle = sprintf(
            '{"year":%d,"month":%d,"day":1',
            $year,
            $month
        );

        $start = strpos(
            $payload,
            $needle
        );

        if ($start === false) {
            return null;
        }

        $json = $this->extractJsonObject(
            $payload,
            $start
        );

        try {
            $data = json_decode(
                $json,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $exception) {
            throw new RuntimeException(
                sprintf(
                    'Unable to decode calendar month %d/%d: %s',
                    $year,
                    $month,
                    $exception->getMessage()
                ),
                previous: $exception
            );
        }

        if (! is_array($data)) {
            return null;
        }

        return $data;
    }

    private function extractJsonObject(
        string $payload,
        int $start
    ): string {
        $depth = 0;
        $inString = false;
        $escaped = false;
        $length = strlen($payload);

        for ($i = $start; $i < $length; $i++) {
            $char = $payload[$i];

            if ($escaped) {
                $escaped = false;

                continue;
            }

            if ($inString && $char === '\\') {
                $escaped = true;

                continue;
            }

            if ($char === '"') {
                $inString = ! $inString;

                continue;
            }

            if ($inString) {
                continue;
            }

            if ($char === '{') {
                $depth++;

                continue;
            }

            if ($char === '}') {
                $depth--;

                if ($depth === 0) {
                    return substr(
                        $payload,
                        $start,
                        $i - $start + 1
                    );
                }
            }
        }

        throw new RuntimeException(
            'Unable to extract complete calendar JSON object from time.ir payload.'
        );
    }
}

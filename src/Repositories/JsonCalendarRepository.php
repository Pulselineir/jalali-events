<?php

namespace Pulseline\JalaliEvents\Repositories;

use Illuminate\Support\Facades\File;
use Pulseline\JalaliEvents\Contracts\CalendarRepositoryInterface;

class JsonCalendarRepository implements CalendarRepositoryInterface
{
    public function findByDate(string $jalaliDate): ?array
    {
        $data = $this->read();

        return $data[$jalaliDate] ?? null;
    }

    public function between(string $from, string $to): array
    {
        $data = $this->read();

        return array_values(array_filter(
            $data,
            fn(array $day) => $day['jalali_date'] >= $from
                && $day['jalali_date'] <= $to
        ));
    }

    public function saveDay(array $day): void
    {
        $data = $this->read();

        $data[$day['jalali_date']] = $day;

        ksort($data);

        $this->write($data);
    }

    public function deleteYear(int $year): void
    {
        $data = $this->read();

        $data = array_filter(
            $data,
            fn(array $day) => (int) $day['jalali_year'] !== $year
        );

        $this->write($data);
    }

    private function read(): array
    {
        $path = config('jalali-events.json.path');

        if (! File::exists($path)) {
            return [];
        }

        $contents = File::get($path);

        if ($contents === '') {
            return [];
        }

        return json_decode($contents, true) ?? [];
    }

    private function write(array $data): void
    {
        $path = config('jalali-events.json.path');

        File::ensureDirectoryExists(dirname($path));

        File::put(
            $path,
            json_encode(
                $data,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            )
        );
    }
}

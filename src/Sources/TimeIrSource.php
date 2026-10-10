<?php

namespace Pulseline\JalaliEvents\Sources;

use RuntimeException;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Pulseline\JalaliEvents\Contracts\CalendarSourceInterface;

class TimeIrSource implements CalendarSourceInterface
{
    public function __construct(
        private readonly TimeIrActionResolver $actionResolver
    ) {}

    public function fetchYear(int $year): array
    {
        $pageResponse = $this->http()
            ->get('https://www.time.ir/event-year');

        if ($pageResponse->failed()) {
            throw new RuntimeException(
                'Failed to load time.ir yearly calendar page.'
            );
        }

        $actionId = $this->actionResolver
            ->resolveYearlyCalendarActionId(
                $pageResponse->body()
            );

        $response = $this->http()
            ->withHeaders([
                'Accept' => 'text/x-component',
                'Content-Type' => 'text/plain;charset=UTF-8',
                'Next-Action' => $actionId,
                'Origin' => 'https://www.time.ir',
                'Referer' => 'https://www.time.ir/event-year',
            ])
            ->withBody(
                json_encode(
                    [$year],
                    JSON_THROW_ON_ERROR
                ),
                'text/plain;charset=UTF-8'
            )
            ->post(
                'https://www.time.ir/event-year'
            );

        if ($response->failed()) {
            throw new RuntimeException(
                sprintf(
                    'Failed to fetch calendar year %d from time.ir.',
                    $year
                )
            );
        }

        $body = $response->body();

        if (
            ! str_contains(
                $body,
                sprintf(
                    '"year":%d',
                    $year
                )
            )
        ) {
            throw new RuntimeException(
                sprintf(
                    'time.ir response does not contain requested Jalali year %d.',
                    $year
                )
            );
        }

        return [
            'year' => $year,
            'html' => $body,
        ];
    }

    private function http(): PendingRequest
    {
        return Http::timeout(
            config('jalali-events.http.timeout', 15)
        )
            ->retry(
                config('jalali-events.http.retry', 3),
                config('jalali-events.http.retry_sleep', 1000)
            )
            ->withUserAgent(
                config(
                    'jalali-events.http.user_agent',
                    'JalaliEvents Laravel Package'
                )
            )
            ->withOptions([
                'verify' => config(
                    'jalali-events.http.verify_ssl',
                    true
                ),
            ]);
    }
}

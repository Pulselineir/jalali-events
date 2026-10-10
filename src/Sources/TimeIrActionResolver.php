<?php

namespace Pulseline\JalaliEvents\Sources;

use RuntimeException;
use Illuminate\Support\Facades\Http;

class TimeIrActionResolver
{
    public function resolveYearlyCalendarActionId(string $html): string
    {
        $chunks = $this->extractChunkUrls($html);

        if ($chunks === []) {
            throw new RuntimeException(
                'No Next.js chunks found in time.ir HTML.'
            );
        }

        foreach ($chunks as $chunk) {
            $response = Http::timeout(
                config('jalali-events.http.timeout', 15)
            )
                ->withOptions([
                    'verify' => config(
                        'jalali-events.http.verify_ssl',
                        true
                    ),
                ])
                ->get(
                    'https://www.time.ir' . $chunk
                );

            if ($response->failed()) {
                continue;
            }

            $javascript = $response->body();

            if (
                ! str_contains(
                    $javascript,
                    'fetchEventYearlyCalendarAction'
                )
            ) {
                continue;
            }

            $actionId = $this->extractActionId(
                $javascript
            );

            if ($actionId !== null) {
                return $actionId;
            }
        }

        throw new RuntimeException(
            'Unable to discover time.ir yearly calendar server action.'
        );
    }

    private function extractChunkUrls(string $html): array
    {
        preg_match_all(
            '#/_next/static/chunks/[^"\']+\.js#',
            $html,
            $matches
        );

        return array_values(
            array_unique(
                $matches[0] ?? []
            )
        );
    }

    private function extractActionId(string $javascript): ?string
    {
        $matched = preg_match(
            '/createServerReference\)\(\s*"([a-f0-9]{40,64})".*?"fetchEventYearlyCalendarAction"/s',
            $javascript,
            $matches
        );

        if ($matched !== 1) {
            return null;
        }

        return $matches[1];
    }
}

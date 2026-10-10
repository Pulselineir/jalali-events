<?php

namespace Pulseline\JalaliEvents\Tests;

use Illuminate\Support\Facades\Http;
use Pulseline\JalaliEvents\Sources\TimeIrActionResolver;
use Pulseline\JalaliEvents\Sources\TimeIrSource;

class TimeIrSourceTest extends TestCase
{
    public function test_it_fetches_requested_year_using_server_action(): void
    {
        $actionId = '7f1826b2a370a6dcddacdb9aa92bc13ba09419e2ee';

        $html = <<<'HTML'
                <html>
                <head>
                <script src="/_next/static/chunks/calendar.js"></script>
                </head>
                <body></body>
                </html>
                HTML;

                        $javascript = <<<JS
                let w=(0,N.createServerReference)(
                "{$actionId}",
                N.callServer,
                void 0,
                N.findSourceMapURL,
                "fetchEventYearlyCalendarAction"
                );
                JS;

                        $calendarResponse = <<<'TEXT'
                0:{"data":[{"year":1404,"month":1,"day":1}]}
                TEXT;

        Http::fake([
            'https://www.time.ir/event-year' => Http::sequence()
                ->push($html, 200)
                ->push(
                    $calendarResponse,
                    200,
                    [
                        'Content-Type' => 'text/x-component',
                    ]
                ),

            'https://www.time.ir/_next/static/chunks/calendar.js'
            => Http::response(
                $javascript,
                200
            ),
        ]);

        $source = new TimeIrSource(
            new TimeIrActionResolver()
        );

        $result = $source->fetchYear(1404);

        $this->assertSame(
            1404,
            $result['year']
        );

        $this->assertStringContainsString(
            '"year":1404',
            $result['html']
        );

        Http::assertSent(
            function ($request): bool {
                return $request->url()
                    === 'https://www.time.ir/event-year'
                    && $request->method() === 'POST'
                    && $request->hasHeader('Next-Action')
                    && $request->body() === '[1404]';
            }
        );
    }
}

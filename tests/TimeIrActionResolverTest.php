<?php

namespace Pulseline\JalaliEvents\Tests;

use Illuminate\Support\Facades\Http;
use Pulseline\JalaliEvents\Sources\TimeIrActionResolver;

class TimeIrActionResolverTest extends TestCase
{
    public function test_it_discovers_yearly_calendar_action_id(): void
    {
        $html = <<<'HTML'
                <html>
                <head>
                    <script src="/_next/static/chunks/example-one.js"></script>
                    <script src="/_next/static/chunks/example-two.js"></script>
                </head>
                <body>
                </body>
                </html>
                HTML;

        Http::fake([
            'https://www.time.ir/_next/static/chunks/example-one.js'
            => Http::response(
                'console.log("nothing here");',
                200
            ),

            'https://www.time.ir/_next/static/chunks/example-two.js'
            => Http::response(
                <<<'JS'
let w=(0,N.createServerReference)(
"7f1826b2a370a6dcddacdb9aa92bc13ba09419e2ee",
N.callServer,
void 0,
N.findSourceMapURL,
"fetchEventYearlyCalendarAction"
);
JS,
                200
            ),
        ]);

        $resolver = new TimeIrActionResolver();

        $actionId = $resolver
            ->resolveYearlyCalendarActionId(
                $html
            );

        $this->assertSame(
            '7f1826b2a370a6dcddacdb9aa92bc13ba09419e2ee',
            $actionId
        );
    }
}

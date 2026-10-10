<?php

namespace Pulseline\JalaliEvents\Tests;

use Pulseline\JalaliEvents\Parsers\TimeIrParser;

class PackageTest extends TestCase
{
    public function test_it_parses_real_time_ir_fixture(): void
    {
        $html = file_get_contents(
            __DIR__ . '/Fixtures/time-ir-1405.html'
        );

        $this->assertNotFalse($html);

        $parser = new TimeIrParser();

        $months = $parser->parse(
            $html,
            1405
        );

        $this->assertCount(12, $months);

        $this->assertSame(
            1405,
            $months[0]['year']
        );

        $this->assertSame(
            1,
            $months[0]['month']
        );

        $this->assertArrayHasKey(
            'day_list',
            $months[0]
        );

        $this->assertArrayHasKey(
            'event_list',
            $months[0]
        );
    }
}

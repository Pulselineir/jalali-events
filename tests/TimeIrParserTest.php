<?php

namespace Pulseline\JalaliEvents\Tests;

use Pulseline\JalaliEvents\Parsers\TimeIrParser;

class TimeIrParserTest extends TestCase
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

        $this->assertCount(
            12,
            $months
        );

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

    public function test_real_fixture_contains_all_enabled_days_of_year(): void
    {
        $html = file_get_contents(
            __DIR__ . '/Fixtures/time-ir-1405.html'
        );

        $parser = new TimeIrParser();

        $months = $parser->parse(
            $html,
            1405
        );

        $days = 0;

        foreach ($months as $month) {
            foreach ($month['day_list'] as $day) {
                if ($day['enabled'] === true) {
                    $days++;
                }
            }
        }

        $this->assertContains(
            $days,
            [365, 366]
        );
    }

    public function test_it_normalizes_all_days_of_real_fixture(): void
    {
        $html = file_get_contents(
            __DIR__ . '/Fixtures/time-ir-1405.html'
        );

        $parser = new TimeIrParser();

        $days = $parser->normalize(
            $html,
            1405
        );

        $this->assertContains(
            count($days),
            [365, 366]
        );

        $this->assertSame(
            '1405/01/01',
            $days[0]['jalali_date']
        );

        $this->assertArrayHasKey(
            'gregorian_date',
            $days[0]
        );

        $this->assertArrayHasKey(
            'is_holiday',
            $days[0]
        );

        $this->assertArrayHasKey(
            'is_weekend',
            $days[0]
        );

        $this->assertArrayHasKey(
            'events',
            $days[0]
        );
    }

    public function test_it_parses_real_server_action_response(): void
    {
        $response = file_get_contents(
            __DIR__ . '/Fixtures/time-ir-1404-response.txt'
        );

        $this->assertNotFalse($response);

        $parser = new TimeIrParser();

        $months = $parser->parse(
            $response,
            1404
        );

        $this->assertCount(
            12,
            $months
        );

        $this->assertSame(
            1404,
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

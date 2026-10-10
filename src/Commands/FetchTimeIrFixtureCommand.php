<?php

namespace Pulseline\JalaliEvents\Commands;

use Illuminate\Console\Command;
use Pulseline\JalaliEvents\Contracts\CalendarSourceInterface;

class FetchTimeIrFixtureCommand extends Command
{
    protected $signature = 'jalali-events:fetch-fixture {year=1405}';

    protected $description = 'Fetch raw calendar HTML from time.ir and save it as a test fixture.';

    public function handle(CalendarSourceInterface $source): int
    {
        $year = (int) $this->argument('year');

        $result = $source->fetchYear($year);

        $packageRoot = dirname(__DIR__, 2);
        $directory = $packageRoot . '/tests/Fixtures';

        if (! is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $path = $directory . "/time-ir-{$year}.html";

        file_put_contents(
            $path,
            $result['html']
        );

        $this->info("Fixture saved to: {$path}");

        return self::SUCCESS;
    }
}

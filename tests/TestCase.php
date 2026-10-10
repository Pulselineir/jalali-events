<?php

namespace Pulseline\JalaliEvents\Tests;

use Illuminate\Support\Facades\Artisan;
use Orchestra\Testbench\TestCase as Orchestra;
use Pulseline\JalaliEvents\JalaliEventsServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            JalaliEventsServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');

        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        $app['config']->set(
            'jalali-events.database.connection',
            'testing'
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('migrate', [
            '--database' => 'testing',
            '--force' => true,
        ]);
    }
}

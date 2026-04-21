<?php

namespace PlinCode\LaravelFullName\Tests;

use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as Orchestra;
use PlinCode\LaravelFullName\LaravelFullNameServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (File::allFiles(__DIR__.'/Fixtures/database/migrations') as $migration) {
            $instance = include $migration->getRealPath();
            $instance->down();
            $instance->up();
        }
    }

    protected function getPackageProviders($app): array
    {
        return [
            LaravelFullNameServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}

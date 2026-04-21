<?php

namespace PlinCode\LaravelFullName\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use PlinCode\LaravelFullName\LaravelFullNameServiceProvider;

class TestCase extends Orchestra
{
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

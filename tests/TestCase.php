<?php

namespace PlinCode\LaravelFullName\Tests;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use PlinCode\LaravelFullName\LaravelFullNameServiceProvider;

class TestCase extends Orchestra
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        foreach (['test_booking_items', 'test_bookings', 'test_persons', 'test_accounts'] as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
            }
        }

        foreach (File::allFiles(__DIR__.'/Fixtures/database/migrations') as $migration) {
            $instance = include $migration->getRealPath();
            $instance->down();
            $instance->up();
        }
    }

    #[\Override]
    protected function getPackageProviders($app): array
    {
        return [
            LaravelFullNameServiceProvider::class,
        ];
    }

    #[\Override]
    public function getEnvironmentSetUp($app): void
    {
        $driver = $_ENV['LFN_INTEGRATION_DRIVER'] ?? null;

        if ($driver === 'mysql') {
            $app['config']->set('database.default', 'integration');
            $app['config']->set('database.connections.integration', [
                'driver' => 'mysql',
                'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
                'port' => (int) ($_ENV['DB_PORT'] ?? 3306),
                'database' => $_ENV['DB_DATABASE'] ?? 'testing',
                'username' => $_ENV['DB_USERNAME'] ?? 'root',
                'password' => $_ENV['DB_PASSWORD'] ?? 'root',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
            ]);

            return;
        }

        if ($driver === 'pgsql') {
            $app['config']->set('database.default', 'integration');
            $app['config']->set('database.connections.integration', [
                'driver' => 'pgsql',
                'host' => $_ENV['DB_HOST'] ?? '127.0.0.1',
                'port' => (int) ($_ENV['DB_PORT'] ?? 5432),
                'database' => $_ENV['DB_DATABASE'] ?? 'testing',
                'username' => $_ENV['DB_USERNAME'] ?? 'postgres',
                'password' => $_ENV['DB_PASSWORD'] ?? 'postgres',
                'charset' => 'utf8',
                'prefix' => '',
                'schema' => 'public',
            ]);

            return;
        }

        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}

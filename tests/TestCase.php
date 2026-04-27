<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelDoctrine\ORM\DoctrineServiceProvider;
use Stemble\LaravelDoctrineFactory\DoctrineFactory;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DoctrineFactory::useNamespace('Workbench\\Database\\Factories\\');
    }

    protected function getPackageProviders($app)
    {
        return [
            DoctrineServiceProvider::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom('workbench/database/migrations');
    }

    protected function defineEnvironment($app)
    {
        // The sqlite database path comes from phpunit.xml (DB_DATABASE env).
        // Laravel's SQLiteConnector throws if the file doesn't exist, so make
        // sure it's there before the first connection attempt.
        $database = config('database.connections.sqlite.database');
        if ($database && $database !== ':memory:' && ! file_exists($database)) {
            touch($database);
        }

        tap($app['config'], function ($config) {
            $config->set('doctrine.managers.default.meta', 'attributes');
            $config->set('doctrine.managers.default.connection', 'sqlite');
        });
    }
}

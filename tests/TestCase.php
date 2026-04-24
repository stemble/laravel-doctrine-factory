<?php

namespace Tests;


use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelDoctrine\ORM\DoctrineServiceProvider;
use Nolanos\LaravelDoctrineFactory\DoctrineFactory;

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
        // Setup default database to use sqlite :memory:
        tap($app['config'], function ($config) {
            $config->set('doctrine.managers.default.meta', 'attributes');
            $config->set('doctrine.managers.default.connection', 'sqlite');
        });
    }
}

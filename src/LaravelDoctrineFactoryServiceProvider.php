<?php

namespace Stemble\LaravelDoctrineFactory;

use Illuminate\Support\ServiceProvider;

// This provider exists solely to register the package with Laravel's auto-discovery
// mechanism (extra.laravel.providers in composer.json). That allows tools like
// Laravel Boost to locate the package root and load resources/boost/ automatically.
class LaravelDoctrineFactoryServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        //
    }
}

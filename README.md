# Laravel Doctrine Factory

Use [Eloquent Factories](https://laravel.com/docs/11.x/eloquent-factories) with your Doctrine Entities.

## Installation

Install via Composer:

```bash
composer require stemble/laravel-doctrine-factory
```

## Usage

Create Laravel factories and extend `Stemble\LaravelDoctrineFactory\DoctrineFactory` instead of the
usual `Illuminate\Database\Eloquent\Factories\Factory`.

`DoctrineFactory` subclasses the default `Factory` to override how it instantiates and
saves the objects. Everything else works exactly the same.

## Design Philosophy

### No Documentation Necessary

The goal of this package is to provide a drop-in replacement for Laravel's default
factories that works with Doctrine entities. It should mirror the existing API
so closely that you could read the Laravel documentation and use this package without
any additional documentation (beyond setup).

### Explained Overrides 

Quite a few methods are overridden by this package to make Factories work with Doctrine entities.
The doc blocks of all overridden methods will be explained next to the `@override` tag.


# Development

### Setup

```bash
git clone git@github.com:stemble/laravel-doctrine-factory.git

cd laravel-doctrine-factory

composer install
```

### Running Tests

```bash

composer test
```

### Publishing new Versions

To publish a new version of the package, you need to create a new tag and push it to the repository.

```bash
git tag vx.x.x
git push origin vx.x.x
```

Go to [Packagist](https://packagist.org/packages/stemble/laravel-doctrine-factory) and click on "Update" to
update the package.

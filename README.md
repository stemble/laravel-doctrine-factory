# Laravel Doctrine Factory

[![Tests](https://github.com/stemble/laravel-doctrine-factory/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/stemble/laravel-doctrine-factory/actions/workflows/tests.yml)

Use [Eloquent Factories](https://laravel.com/docs/11.x/eloquent-factories) with your Doctrine Entities.

## Why Not the Official Factories?

`laravel-doctrine/orm` ships with [its own factory system](https://laravel-doctrine-orm-official.readthedocs.io/en/latest/testing.html),
but its API is frozen at Laravel's pre-8.x style:

- Factories are registered as global closures via `$factory->define(...)` — no class-based factories
- "States" are second-class — declared via `defineAs(..., 'admin', ...)` instead of first-class state methods
- No relationship helpers (`for()`, `has()`)
- No sequences, no `recycle()`, no `afterMaking` / `afterCreating` callbacks
- No magic relationship methods (`hasPosts()`, `forUser()`)

By contrast, this package keeps you on Laravel's modern factory API. If you already know Eloquent factories
you already know this package — read the [Laravel docs](https://laravel.com/docs/11.x/eloquent-factories) and use
this package as a drop-in replacement. See [Feature Compatibility](#feature-compatibility) for the full status grid.

## Installation

Install via Composer:

```bash
composer require stemble/laravel-doctrine-factory
```

## Usage

Create Laravel factories and extend `Stemble\LaravelDoctrineFactory\DoctrineFactory` instead of the
usual `Illuminate\Database\Eloquent\Factories\Factory`.

`DoctrineFactory` subclasses the default `Factory` to override how it instantiates and
saves the objects. Everything else should work exactly the same.

## Persistence Semantics

Doctrine splits "register an entity with the EntityManager" (`persist`) from "write
to the database" (`flush`). This package mirrors that split across `make` and `create`:

- **`make()`** — instantiates the entity and calls `EntityManager::persist()` on it.
  Nothing is written to the database yet. Related entities pulled in through the
  factory chain are persisted too, so a later `flush()` will save them together
  without requiring `cascade: ['persist']` on every association.
- **`create()`** — does everything `make()` does, then calls `EntityManager::flush()`
  to write the changes.

If you call `make()` and later call `EntityManager::flush()` yourself, the made
entities will be inserted at that point. This is the intended deviation from
Laravel's `make()`, which leaves models entirely in-memory.

## Feature Compatibility

Status of each Laravel factory feature when used through `DoctrineFactory`:

- ✅ **works** — migrated and behaves like the Eloquent version
- ⚠️ **semi** — works but in a slightly different way than expected
- ❌ **broken** — does not work, or has not been verified
- **N/A** — no change required, or no Doctrine equivalent

| Feature                                                | Status   | Notes                                                                                        |
|--------------------------------------------------------|----------|----------------------------------------------------------------------------------------------|
| Defining factories (`definition()`)                    | ✅ works  |                                                                                              |
| `make()`                                               | ⚠️ semi  | Also calls `EntityManager::persist()` — see [Persistence Semantics](#persistence-semantics)  |
| `create()`                                             | ✅ works  |                                                                                              |
| `count()`                                              | ✅ works  |                                                                                              |
| States (`state()`, state methods)                      | ✅ works  |                                                                                              |
| Sequences (`Sequence`, `sequence()`)                   | ✅ works  |                                                                                              |
| Has-many (`has()`)                                     | ✅ works  |                                                                                              |
| Belongs-to (`for()`)                                   | ✅ works  |                                                                                              |
| Magic relationship methods (`hasPosts()`, `forUser()`) | ✅ works  |                                                                                              |
| Many-to-many (`hasAttached()`)                         | N/A      | Set a `Collection` in factory state for plain M2M, or `->has()` the pivot entity for M2M with pivot data |
| `recycle()`                                            | ✅ works  |                                                                                              |
| `afterMaking()` callback                               | ✅ works  |                                                                                              |
| `afterCreating()` callback                             | ✅ works  |                                                                                              |
| Polymorphic relationships                              | N/A      | No direct Doctrine equivalent                                                                |
| `trashed()` / soft deletes                             | N/A      | Doctrine has no built-in soft deletes                                                        |

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

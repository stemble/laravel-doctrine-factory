# Laravel Doctrine Factory

[![Tests](https://github.com/stemble/laravel-doctrine-factory/actions/workflows/tests.yml/badge.svg?branch=main)](https://github.com/stemble/laravel-doctrine-factory/actions/workflows/tests.yml)

Use [Eloquent Factories](https://laravel.com/docs/11.x/eloquent-factories) with your Doctrine Entities.

## Why Not the Official Factories?

`laravel-doctrine/orm` ships
with [its own factory system](https://laravel-doctrine-orm-official.readthedocs.io/en/latest/testing.html),
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

## Getting Started

Add Laravel's `HasFactory` trait to your entity and annotate it with `@implements HasFactory<YourFactory>`.
The annotation is optional but gives IDEs full type inference — `User::factory()` returns `UserFactory`,
so state methods like `->admin()` are recognised without a cast.

`DoctrineFactory` extends Laravel's `Illuminate\Database\Eloquent\Factories\Factory` rather than replacing it,
so the standard `HasFactory` trait works without modification — there is no Doctrine-specific version to import.

```php
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @implements HasFactory<UserFactory>
 */
class User
{
    use HasFactory;

    private string $name;
    private bool $admin = false;
    private Collection $posts;

    public function __construct(string $name)
    {
        $this->name  = $name;
        $this->posts = new ArrayCollection;
    }
}
```

Then create a factory that extends `Stemble\LaravelDoctrineFactory\DoctrineFactory`:

```php
use Stemble\LaravelDoctrineFactory\DoctrineFactory;

class UserFactory extends DoctrineFactory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name'  => fake()->name(),
            'admin' => false,
        ];
    }

    public function admin(): static
    {
        return $this->state(['admin' => true]);
    }
}
```

Factory discovery follows the same convention as Eloquent: given an entity `App\Entities\User`, Laravel looks
for `Database\Factories\Entities\UserFactory`. The factory file lives in `database/factories/Entities/UserFactory.php`.
Subdirectories under `database/factories/` are mirrored from your entity namespace below `App\`.

Use it the same way you'd use any Laravel factory:

```php
$user  = User::factory()->create();
$users = User::factory()->count(3)->create();
$admin = User::factory()->admin()->create();
```

## Feature Compatibility

Status of each Laravel factory feature when used through `DoctrineFactory`:

- ✅ **works** — migrated and behaves like the Eloquent version
- ✅ **works\*** — works, but with intentionally different semantics; see linked note
- ❌ **broken** — does not work, or has not been verified
- **N/A** — no change required, or no Doctrine equivalent

| Feature                                                | Status   | Notes                                                                                                                   |
|--------------------------------------------------------|----------|-------------------------------------------------------------------------------------------------------------------------|
| Defining factories (`definition()`)                    | ✅ works  |                                                                                                                         |
| `make()`                                               | ✅ works* | Also calls `EntityManager::persist()` — see [`make()` persists to the identity map](#make-persists-to-the-identity-map) |
| `create()`                                             | ✅ works  |                                                                                                                         |
| `count()`                                              | ✅ works  |                                                                                                                         |
| States (`state()`, state methods)                      | ✅ works  |                                                                                                                         |
| Sequences (`Sequence`, `sequence()`)                   | ✅ works  |                                                                                                                         |
| Has-many (`has()`)                                     | ✅ works  |                                                                                                                         |
| Belongs-to (`for()`)                                   | ✅ works  |                                                                                                                         |
| Magic relationship methods (`hasPosts()`, `forUser()`) | ✅ works  |                                                                                                                         |
| Many-to-many (`hasAttached()`)                         | N/A      | Set a `Collection` in factory state for plain M2M, or `->has()` the pivot entity for M2M with pivot data                |
| `recycle()`                                            | ✅ works  |                                                                                                                         |
| `afterMaking()` callback                               | ✅ works  |                                                                                                                         |
| `afterCreating()` callback                             | ✅ works  |                                                                                                                         |
| Polymorphic relationships                              | N/A      | No direct Doctrine equivalent                                                                                           |
| `trashed()` / soft deletes                             | N/A      | Doctrine has no built-in soft deletes                                                                                   |

## Differences From Eloquent

This package mirrors Laravel's `Illuminate\Database\Eloquent\Factories\Factory` API as closely as possible,
so the [Laravel docs](https://laravel.com/docs/11.x/eloquent-factories) work as your primary reference.
The deviations below fall out of how Doctrine handles persistence, instantiation, and relationships — none of
them are bugs, but they are not obvious from reading the Laravel docs.

### `make()` persists to the identity map

Doctrine splits "register an entity with the EntityManager" (`persist`) from "write to the database"
(`flush`). This package mirrors that split across `make()` and `create()`:

- **`make()`** — instantiates the entity and calls `EntityManager::persist()` on it. Nothing is written to
  the database yet.
- **`create()`** — does everything `make()` does, then calls `EntityManager::flush()` to write the changes.

This is the intended deviation from Laravel's `make()`, which leaves models entirely in-memory. **If you call
`make()` and later trigger an `EntityManager::flush()` yourself — even unintentionally — the made entities
will be inserted at that point.**

Two consequences worth knowing:

1. **No `cascade: ['persist']` required.** Related entities pulled in through the factory chain are
   persisted too, so a single `flush()` saves the whole graph without needing cascade rules on every
   association.
2. **Build a graph, then flush once.** You can `make()` related entities, set them up however you need,
   then `create()` the root — everything flushes together.

### Constructor-aware instantiation

Eloquent models share a uniform constructor; Doctrine entities don't. This package routes definition
attributes through the entity constructor first, then sets remaining attributes via reflection.

```php
class User
{
    private string $name;
    private bool $admin = false;
    private Collection $posts;

    public function __construct(string $name)
    {
        $this->name  = $name;
        $this->posts = new ArrayCollection;
    }
}

class UserFactory extends DoctrineFactory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name'  => fake()->name(),  // matched to constructor parameter
            'admin' => false,           // set via reflection after construction
        ];
    }
}
```

A few rules to keep in mind:

- **Required constructor parameters must be provided.** If the definition doesn't include a key matching a
  required constructor parameter, `MissingConstructorAttributesException` is thrown.
- **Optional constructor parameters can be omitted** from the definition.
- **Constructor logic still runs.** If your constructor transforms a value, the factory respects that.

### Reflection bypasses setters

Non-constructor attributes are written directly to private properties via reflection — setters are
**not** called. If a setter has side effects (validation, eventing, syncing a related collection), it
won't fire. Either:

- Pass the value through the constructor, or
- Use `afterMaking()` to call the setter explicitly — see [Bidirectional Relationships](#bidirectional-relationships).

### Relationships use entity instances, not IDs

`for()` and `has()` resolve to full entity instances:

```php
$post = Post::factory()->for(User::factory())->make();

$post->getUser();    // returns a User entity
$post->getUser_id(); // ← this doesn't exist
```

This is fundamental to how Doctrine works — you set `$post->user = $userEntity`, never
`$post->user_id = 123`.

### Relationship names match entity property names

The second argument to `->for()` (and `->has()`) is the **entity property name**, not a database column,
relationship table, or class name:

```php
// `Post` has a property `User $author`
$user = User::factory()->create();
$post = Post::factory()->for($user, 'author')->create();
```

When the property name matches the related entity's basename (e.g. property `$user` of type `User`), magic
methods like `forUser()` infer the property automatically and the second argument can be omitted.

## Bidirectional Relationships

Doctrine relationships are often bidirectional — both entities reference each other. The factory only sets
one side. The owning side persists correctly to the database, but **reading the inverse side in-memory will
return an empty collection** until you flush and refresh:

```php
$post = Post::factory()->for($user)->make();

$post->getAuthor();  // returns $user      ← owning side
$user->getPosts();   // empty Collection   ← inverse side, NOT synced
```

This is the single biggest gotcha for developers coming from Eloquent, where models are dumb data
containers and the inverse-side invariant doesn't exist.

### Syncing with `afterMaking()`

Sync the inverse side from `afterMaking()`. Put it in `configure()` so it runs every time the factory is
used:

```php
class PostFactory extends DoctrineFactory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'title'  => fake()->sentence(),
            'author' => User::factory(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Post $post) {
            $post->getAuthor()->addPost($post);
        });
    }
}
```

Now `$user->getPosts()` returns the new post immediately — no flush, no refresh.

### `afterMaking()` vs `afterCreating()`

- **`afterMaking()`** runs *before* flush. Use it for setting up references between in-memory entities —
  bidirectional sync, building related graphs that must exist before persistence.
- **`afterCreating()`** runs *after* flush. Use it when the entity needs a database-assigned ID before the
  next operation, or when you're modifying entities that are already persisted.

```php
class PostFactory extends DoctrineFactory
{
    protected $model = Post::class;

    // afterMaking: create child entities in-memory before flush — they'll be persisted together
    public function withComments(int $count = 3): static
    {
        return $this->afterMaking(function (Post $post) use ($count) {
            Comment::factory()->count($count)->for($post)->make();
        });
    }

    // afterCreating: needs the database-assigned ID, so it runs after flush
    public function withSlug(): static
    {
        return $this->afterCreating(function (Post $post) {
            $post->setSlug($post->getId() . '-' . str($post->getTitle())->slug());
        });
    }
}
```

## Patterns

A handful of conventions that come up repeatedly when working with this package.

### `configure()` for default callbacks

Override `configure()` to register `afterMaking` / `afterCreating` callbacks that should always run for a
factory — most commonly, the bidirectional relationship sync shown above. It's called once per factory
instance and is the right place for setup that every caller depends on.

### Doctrine Collections in definitions

You can use `ArrayCollection` (or any Doctrine `Collection`) directly in a definition. The factory
resolves any factories nested inside it:

```php
use Doctrine\Common\Collections\ArrayCollection;

Post::factory()->create([
    'tags' => new ArrayCollection([
        Tag::factory(),         // resolved to a Tag instance
        Tag::factory()->make(), // already an instance, used as-is
    ]),
]);
```

This is also how you set up plain many-to-many relationships — see the [Many-to-many row in the feature
table](#feature-compatibility).

### Domain-specific helper methods

Wrap `for()` / `state()` / `afterMaking()` calls in domain-specific methods that accept multiple input
types — entities, factories, or attribute arrays. Callers get a flexible API and you get a single place to
keep the relationship logic:

```php
class PostFactory extends DoctrineFactory
{
    public function forAuthor(User|UserFactory|array|null $author = null): static
    {
        $author ??= [];
        $author = is_array($author) ? User::factory()->make($author) : $author;

        return $this->for($author, 'author');
    }
}

// All four usages work
Post::factory()->forAuthor();
Post::factory()->forAuthor($user);
Post::factory()->forAuthor(User::factory());
Post::factory()->forAuthor(['name' => 'Alice']);
```

This is more flexible than the built-in magic `forUser()`, which only accepts an attribute array.

### Factory inheritance for entity hierarchies

When entities share an inheritance hierarchy, mirror it on the factories. An abstract base factory holds
the shared definition; subclasses extend and add their own pieces:

```php
abstract class ContentFactory extends DoctrineFactory
{
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'body'  => fake()->paragraphs(3, true),
        ];
    }
}

class PostFactory extends ContentFactory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            ...parent::definition(),
            'author' => User::factory(),
        ];
    }
}

class PageFactory extends ContentFactory
{
    protected $model = Page::class;

    public function definition(): array
    {
        return [
            ...parent::definition(),
            'slug' => fake()->slug(),
        ];
    }
}
```

### `EntityManager::refresh()` escape hatch

When the in-memory state diverges from the database — most commonly because a related entity's collection
was mutated after persistence — `EntityManager::refresh($entity)` re-fetches it from the database:

```php
use LaravelDoctrine\ORM\Facades\EntityManager;

$user = User::factory()->create();
Post::factory()->for($user)->count(3)->create();

EntityManager::refresh($user);
$user->getPosts(); // now reflects the 3 new posts
```

Manual sync via `afterMaking()` (above) avoids the round-trip but requires you to know which collections
to update. `refresh()` is the safer fallback when you don't.

# Development

## Setup

```bash
git clone git@github.com:stemble/laravel-doctrine-factory.git
cd laravel-doctrine-factory
composer install
```

## Running Tests

```bash
composer test
```

## Publishing new Versions

To publish a new version of the package, create a new tag and push it to the repository:

```bash
git tag vx.x.x
git push origin vx.x.x
```

Go to [Packagist](https://packagist.org/packages/stemble/laravel-doctrine-factory) and click on "Update" to
update the package.

## Explained Overrides

Most methods that this package overrides are documented next to an `@override` tag in the source, including
the rationale for the change. If you're working on the package itself, the doc blocks are the canonical
reference for *why* a given override exists.

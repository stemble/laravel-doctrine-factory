---
name: laravel-doctrine-factory
description: Build and work with Doctrine ORM entities using Laravel's modern factory API via the stemble/laravel-doctrine-factory package.
---

# Laravel Doctrine Factory

## When to use this skill

Use this skill when creating or modifying factories for Doctrine ORM entities in a Laravel application — including defining factories, writing states, setting up relationships, and handling bidirectional sync.

---

## Conventions

- Add the standard `HasFactory` trait to the entity — no Doctrine-specific version needed.
- Extend `Stemble\LaravelDoctrineFactory\DoctrineFactory` in the factory class.
- Place factory files under `database/factories/` mirroring the entity namespace below `App\` (e.g. `App\Entities\User` → `database/factories/Entities/UserFactory.php`).

---

## Defining a factory

`definition()` returns an attribute array. Keys that match constructor parameter names are passed to the constructor; remaining keys are written to private properties via reflection (setters are NOT called).

```php
use Stemble\LaravelDoctrineFactory\DoctrineFactory;

class UserFactory extends DoctrineFactory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name'  => fake()->name(), // constructor param
            'admin' => false,          // set via reflection
        ];
    }

    public function admin(): static
    {
        return $this->state(['admin' => true]);
    }
}
```

Required constructor parameters MUST be present in the definition (or passed at call time), or `MissingConstructorAttributesException` is thrown.

---

## make() vs create()

- **`make()`** — instantiates the entity and calls `EntityManager::persist()`. Nothing is written to the database yet. If any `flush()` is triggered later the entity will be inserted.
- **`create()`** — does everything `make()` does, then calls `EntityManager::flush()`.

Because `make()` registers entities in the identity map, related entities built with the factory chain are automatically flushed in a single `create()` call — no `cascade: ['persist']` annotations required.

```php
$user  = User::factory()->create();        // persists + flushes
$users = User::factory()->count(3)->create();
$draft = User::factory()->make();          // persists only (no flush yet)
```

---

## Relationships

`for()` and `has()` resolve to full entity instances — never foreign-key IDs.

The second argument is the **entity property name**, not a column or class name.

```php
// Post has property `User $author`
$post = Post::factory()->for(User::factory(), 'author')->create();

// Magic method: property name matches class basename → second arg can be omitted
$post = Post::factory()->forUser(User::factory())->create();

// has() — User has Collection $posts
$user = User::factory()->has(Post::factory()->count(3), 'posts')->create();
// or magic:
$user = User::factory()->hasPosts(3)->create();
```

### Doctrine Collections in definitions

Pass an `ArrayCollection` directly; nested factories inside it are resolved automatically:

```php
Post::factory()->create([
    'tags' => new ArrayCollection([
        Tag::factory(),         // resolved to Tag instance
        Tag::factory()->make(), // already an instance, used as-is
    ]),
]);
```

---

## Bidirectional relationships

Doctrine relationships are often bidirectional. The factory only sets the owning side. The inverse side (e.g. `$user->getPosts()`) will return an empty collection in memory until you flush and refresh.

**Fix with `afterMaking()` in `configure()`:**

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

Now `$user->getPosts()` contains the new post immediately — no flush or refresh needed.

---

## afterMaking() vs afterCreating()

- **`afterMaking()`** — runs before flush. Use for in-memory graph setup: bidirectional sync, attaching child entities that will be flushed together.
- **`afterCreating()`** — runs after flush. Use when you need a database-assigned ID before the next operation.

```php
// needs DB-assigned ID → afterCreating
public function withSlug(): static
{
    return $this->afterCreating(function (Post $post) {
        $post->setSlug($post->getId() . '-' . str($post->getTitle())->slug());
    });
}

// children flushed together with parent → afterMaking
public function withComments(int $count = 3): static
{
    return $this->afterMaking(function (Post $post) use ($count) {
        Comment::factory()->count($count)->for($post)->make();
    });
}
```

---

## Useful patterns

### Domain-specific helper methods

Wrap `for()` / `state()` / `afterMaking()` in methods that accept multiple input types:

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

Post::factory()->forAuthor();
Post::factory()->forAuthor($user);
Post::factory()->forAuthor(User::factory());
Post::factory()->forAuthor(['name' => 'Alice']);
```

### Factory inheritance for entity hierarchies

Mirror entity inheritance on factories — abstract base holds the shared definition:

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
        return [...parent::definition(), 'author' => User::factory()];
    }
}
```

### EntityManager::refresh() escape hatch

When in-memory state diverges from the database after persistence:

```php
use LaravelDoctrine\ORM\Facades\EntityManager;

$user = User::factory()->create();
Post::factory()->for($user)->count(3)->create();

EntityManager::refresh($user);
$user->getPosts(); // now reflects the 3 new posts
```

---

## Reflection bypasses setters

Non-constructor attributes are written directly to private properties — setters are not called. If a setter has side effects (validation, eventing, collection sync), either pass the value through the constructor or call the setter from `afterMaking()`.

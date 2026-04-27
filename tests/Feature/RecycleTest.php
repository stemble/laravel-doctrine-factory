<?php

namespace Tests\Feature;

use LaravelDoctrine\ORM\Facades\EntityManager;
use Stemble\LaravelDoctrineFactory\DoctrineFactory;
use Workbench\App\Entities\Comment;
use Workbench\App\Entities\Post;
use Workbench\App\Entities\User;

/**
 * Doctrine writes through its own connection, so Laravel's RefreshDatabase
 * transaction doesn't roll them back between tests. Absolute row counts leak
 * across tests — assert on the delta around the action under test instead.
 */
function countUsers(): int
{
    return EntityManager::getRepository(User::class)->count([]);
}

/**
 * Covers
 */
covers(DoctrineFactory::class);

/**
 * ---------------------------------------------------------------------------------
 * Recycling Existing Models
 * ---------------------------------------------------------------------------------
 *
 * @see https://laravel.com/docs/11.x/eloquent-factories#recycling-an-existing-model-for-relationships
 */
describe('recycle', function () {
    test('the recycled entity is reused inside a chained factory definition', function () {
        $user = User::factory()->create();

        // Comment::factory() -> Post::factory() -> User::factory() (in Post's definition).
        // Without recycle a fresh User would be created for the Post.
        $comment = Comment::factory()
            ->recycle($user)
            ->create();

        expect($comment)->getPost()->getUser()->toBe($user);
    });

    test('the recycled entity is reused through a for() chain', function () {
        $user = User::factory()->create();

        $post = Post::factory()
            ->recycle($user)
            ->forUser()
            ->make();

        expect($post)->getUser()->toBe($user);
    });

    test('the recycled entity is reused through a has() chain', function () {
        $user = User::factory()->create();

        $before = countUsers();

        $post = Post::factory()
            ->recycle($user)
            ->forUser()
            ->create();

        // The chained user is recycled, not freshly created.
        expect($post)->getUser()->toBe($user);

        // And no extra users were inserted.
        expect(countUsers() - $before)->toBe(0);
    });

    test('without recycle, a fresh entity is created for the chain', function () {
        $existing = User::factory()->create();

        $before = countUsers();

        $comment = Comment::factory()->create();

        expect($comment->getPost()->getUser())->not->toBe($existing);

        // One fresh user was created for the Comment -> Post -> User chain.
        expect(countUsers() - $before)->toBe(1);
    });

    test('recycle accepts a Collection of candidate entities', function () {
        $alice = User::factory()->create(['name' => 'Alice']);
        $bob = User::factory()->create(['name' => 'Bob']);

        $candidates = collect([$alice, $bob]);

        $post = Post::factory()
            ->recycle($candidates)
            ->forUser()
            ->make();

        // The recycled user must be one of the candidates — not a freshly built one.
        expect($post->getUser())->toBeIn([$alice, $bob]);
    });

    test('recycled entity is shared across multiple created entities', function () {
        $user = User::factory()->create();

        $before = countUsers();

        $posts = Post::factory()
            ->count(3)
            ->recycle($user)
            ->create();

        expect($posts)->toHaveCount(3);

        $posts->each(function (Post $post) use ($user) {
            expect($post)->getUser()->toBe($user);
        });

        // No new users — all three posts share the recycled one.
        expect(countUsers() - $before)->toBe(0);
    });
})->note('https://laravel.com/docs/11.x/eloquent-factories#recycling-an-existing-model-for-relationships');

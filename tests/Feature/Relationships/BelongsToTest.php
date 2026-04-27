<?php


namespace Tests\Feature\Relationships;

use LaravelDoctrine\ORM\Facades\EntityManager;
use Stemble\LaravelDoctrineFactory\DoctrineBelongsToRelationship;
use Stemble\LaravelDoctrineFactory\DoctrineFactory;
use Workbench\App\Entities\Comment;
use Workbench\App\Entities\Post;
use Workbench\App\Entities\User;

describe('BelongsTo Relationships', function () {
    test("the parent can be an instance", function () {
        $user = User::factory()->make();

        $post = Post::factory()
            ->for($user)
            ->make();

        expect($post->getUser())->toBe($user);
    });

    test("the parent can be a factory", function () {
        $post = Post::factory()
            ->for(User::factory())
            ->make();

        expect($post->getUser())->toBeInstanceOf(User::class);
    });

    test("the parent factory's state can be changed", function () {
        $post = Post::factory()
            ->for(User::factory()->state(['name' => 'John Doe']))
            ->make();

        expect($post->getUser()->getName())->toBe('John Doe');
    });

    test("a shared parent factory resolves to the same instance across multiple children (make)", function () {
        $posts = Post::factory()
            ->for(User::factory())
            ->count(3)
            ->make();

        expect($posts)->toHaveCount(3);

        $firstUser = $posts->first()->getUser();
        expect($firstUser)->toBeInstanceOf(User::class);

        $posts->each(function ($post) use ($firstUser) {
            expect($post->getUser())->toBe($firstUser);
        });
    });

    test("a shared parent factory is persisted only once across multiple children (create)", function () {
        $posts = Post::factory()
            ->for(User::factory())
            ->count(3)
            ->create();

        expect($posts)->toHaveCount(3);

        $firstUserId = $posts->first()->getUser()->getId();
        expect($firstUserId)->not->toBeNull();

        $posts->each(function ($post) use ($firstUserId) {
            expect($post->getUser()->getId())->toBe($firstUserId);
        });
    });

    test("the relationship name can be specified", function () {
        $author = User::factory()->create();
        $secondaryAuthor = User::factory()->create();

        $post = Post::factory()
            ->for($author)
            ->for($secondaryAuthor, 'secondaryAuthor')
            ->make();

        expect($post->getUser())->toEqual($author)
            ->and($post->getSecondaryAuthor())->toEqual($secondaryAuthor);
    });

    describe("magic methods can infer the relationship", function () {
        test("calling without arguments", function () {
            $post = Post::factory()
                ->forUser()
                ->make();

            expect($post->getUser())->toBeInstanceOf(User::class);
        });

        test("calling with array attributes", function () {
            $name = "Donkey Kong";

            $post = Post::factory()->forUser(["name" => $name])->make();

            expect($post->getUser())->getName()->toEqual($name);
        });
    })->note('https://laravel.com/docs/11.x/eloquent-factories#belongs-to-relationships-using-magic-methods')
        ->done(issue: 5);

    it("creates all records", function () {
        $post = Post::factory()
            ->for(User::factory())
            ->create();

        EntityManager::refresh($post);
        EntityManager::refresh($post->getUser());

        expect(EntityManager::find(Post::class, $post->getId()))->not->toBeNull();
        expect(EntityManager::find(User::class, $post->getUser()->getId()))->not->toBeNull();
    });

    it("entities created by BelongsTo relationship should get passed to constructor", function () {
        $comment = Comment::factory()
            // The Comment constructor requires a Post instance.
            ->for(Post::factory()->forUser())
            ->create([
                "body" => "Blah",
                "user" => User::factory()->create(),
            ]);

        expect($comment)->getBody()->toBe("Blah");
        expect($comment)->getPost()->toBeInstanceOf(Post::class);
    });

    it("entities made by BelongsTo relationship should get passed to constructor", function () {
        $comment = Comment::factory()
            // The Comment constructor requires a Post instance.
            ->for(Post::factory()->forUser())
            ->forUser()
            ->make([
                "body" => "Blah",
            ]);

        expect($comment)->getBody()->toBe("Blah");
        expect($comment)->getPost()->toBeInstanceOf(Post::class);
    });
})->note(note: 'https://laravel.com/docs/11.x/eloquent-factories#belongs-to-relationships')
    ->covers(DoctrineFactory::class, DoctrineBelongsToRelationship::class)
    ->done(issue: 10);

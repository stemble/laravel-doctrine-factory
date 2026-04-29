<?php

namespace Tests\Feature;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\ORMInvalidArgumentException;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Workbench\App\Entities\Post;
use Workbench\App\Entities\User;

describe('BelongsTo relationships', function () {
    test('it throws exception when persisting entity with unpersisted relationships', function () {
        expect(function () {
            $user = new User('John Doe');  // Unpersisted user

            $post = new Post;
            $post->setTitle('Test Post');
            $post->setUser($user);         // Setting unpersisted relationship
            $post->setSecondaryAuthor($user); // Another unpersisted relationship

            EntityManager::persist($post);
            EntityManager::flush();
        })->toThrow(
            ORMInvalidArgumentException::class,
            'not configured to cascade persist'
        );
    });

    test('it does not throw when the parent comes from a forX() chain', function () {
        Post::factory()->forUser()->create();
    })->throwsNoExceptions();

    test('it does not throw when an unpersisted parent entity is passed via attributes', function () {
        Post::factory()->create([
            'user' => User::factory()->make(),
        ]);
    })->throwsNoExceptions();

    test('it does not throw when the parent factory comes from definition()', function () {
        Post::factory()->create([]);
    })->throwsNoExceptions();
});

describe('HasMany relationships', function () {
    test('it throws exception when persisting entity with unpersisted relationships', function () {
        expect(function () {
            $user = new User('John Doe');  // Unpersisted user

            $post = new Post;
            $post->setTitle('Test Post');
            $user->getPosts()->add($post);

            EntityManager::persist($user);
            EntityManager::flush();
        })->toThrow(
            ORMInvalidArgumentException::class,
            'not configured to cascade persist'
        );
    });

    test('it does not throw exception when creating with instances', function () {
        // Works
        User::factory()->create([
            'children' => new ArrayCollection([User::factory()->make()]),
        ]);
    });

    test('it does not throw exception when creating with factories', function () {
        // Works
        User::factory()->create([
            'children' => new ArrayCollection([User::factory()]),
        ]);
    });
});

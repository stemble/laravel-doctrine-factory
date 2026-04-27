<?php

namespace Tests\Feature;

use Stemble\LaravelDoctrineFactory\DoctrineFactory;
use Workbench\App\Entities\User;

/**
 * Covers
 */
covers(DoctrineFactory::class);

/**
 * ---------------------------------------------------------------------------------
 * Factory Callbacks — afterCreating
 * ---------------------------------------------------------------------------------
 *
 * @see https://laravel.com/docs/11.x/eloquent-factories#factory-callbacks
 */
describe('afterCreating', function () {
    test('the callback runs once per created entity', function () {
        $seen = [];

        User::factory()
            ->afterCreating(function (User $user) use (&$seen) {
                $seen[] = $user;
            })
            ->create();

        expect($seen)->toHaveCount(1)
            ->and($seen[0])->toBeInstanceOf(User::class);
    });

    test('the callback receives the created instance with an id', function () {
        $name = 'Inigo Montoya';
        /** @var User|null $captured */
        $captured = null;

        $user = User::factory()
            ->afterCreating(function (User $user) use (&$captured) {
                $captured = $user;
            })
            ->create(['name' => $name]);

        expect($captured)->toBe($user)
            ->getName()->toBe($name)
            ->getId()->not->toBeNull();
    });

    test('the callback runs once per entity when creating many', function () {
        $count = 0;

        User::factory()
            ->count(3)
            ->afterCreating(function () use (&$count) {
                $count++;
            })
            ->create();

        expect($count)->toBe(3);
    });

    test('the callback does not run when calling make()', function () {
        $count = 0;

        User::factory()
            ->afterCreating(function () use (&$count) {
                $count++;
            })
            ->make();

        expect($count)->toBe(0);
    });

    test('multiple afterCreating callbacks all run', function () {
        $first = false;
        $second = false;

        User::factory()
            ->afterCreating(function () use (&$first) {
                $first = true;
            })
            ->afterCreating(function () use (&$second) {
                $second = true;
            })
            ->create();

        expect($first)->toBeTrue()
            ->and($second)->toBeTrue();
    });
})->note('https://laravel.com/docs/11.x/eloquent-factories#factory-callbacks');

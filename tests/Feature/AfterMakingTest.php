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
 * Factory Callbacks — afterMaking
 * ---------------------------------------------------------------------------------
 *
 * @see https://laravel.com/docs/11.x/eloquent-factories#factory-callbacks
 */
describe('afterMaking', function () {
    test('the callback runs once per made entity', function () {
        $seen = [];

        User::factory()
            ->afterMaking(function (User $user) use (&$seen) {
                $seen[] = $user;
            })
            ->make();

        expect($seen)->toHaveCount(1)
            ->and($seen[0])->toBeInstanceOf(User::class);
    });

    test('the callback receives the made instance', function () {
        $name = 'Inigo Montoya';
        $captured = null;

        $user = User::factory()
            ->afterMaking(function (User $user) use (&$captured) {
                $captured = $user;
            })
            ->make(['name' => $name]);

        expect($captured)->toBe($user)
            ->getName()->toBe($name);
    });

    test('the callback runs once per entity when making many', function () {
        $count = 0;

        User::factory()
            ->count(3)
            ->afterMaking(function () use (&$count) {
                $count++;
            })
            ->make();

        expect($count)->toBe(3);
    });

    test('the callback also runs when calling create()', function () {
        $count = 0;

        User::factory()
            ->afterMaking(function () use (&$count) {
                $count++;
            })
            ->create();

        expect($count)->toBe(1);
    });

    test('multiple afterMaking callbacks all run', function () {
        $first = false;
        $second = false;

        User::factory()
            ->afterMaking(function () use (&$first) {
                $first = true;
            })
            ->afterMaking(function () use (&$second) {
                $second = true;
            })
            ->make();

        expect($first)->toBeTrue()
            ->and($second)->toBeTrue();
    });
})->note('https://laravel.com/docs/11.x/eloquent-factories#factory-callbacks');

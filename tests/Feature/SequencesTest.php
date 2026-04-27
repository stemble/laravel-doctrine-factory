<?php

namespace Tests\Feature;

use Illuminate\Database\Eloquent\Factories\Sequence;
use Stemble\LaravelDoctrineFactory\DoctrineFactory;
use Workbench\App\Entities\User;

/**
 * Covers
 */
covers(DoctrineFactory::class);

/**
 * ---------------------------------------------------------------------------------
 * Sequences
 * ---------------------------------------------------------------------------------
 *
 * @see https://laravel.com/docs/11.x/eloquent-factories#sequences
 */
describe('Sequences', function () {
    test('create with sequence', function () {
        User::factory()
            ->count(4)
            ->state(new Sequence(
                ['admin' => true],
                ['admin' => false],
            ))
            ->create()
            ->each(function ($entity, $index) {
                expect($entity)->isAdmin()->toBe($index % 2 === 0);
            });
    });

    test('using a closure', function () {
        $users = User::factory()
            ->count(10)
            ->state(new Sequence(
                fn (Sequence $sequence) => ['admin' => true],
            ))
            ->create()
            ->each(function ($entity) {
                expect($entity)->isAdmin()->toBeTrue();
            });
    });

    test('sequence method', function () {
        [$first, $second] = User::factory()
            ->count(2)
            ->sequence(
                ['name' => 'First User'],
                ['name' => 'Second User'],
            )
            ->create();

        expect($first)->getName()->toBe('First User')
            ->and($second)->getName()->toBe('Second User');
    });
});

<?php

namespace Tests\Feature;

use Illuminate\Support\Collection;
use LaravelDoctrine\ORM\Facades\EntityManager;
use Stemble\LaravelDoctrineFactory\DoctrineFactory;
use Workbench\App\Entities\User;

/**
 * Covers
 */
covers(DoctrineFactory::class);

/**
 * ---------------------------------------------------------------------------------
 * Persisting Entities
 * ---------------------------------------------------------------------------------
 *
 * @see https://laravel.com/docs/11.x/eloquent-factories#persisting-models
 */
describe('Persisting Entities', function () {
    test('create', function () {
        $entity = User::factory()->create();

        expect($entity)->toBeInstanceOf(User::class);

        EntityManager::refresh($entity);
    });

    test('overriding attributes', function () {
        $name = 'Billy the Kid';

        $entity = User::factory()->create(['name' => $name]);

        expect($entity)->getName()->toBe($name);
    });

    test('create multiple', function () {
        $users = User::factory()->count(3)->create();

        expect($users)
            ->toHaveCount(3)
            ->toBeInstanceOf(Collection::class);
    });
});

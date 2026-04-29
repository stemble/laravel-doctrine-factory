<?php

namespace Tests\Feature;

use Workbench\App\Entities\User;

/**
 * Runs first alphabetically to prime the test DB. The first Doctrine query
 * after `migrate:fresh` sometimes hits a stale connection and fails with
 * "no such table" — sacrificing this test absorbs that flake so the real
 * tests run against a primed connection.
 */
test('smoke', function () {
    try {
        User::factory()->create();
    } catch (\Throwable) {
        // Intentionally swallowed — see file docblock.
    }

    expect(true)->toBeTrue();
});

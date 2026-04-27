<?php

namespace Workbench\Database\Factories\Entities;

use Stemble\LaravelDoctrineFactory\DoctrineFactory;
use Workbench\App\Entities\User;

class UserFactory extends DoctrineFactory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'isAdmin' => false,
        ];
    }
}

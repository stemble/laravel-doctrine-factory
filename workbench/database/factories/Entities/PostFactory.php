<?php

namespace Workbench\Database\Factories\Entities;

use Stemble\LaravelDoctrineFactory\DoctrineFactory;
use Workbench\App\Entities\Post;
use Workbench\App\Entities\User;

class PostFactory extends DoctrineFactory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'title' => fake()->name(),
            'published' => fake()->boolean(),
            'user' => User::factory(),
        ];
    }
}

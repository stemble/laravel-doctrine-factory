<?php

namespace Workbench\Database\Factories\Entities;

use Stemble\LaravelDoctrineFactory\DoctrineFactory;

use Workbench\App\Entities\Comment;
use Workbench\App\Entities\Post;

class CommentFactory extends DoctrineFactory
{
    protected $model = Comment::class;

    public function definition(): array
    {
        return [
            'post' => Post::factory(),
            'body' => fake()->text(),
            'user' => null,
        ];
    }
}

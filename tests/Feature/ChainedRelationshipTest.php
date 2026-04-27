<?php

namespace Tests\Feature;

use Workbench\App\Entities\Comment;
use Workbench\App\Entities\Post;
use Workbench\App\Entities\User;

test('it persists all entities before flushing the database', function () {
    // Comment -> Post -> User
    $comment = Comment::factory()->create();

    // Verify the comment was created with its chained relationships
    expect($comment)->toBeInstanceOf(Comment::class);
    expect($comment->getPost())->toBeInstanceOf(Post::class);
    expect($comment->getPost()->getUser())->toBeInstanceOf(User::class);

    // Verify all entities were persisted to the database
    expect($comment->getId())->not()->toBeNull();
    expect($comment->getPost()->getId())->not()->toBeNull();
    expect($comment->getPost()->getUser()->getId())->not()->toBeNull();
});

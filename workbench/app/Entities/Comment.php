<?php

namespace Workbench\App\Entities;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Entity, Table(name: 'comments')]
class Comment
{
    use HasFactory;

    #[Id, GeneratedValue, Column(type: 'integer')]
    protected $id;

    #[Column(type: 'text')]
    protected string $body;

    #[ManyToOne(targetEntity: User::class)]
    protected ?User $user = null;

    #[ManyToOne(targetEntity: Post::class)]
    protected Post $post;

    public function __construct(string $body, Post $post, ?User $user = null)
    {
        $this->body = $body;
        $this->user = $user;
        $this->post = $post;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getPost(): Post
    {
        return $this->post;
    }
}

<?php

namespace Workbench\App\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;

#[Entity, Table(name: 'users')]
class User
{
    use HasFactory;

    #[Id, GeneratedValue, Column(type: 'integer')]
    protected $id;

    #[Column(type: 'string')]
    protected $name;

    #[Column(type: 'boolean')]
    protected bool $admin = false;

    protected bool $uninitializedFlag;

    #[ManyToOne(targetEntity: User::class, inversedBy: 'children')]
    protected ?User $parent = null;

    #[OneToMany(targetEntity: Post::class, mappedBy: 'user')]
    protected Collection $posts;

    #[OneToMany(targetEntity: Post::class, mappedBy: 'secondaryAuthor')]
    protected Collection $secondaryPosts;

    #[OneToMany(targetEntity: User::class, mappedBy: 'parent', cascade: [])]
    protected Collection $children;

    /**
     * @param  $id
     */
    public function __construct(string $name)
    {
        $this->name = $name;
        $this->posts = new ArrayCollection;
        $this->secondaryPosts = new ArrayCollection;
        $this->children = new ArrayCollection;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function isAdmin(): bool
    {
        return $this->admin;
    }

    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function getSecondaryPosts(): Collection
    {
        return $this->secondaryPosts;
    }
}

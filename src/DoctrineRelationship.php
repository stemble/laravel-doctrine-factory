<?php

namespace Stemble\LaravelDoctrineFactory;

use Doctrine\Common\Collections\Collection;
use Illuminate\Database\Eloquent\Factories\Relationship;

class DoctrineRelationship extends Relationship
{
    /**
     * Create the child relationship for the given parent model.
     *
     * @param $parent
     * @return void
     */
    public function createFor($parent)
    {
        $reflectionClass = new \ReflectionClass(get_class($parent));
        $reflectionCollection = $reflectionClass->getProperty($this->relationship);
        $reflectionCollection->setAccessible(true);
        /** @var Collection $relationship */
        $relationship = $reflectionCollection->getValue($parent);

        $output = $this->factory->for($parent)->create();

        $output->each(function ($entity) use ($relationship) {
            $relationship->add($entity);
        });
    }
}
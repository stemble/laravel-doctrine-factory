<?php

namespace Stemble\LaravelDoctrineFactory;

use Illuminate\Database\Eloquent\Factories\BelongsToRelationship;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Doctrine handles BelongsTo relationships differently than Eloquent.
 *
 * This class overrides the default BelongsToRelationship class to work
 * with entity references instead of foreign keys.
 */
class DoctrineBelongsToRelationship extends BelongsToRelationship
{
    /**
     * Get the parent model attributes and resolvers for the given child model.
     *
     * @override Remove the type `Model` type constraint for the $model parameter.
     * @override $relationship will never be a `MorphTo` instance.
     * @override Doctrine Entities don't expose foreign keys, so the $relationship name is what's assigned.
     *
     * @return array
     */
    public function attributesFor($model)
    {
        $key = Str::plural(Str::camel(class_basename($model)));

        return [
            $this->relationship => $this->resolver($key),
        ];
    }

    /**
     * Get the deferred resolver for this relationship's parent ID.
     *
     * @override Doctrine Entities don't expose foreign keys. We just
     * return the whole instance and the EntityManager will handle
     * mapping those to foreign keys.
     *
     * @param  string|null  $key
     * @return \Closure
     */
    protected function resolver($key)
    {
        return function () {
            if (! $this->resolved) {
                $instance = $this->factory instanceof Factory
                    ? ($this->factory->getRandomRecycledModel($this->factory->modelName()) ?? $this->factory->create())
                    : $this->factory;

                return $this->resolved = $instance;
            }

            return $this->resolved;
        };
    }
}

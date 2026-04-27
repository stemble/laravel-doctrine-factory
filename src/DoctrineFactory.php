<?php

namespace Stemble\LaravelDoctrineFactory;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection as DoctrineCollection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use LaravelDoctrine\ORM\Facades\EntityManager;
use ReflectionClass;
use ReflectionException;

/**
 * @template TModel
 *
 * @method $this trashed()
 */
abstract class DoctrineFactory extends Factory
{
    /**
     * Create a collection of models and persist them to the database.
     *
     * @override This method flips the conditional around. The original checked if the result was an
     * instance of Model. With Doctrine there is no common superclass, so we instead check if the
     * result is a Collection.
     *
     * @param  (callable(array<string, mixed>): array<string, mixed>)|array<string, mixed>  $attributes
     * @param  object|null  $parent
     * @return \Illuminate\Database\Eloquent\Collection<int, TModel>|TModel
     */
    public function create($attributes = [], ?Model $parent = null)
    {
        if (! empty($attributes)) {
            return $this->state($attributes)->create([], $parent);
        }

        $results = $this->make($attributes, $parent);

        if ($results instanceof Collection) {
            $this->store($results);

            $this->callAfterCreating($results, $parent);
        } else {
            $this->store(collect([$results]));

            $this->callAfterCreating(collect([$results]), $parent);
        }

        return $results;
    }

    /**
     * Create a collection of models.
     *
     * @override Two changes from the Eloquent version:
     *   1. Each instance is passed to `EntityManager::persist()` so it is tracked
     *      by the UnitOfWork. Nothing is written to the database — that requires
     *      `EntityManager::flush()` (which `create()` calls). This is what allows
     *      related entities pulled in by the factory chain to be flushed together
     *      without `cascade: ['persist']` on every association.
     *   2. Collections are constructed via `collect()` directly instead of
     *      `newModel()->newCollection()`, since Doctrine entities don't share a
     *      base class that exposes `newCollection()`.
     *
     * @param  (callable(array<string, mixed>): array<string, mixed>)|array<string, mixed>  $attributes
     * @return Collection<int, TModel>|TModel
     */
    public function make($attributes = [], ?Model $parent = null)
    {
        if (! empty($attributes)) {
            return $this->state($attributes)->make([], $parent);
        }

        if ($this->count === null) {
            return tap($this->makeInstance($parent), function ($instance) {
                $this->blindlyAttemptToPersist($instance);
                $this->callAfterMaking(collect([$instance]));
            });
        }

        if ($this->count < 1) {
            /**
             * @modified Directly calls `collect` instead of doing $this->newModel()->newCollection()
             */
            return collect(); // Re
        }

        /**
         * @modified Directly calls `collect` instead of doing $this->newModel()->newCollection()
         */
        $instances = collect(array_map(function () use ($parent) {
            return $this->blindlyAttemptToPersist($this->makeInstance($parent));
        }, range(1, $this->count)));

        $this->callAfterMaking($instances);

        return $instances;
    }

    /**
     * Get a new model instance.
     *
     * @override Eloquent model constructors have a consistent API, but that is not
     * necessarily the case for Doctrine entities. This method is overridden to
     * use reflection to create a new instance without using the constructor, and
     * then set the properties directly–even if they are private.
     *
     * @param  array<string, mixed>  $attributes
     * @return TModel|Collection<TModel>
     *
     * @throws MissingConstructorAttributesException|ReflectionException
     */
    public function newModel(array $attributes = [])
    {
        $reflectionClass = new ReflectionClass($this->modelName());

        // Initialize object using the constructor
        $constructorArgs = [];

        if ($constructor = $reflectionClass->getConstructor()) {
            $params = $constructor->getParameters();

            foreach ($params as $param) {
                if (array_key_exists($param->getName(), $attributes)) {
                    $constructorArgs[$param->getName()] = $attributes[$param->getName()];
                    unset($attributes[$param->getName()]);
                } elseif (! $param->isOptional()) {
                    throw new MissingConstructorAttributesException($this, $param);
                }
            }
        }

        $instance = $reflectionClass->newInstanceArgs($constructorArgs);

        // Update every non-constructor attribute directly
        foreach ($attributes as $attribute => $value) {
            if ($reflectionClass->hasProperty($attribute)) {
                $reflectionProperty = $reflectionClass->getProperty($attribute);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($instance, $value);
            }
        }

        return $instance;
    }

    /**
     * Create the parent relationship resolvers (as deferred Closures).
     *
     * @override Instead of instantiating a child model to create the parent, simply
     * pass the class name. This is enough for DoctrineBelongsToRelationship to create
     * the parent.
     *
     * @return array
     */
    protected function parentResolvers()
    {
        $model = $this->modelName();

        return $this->for->map(function (DoctrineBelongsToRelationship $for) use ($model) {
            return $for->recycle($this->recycle)->attributesFor($model);
        })->collapse()->all();
    }

    /**
     * Set the connection name on the results and store them.
     *
     * @override This method is overridden to use the `EntityManager` facade to persist the models
     * rather than the `Model::create` method that is used in the Eloquent factories.
     *
     * @param  Collection<int, TModel>  $results
     * @return void
     */
    protected function store(Collection $results)
    {
        $results->each(function ($model) {
            // Blindly attempt to persist everything. Will ignore invalid attempts.
            $reflection = new ReflectionClass($model);
            foreach ($reflection->getProperties() as $property) {
                $property->setAccessible(true);
                if ($property->isInitialized($model)) {
                    $property->getValue($model) ?? null;
                }
            }

            $this->createChildren($model);
        });

        EntityManager::flush();
    }

    /**
     * Create the children for the given model.
     *
     * @return void
     */
    protected function createChildren($model)
    {
        $this->has->each(function ($has) use ($model) {
            $has->recycle($this->recycle)->createFor($model);
        });
    }

    /**
     * Define a parent relationship for the model.
     *
     * @override This method is overridden to add an instance of `DoctrineBelongsToRelationship`
     * to the `for` property, rather then the `BelongsToRelationship` class that is used in the
     * Eloquent factories.
     *
     * @param  Factory|Model  $factory
     * @param  string|null  $relationship
     * @return static
     */
    public function for($factory, $relationship = null)
    {
        return $this->newInstance(['for' => $this->for->concat([new DoctrineBelongsToRelationship(
            $factory,
            $relationship ?? Str::camel(class_basename(
                $factory instanceof Factory ? $factory->modelName() : $factory
            ))
        )])]);
    }

    /**
     * Define a child relationship for the model.
     *
     * @param  DoctrineFactory  $factory
     * @param  string|null  $relationship
     */
    public function has($factory, $relationship = null): static
    {
        $guessRelationship = Str::plural($this->guessRelationship($factory->modelName()));
        $doctrineRelationship = new DoctrineRelationship(
            $factory,
            $relationship ?? $guessRelationship
        );

        return $this->newInstance([
            'has' => $this->has->concat([$doctrineRelationship]),
        ]);
    }

    /**
     * Proxy dynamic factory methods onto their proper methods.
     *
     * @override The original relies on specific Eloquent model information
     * that we do not have on Doctrine Entities.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (! Str::startsWith($method, ['for', 'has'])) {
            return parent::__call($method, $parameters);
        }

        $relationship = Str::camel(Str::substr($method, 3));

        // TODO: This will not support nested Entities
        $factoryName = static::$namespace.'Entities\\'.Str::singular(Str::studly($relationship)).'Factory';
        $factory = new $factoryName;

        if (Str::startsWith($method, 'for')) {
            return $this->for($factory->state($parameters[0] ?? []), $relationship);
        }

        if (Str::startsWith($method, 'has')) {
            return $this->has(
                $factory
                    ->count(is_numeric($parameters[0] ?? null) ? $parameters[0] : 1)
                    ->state((is_callable($parameters[0] ?? null) || is_array($parameters[0] ?? null)) ? $parameters[0] : ($parameters[1] ?? [])),
                $relationship
            );
        }
    }

    /**
     * Returns true if the given object is a Doctrine Entity.
     *
     * @param  mixed  $object
     * @return bool
     */
    private function blindlyAttemptToPersist($object)
    {
        try {
            if ($object instanceof DoctrineCollection) {
                $objects = $object;
            } else {
                $objects = [$object];
            }

            foreach ($objects as $obj) {
                EntityManager::persist($obj);
            }
        } catch (\Exception $e) {
        }

        return $object;
    }

    /**
     * @overrides The original implementation to return the whole object instead of it's key.
     *
     * @return array
     */
    protected function expandAttributes(array $definition)
    {
        return collect($definition)
            ->map($evaluateRelations = function ($attribute) {
                /**
                 * @modified Removed `getKey` calls on recycled models.
                 */
                if ($attribute instanceof self) {
                    return $this->getRandomRecycledModel($attribute->modelName())
                        ?? $attribute->recycle($this->recycle)->make();
                }

                /**
                 * @modified Removed path: `elseif ($attribute instanceof Model)`
                 */

                /**
                 * @modified Resolve factories inside the collections.
                 */
                if ($attribute instanceof DoctrineCollection) {
                    $newCollection = new ArrayCollection;
                    foreach ($attribute as $entityOrFactory) {
                        if ($entityOrFactory instanceof self) {
                            $entity = $this->getRandomRecycledModel($entityOrFactory->modelName()) ?? $entityOrFactory->recycle($this->recycle)->make();

                            $newCollection->add($entity);
                        } else {
                            $newCollection->add($entityOrFactory);
                        }
                    }

                    return $newCollection;
                }

                return $attribute;
            })
            ->map(function ($attribute, $key) use (&$definition, $evaluateRelations) {
                if (is_callable($attribute) && ! is_string($attribute) && ! is_array($attribute)) {
                    $attribute = $attribute($definition);
                }
                $attribute = $evaluateRelations($attribute);
                $definition[$key] = $attribute;

                return $attribute;
            })
            ->all();
    }
}

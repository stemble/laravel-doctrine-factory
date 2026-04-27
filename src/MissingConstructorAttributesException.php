<?php

namespace Stemble\LaravelDoctrineFactory;

class MissingConstructorAttributesException extends \Exception
{
    public function __construct(
        DoctrineFactory      $factory,
        \ReflectionParameter $parameter,
    )
    {
        $factoryName = class_basename($factory);
        $paramName = $parameter->getName();
        $paramType = $parameter->getType();


        parent::__construct(
            "$factoryName is missing attribute for required constructor parameter: $paramType $$paramName"
        );
    }
}
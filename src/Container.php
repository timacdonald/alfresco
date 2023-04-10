<?php

namespace Alfresco;

use Traversable;

class Container
{
    /**
     * @param  array<class-string, (callable(Container): mixed)>  $bindings
     */
    public function __construct(public array $bindings)
    {
        //
    }

    /**
     * @template T
     *
     * @param  class-string<T>  $class
     * @param  array<int, mixed>|Traversable<int, mixed>  $arguments
     * @return T
     */
    public function make(string $class, array|Traversable $arguments = []): mixed
    {
        if (array_key_exists($class, $this->bindings)) {
            return $this->bindings[$class]($this, ...$arguments);
        }

        if (method_exists($class, 'resolve')) {
            return $class::resolve($this, ...$arguments);
        }

        return new $class(...$arguments);
    }
}

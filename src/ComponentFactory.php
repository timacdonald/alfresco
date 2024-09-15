<?php

namespace Alfresco;

use Alfresco\Contracts\Slotable;
use Closure;
use Illuminate\Container\Container;
use Illuminate\Support\Collection;
use ReflectionFunction;
use ReflectionParameter;
use Stringable;

class ComponentFactory
{
    /**
     * The component resolver cache.
     *
     * @var array<string, \Closure>
     */
    protected array $resolverCache = [];

    /**
     * Create a new instance.
     */
    public function __construct(
        protected Configuration $config,
        protected Translation $translation,
        protected Container $container,
    ) {
        //
    }

    /**
     * Render a HTML tag.
     *
     * @param  array<string, string|bool|array<int, string>>  $attributes
     */
    public function tag(
        string $as,
        array $attributes = [],
        string|Stringable $before = '',
        string|Stringable $after = '',
        ?Slotable $slot = null
    ): HtmlTag {
        return new HtmlTag($as, $attributes, $before, $after, $slot);
    }

    /**
     * Render inline text.
     */
    public function inlineText(string $before = '', string $after = ''): Wrapper
    {
        return $this->wrapper(
            before: ' '.ltrim($before, ' '),
            after: $after,
        );
    }

    public function wrapper(string|Stringable $before = '', string|Stringable $after = '', ?Slotable $slot = null): Wrapper
    {
        return new Wrapper($before, $after, $slot);
    }

    /**
     * Render a component.
     *
     * @param  array<string, mixed>  $data
     */
    public function component(string $path, array $data = []): Slotable
    {
        return with($this->resolve($path, $data), function (Slotable|string $component) {
            return $component instanceof Slotable
                ? $component
                : new HtmlString($component);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function resolve(string $path, array $data): Slotable|string
    {
        return $this->container->call($this->resolver($path), $data);
    }

    /**
     * @return Collection<int, ReflectionParameter>
     */
    protected function requiredParameters(Closure $resolver): Collection
    {
        return $this->parametersFor($resolver)->reject->isOptional();
    }

    /**
     * @return Collection<int, ReflectionParameter>
     */
    protected function parametersFor(Closure $resolver): Collection
    {
        return collect((new ReflectionFunction($resolver))->getParameters());
    }

    protected function resolver(string $path): Closure
    {
        return $this->resolverCache[$path] ??= (static fn (string $__path) => require_once $__path)("{$this->config->get('component_directory')}/{$path}.php");
    }
}

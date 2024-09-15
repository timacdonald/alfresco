<?php

namespace Alfresco;

use Alfresco\Contracts\Slotable;
use Closure;
use Illuminate\Support\Collection;
use ReflectionFunction;
use ReflectionParameter;
use RuntimeException;
use Stringable;

class ComponentFactory
{
    protected array $resolverCache = [];

    public function __construct(
        protected Configuration $config,
        protected Translation $translation
    ) {
        //
    }

    /**
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
        return with($this->resolver($path), fn (Closure $resolver) => $resolver(
            ...collect($data)
                ->merge($this->defaultArguments())
                ->only($this->parametersFor($resolver)->map->name)
                ->pipe(function (Collection $arguments) use ($path, $resolver) {
                    if ($this->requiredParameters($resolver)->map->name->diff($arguments->keys())->isEmpty()) {
                        return $arguments;
                    }

                    throw new RuntimeException(
                        "Missing required argument(s) [{$this->requiredParameters($resolver)->map->name->diff($arguments->keys())->implode(', ')}] for component [{$path}]."
                    );
                })
        ));
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

    /**
     * @return array{ render: ComponentFactory, translate: (Closure(string): string) }
     */
    protected function defaultArguments(): array
    {
        return [
            'render' => $this,
            'translate' => $this->translation->get(...),
        ];
    }
}

<?php

namespace Alfresco;

use Alfresco\Contracts\Slotable;
use Closure;
use Illuminate\Config\Repository as Configuration;
use Illuminate\Container\Container;
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
        protected Highlighter $highlighter,
        protected Replacer $replacer,
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
     * Render the given code snippet.
     */
    public function codeSnippet(string $snippet, string $language)
    {
        return array_reduce([
            $this->replacer->handle(...),
            $this->highlighter->handle(...),
        ], fn ($snippet, $f) => $f($snippet, $language), $snippet);
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

    /**
     * Render a component wrapper.
     */
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
        return with($this->resolve($path, $data), fn (Slotable|string $component) => $component instanceof Slotable
            ? $component
            : new HtmlString($component));
    }

    /**
     * Resolve the component.
     *
     * @param  array<string, mixed>  $data
     */
    protected function resolve(string $path, array $data): Slotable|string
    {
        return $this->container->call($this->resolver($path), $data);
    }

    /**
     * The component resolver.
     */
    protected function resolver(string $path): Closure
    {
        return $this->resolverCache[$path] ??= (static fn (string $__path) => require_once $__path)(
            "{$this->config->get('component_directory')}/{$path}.php"
        );
    }
}

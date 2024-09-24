<?php

namespace Alfresco\Render;

use Alfresco\Contracts\Slotable;
use Alfresco\Highlighter;
use Alfresco\Render\HtmlString;
use Alfresco\Render\HtmlTag;
use Alfresco\Render\Replacer;
use Alfresco\Translation;
use Alfresco\Render\Wrapper;
use Closure;
use Illuminate\Config\Repository as Configuration;
use Illuminate\Container\Container;
use Stringable;

class Factory
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
     * Make a HTML string.
     */
    public function html(string $content): HtmlString
    {
        return new HtmlString($content);
    }

    /**
     * Make a HTML tag.
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
     * Make a code snippet.
     */
    public function codeSnippet(string $snippet, string $language): string
    {
        return array_reduce([
            $this->replacer->handle(...),
            $this->highlighter->handle(...),
        ], fn ($snippet, $f) => $f($snippet, $language), $snippet);
    }

    /**
     * Make inline text.
     */
    public function inlineText(string $before = '', string $after = ''): Wrapper
    {
        return $this->wrapper(
            before: ' '.ltrim($before, ' '),
            after: $after,
        );
    }

    /**
     * Make a wrapper.
     */
    public function wrapper(string|Stringable $before = '', string|Stringable $after = '', ?Slotable $slot = null): Wrapper
    {
        return new Wrapper($before, $after, $slot);
    }

    /**
     * Make a component.
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

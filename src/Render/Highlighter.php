<?php

declare(strict_types=1);

namespace Alfresco\Render;

use Spatie\ShikiPhp\Shiki;
use Illuminate\Support\Collection;
use function Safe\file_get_contents;
use function Safe\file_put_contents;
use Symfony\Component\Finder\Finder;
use Illuminate\Config\Repository as Configuration;

class Highlighter
{
    /**
     * The cache of already highlighted files.
     *
     * @var Collection<int, string>
     */
    protected Collection $filesCache;

    /**
     * Create a new instance.
     */
    public function __construct(
        protected Shiki $shiki,
        protected Configuration $config,
        protected Finder $finder,
    ) {
        //
    }

    /**
     * Highlight the given code snippet.
     */
    public function handle(string $code, string $language): string
    {
        if ($this->files()->contains($path = $this->path($code, $language))) {
            return file_get_contents($path);
        }

        return tap($this->shiki->highlightCode($code, match ($language) {
            'php' => 'blade',
            'apache-conf' => 'apache',
            'nginx-conf' => 'nginx',
            default => $language,
        }), fn ($content) => file_put_contents($path, $content));
    }

    /**
     * The cache path for the given code snippet.
     */
    protected function path(string $code, string $language): string
    {
        return "{$this->config->get('cache_directory')}/{$this->hash($code, $language)}-highlight.{$language}.html";
    }

    /**
     * The hash for the given code snippet.
     */
    protected function hash(string $code, string $language): string
    {
        return hash('xxh128', "{$language}\n{$code}");
    }

    /**
     * The cached highlighted files.
     *
     * @return Collection<int, string>
     */
    protected function files(): Collection
    {
        return $this->filesCache ??= collect($this->finder
            ->in($this->config->get('cache_directory'))
            ->files()
            ->depth(0)
            ->name('*-highlight.*.html')
            ->getIterator())->keys();
    }
}

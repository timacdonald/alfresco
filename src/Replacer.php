<?php

namespace Alfresco;

use Illuminate\Config\Repository as Configuration;
use Illuminate\Support\Collection;
use Symfony\Component\Finder\Finder;

use function Safe\file_get_contents;
use function Safe\file_put_contents;

class Replacer
{
    /**
     * The cache of already highlighted files.
     */
    protected Collection $filesCache;

    /**
     * Create a new instance.
     */
    public function __construct(
        protected Configuration $config,
        protected Output $output,
        protected Finder $finder,
    ) {
        //
    }

    /**
     * Replace the given code snippet.
     */
    public function handle(string $code, string $language): string
    {
        if ($this->files()->doesntContain($path = $this->originalPath($code, $language))) {
            file_put_contents($path, $code);
        }

        if ($this->files()->contains($path = $this->replacementPath($code, $language))) {
            return file_get_contents($path);
        }

        return $code;
    }

    /**
     * The cache path for the original code snippet.
     */
    protected function originalPath(string $code, string $language): string
    {
        return "{$this->config->get('cache_directory')}/{$this->hash($code, $language)}-original.{$language}";
    }

    /**
     * The cache path for replacement code snippet.
     */
    protected function replacementPath(string $code, string $language): string
    {
        // TODO. these should not be  in the cache path and instead in a commited repository.
        // Maybe we should commit all code snippets to track what is modified?
        return "{$this->config->get('cache_directory')}/{$this->hash($code, $language)}-replacement.{$language}";
    }

    /**
     * The hash for the given code snippet.
     */
    protected function hash(string $code, string $language): string
    {
        return hash('xxh128', "{$language}\n{$code}");
    }

    protected function files(): Collection
    {
        return $this->filesCache ??= collect($this->finder
            ->in($this->config->get('cache_directory'))
            ->files()
            ->depth(0)
            ->name(['*-replacement.*', '*-original.*'])
            ->getIterator())->keys();
    }
}

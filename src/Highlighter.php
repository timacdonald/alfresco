<?php

namespace Alfresco;

use Illuminate\Config\Repository as Configuration;
use Spatie\ShikiPhp\Shiki;

use function Safe\file_get_contents;
use function Safe\file_put_contents;

class Highlighter
{
    /**
     * Create a new instance.
     */
    public function __construct(
        protected Shiki $shiki,
        protected Configuration $config,
    ) {
        //
    }

    /**
     * Highlight the given code snippet.
     */
    public function handle(string $code, string $language): string
    {
        if (file_exists($path = $this->path($code, $language))) {
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
        return "{$this->config->get('cache_directory')}/{$this->hash($code, $language)}.{$language}.html";
    }

    /**
     * The hash for the given code snippet.
     */
    protected function hash(string $code, string $language): string
    {
        return hash('xxh128', "{$language}\n{$code}");
    }
}

<?php

namespace Alfresco;

use Spatie\ShikiPhp\Shiki;

use function Safe\file_get_contents;
use function Safe\file_put_contents;

class Highlighter
{
    public function __construct(protected Shiki $shiki)
    {
        //
    }

    /**
     * @todo Check that these mappings all make sense.
     */
    public function highlight(string $code, string $language): string
    {
        // TODO inject path. Quick hack
        $hash = hash('xxh128', $code);

        if (file_exists($path = __DIR__."/../build/cache/{$hash}.{$language}.html")) {
            return file_get_contents($path);
        }

        $content = $this->shiki->highlightCode($code, match ($language) {
            'php' => 'blade',
            'apache-conf' => 'apache',
            'nginx-conf' => 'nginx',
            default => $language,
        });

        file_put_contents($path, $content);

        return $content;
    }
}

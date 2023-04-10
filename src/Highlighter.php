<?php

namespace Alfresco;

use Spatie\ShikiPhp\Shiki;

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
        return $this->shiki->highlightCode($code, match ($language) {
            'php' => 'blade',
            'apache-conf' => 'apache',
            'nginx-conf' => 'nginx',
            default => $language,
        });
    }
}

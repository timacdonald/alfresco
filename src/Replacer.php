<?php

namespace Alfresco;

use Illuminate\Config\Repository as Configuration;
use Illuminate\Support\Str;
use RuntimeException;

use function Safe\file_get_contents;

class Replacer
{
    /**
     * Create a new instance.
     */
    public function __construct(
        protected Configuration $config,
    ) {
        //
    }

    /**
     * Make replacements for the given code snippet.
     */
    public function handle(string $original, string $language): string
    {
        // TODO make this look for a .replacement.php or something better.
        return $original;
        if (! file_exists($path = $this->config->get('replacements_directory').'/'.hash('xxh128', $original))) {
            return $original;
        }

        $content = file_get_contents($path);

        $replacement = Str::after($content, <<< REPLACEMENT
            {$original}
            === AFTER ===

            REPLACEMENT);

        if ($content === $replacement) {
            throw new RuntimeException('Unexpected content found in the replacement.');
        }

        return $replacement;
    }
}

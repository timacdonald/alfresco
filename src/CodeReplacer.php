<?php

namespace Alfresco;

use Illuminate\Support\Str;
use RuntimeException;

use function Safe\file_get_contents;

class CodeReplacer
{
    public function __construct(protected Configuration $config)
    {
        //
    }

    public function replace(string $original): string
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

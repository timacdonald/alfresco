<?php

namespace Alfresco;

use RuntimeException;

class Translation
{
    /**
     * @param  array<string, string>  $values
     */
    public function __construct(
        protected string $language,
        protected array $values,
    ) {
        //
    }

    public function get(string $key): string
    {
        if (! array_key_exists($key, $this->values)) {
            throw new RuntimeException("Unknown translation key [{$key}] for language [{$this->language}].");
        }

        return $this->values[$key];
    }
}

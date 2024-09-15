<?php

namespace Alfresco;

use RuntimeException;

class Translation
{
    protected array $values = [];

    public function __construct(
        protected Configuration $config,
    ) {
        //
    }

    public function get(string $key): string
    {
        if (! array_key_exists($key, $this->values())) {
            throw new RuntimeException("Unknown translation key [{$key}] for language [{$this->config->get('language')}].");
        }

        return $this->values()[$key];
    }

    protected function values()
    {
        return $this->values[$this->config->get('language')] ??= (require_once $this->config->get('translation_directory')."/{$this->config->get('language')}.php");
    }
}

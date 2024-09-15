<?php

namespace Alfresco;

use RuntimeException;

class Translation
{
    /**
     * @var array<string, array<string, string>>
     */
    protected array $valuesCache = [];

    public function __construct(
        protected Configuration $config,
    ) {
        //
    }

    public function get(string $key): string
    {
        $values = $this->values();

        if (! array_key_exists($key, $values)) {
            throw new RuntimeException("Unknown translation key [{$key}] for language [{$this->config->get('language')}].");
        }

        return $values[$key];
    }

    /**
     * @return array<string, string>
     */
    protected function values(): array
    {
        return $this->valuesCache[$this->config->get('language')] ??= (require_once $this->config->get('translation_directory')."/{$this->config->get('language')}.php");
    }
}

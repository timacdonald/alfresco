<?php

namespace Alfresco;

use RuntimeException;

class Configuration
{
    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(public array $config)
    {
        //
    }

    /**
     * @param  array<string, mixed>  $config
     */
    public function merge(array $config): self
    {
        $this->config = array_merge($this->config, $config);

        return $this;
    }

    public function get(string $key): mixed
    {
        if (! array_key_exists($key, $this->config)) {
            throw new RuntimeException("Unknown configuration key [{$key}].");
        }

        return $this->config[$key];
    }
}

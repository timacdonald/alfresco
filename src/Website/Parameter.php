<?php

namespace Alfresco\Website;

use Illuminate\Support\Collection;

class Parameter
{
    public Collection $types;

    public function __construct(
        array $types,
        public string $name,
    ) {
        $this->types = collect($types);
    }
}

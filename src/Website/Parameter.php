<?php

declare(strict_types=1);

namespace Alfresco\Website;

use Illuminate\Support\Collection;

class Parameter
{
    /**
     * The parameter types.
     *
     * @var Collection<int, string>
     */
    public Collection $types;

    /**
     * Create a new instance.
     *
     * @param  list<string>  $types
     */
    public function __construct(
        array $types,
        public string $name,
    ) {
        $this->types = collect($types);
    }
}

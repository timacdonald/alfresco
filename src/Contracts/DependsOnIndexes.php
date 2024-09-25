<?php

declare(strict_types=1);

namespace Alfresco\Contracts;

interface DependsOnIndexes
{
    /**
     * Retrieve the generator's indexes.
     *
     * @return list<Generator>
     */
    public function indexes(): array;
}

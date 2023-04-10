<?php

namespace Alfresco\Contracts;

interface DependsOnIndexes
{
    /**
     * Retrieve the generator's indexes.
     *
     * @return array<int, Generator>
     */
    public function indexes(): array;
}

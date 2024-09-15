<?php

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

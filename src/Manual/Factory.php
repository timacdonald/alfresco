<?php

namespace Alfresco\Manual;

use Illuminate\Config\Repository as Configuration;
use PHPUnit\Event\RuntimeException;
use XMLReader;

class Factory
{
    /**
     * Create a new instance.
     */
    public function __construct(
        protected Configuration $config,
    ) {
        //
    }

    /**
     * Make a new Manual instance from the given path.
     */
    public function make(string $path): Manual
    {
        return new Manual($this->reader($path));
    }

    /**
     * Make a base XML reader for the given path.
     */
    protected function reader(string $path): XMLReader
    {
        $reader = XMLReader::open($path, 'UTF-8');

        if ($reader === false) {
            throw new RuntimeException('Unable to create XML reader.');
        }

        return $reader;
    }
}

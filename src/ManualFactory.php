<?php

namespace Alfresco;

use PHPUnit\Event\RuntimeException;
use XMLReader;

class ManualFactory
{
    /**
     * Create a new instance.
     */
    public function __construct(
        protected Configuration $config,
    ) {
        //
    }

    public function make(string $path): Manual
    {
        $reader = XMLReader::open($path, 'UTF-8');

        if ($reader === false) {
            throw new RuntimeException('Unable to create XML reader.');
        }

        return new Manual($reader);
    }
}

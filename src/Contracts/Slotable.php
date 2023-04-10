<?php

namespace Alfresco\Contracts;

use Stringable;

interface Slotable extends Stringable
{
    /**
     * Retrieve the "before" content.
     */
    public function before(): string;

    /**
     * Retrieve the "after" content.
     */
    public function after(): string;

    /**
     * Render to a string.
     */
    public function toString(): string;
}

<?php

declare(strict_types=1);

namespace Alfresco\Render;

use Stringable;
use RuntimeException;
use Alfresco\Contracts\Slotable;

class Wrapper implements Slotable
{
    /**
     * Create a new instance.
     */
    public function __construct(
        protected string|Stringable $before = '',
        protected string|Stringable $after = '',
        protected ?Slotable $slot = null,
    ) {
        //
    }

    /**
     * Retrieve the "before" content.
     */
    public function before(): string
    {
        return $this->before.$this->slot?->before();
    }

    /**
     * Retrieve the "after" content.
     */
    public function after(): string
    {
        return $this->slot?->after().$this->after;
    }

    /**
     * Convert to a string.
     */
    public function toString(): string
    {
        if ($this->slot !== null) {
            throw new RuntimeException('Unable to render a wrapper with a content wrapper.');
        }

        return $this->before().$this->after();
    }

    /**
     * Convert to a string.
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}

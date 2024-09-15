<?php

namespace Alfresco;

use Alfresco\Contracts\Slotable;
use RuntimeException;
use Stringable;

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

    public function before(): string
    {
        return $this->before.$this->slot?->before();
    }

    public function after(): string
    {
        return $this->slot?->after().$this->after;
    }

    public function toString(): string
    {
        if ($this->slot !== null) {
            throw new RuntimeException('Unable to render a wrapper with a content wrapper.');
        }

        return $this->before().$this->after();
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}

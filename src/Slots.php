<?php

namespace Alfresco;

use Alfresco\Contracts\Slotable;

class Slots implements Slotable
{
    /**
     * Create a new instance.
     */
    public function __construct(
        protected array $slots,
    ) {
        //
    }

    public function before(): string
    {
        return '';
    }

    public function after(): string
    {
        return '';
    }

    public function toString(): string
    {
        $content = '';

        foreach ($this->slots as $slot) {
            $content .= $slot->toString();
        }

        return $content;
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}


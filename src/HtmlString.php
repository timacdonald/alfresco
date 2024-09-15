<?php

namespace Alfresco;

use Alfresco\Contracts\Slotable;
use Stringable;

class HtmlString implements Slotable
{
    /**
     * Create a new instance.
     */
    public function __construct(
        protected string|Stringable $content,
    ) {
        //
    }

    public function before(): string
    {
        return $this->content;
    }

    public function after(): string
    {
        return '';
    }

    public function toString(): string
    {
        return $this->before().$this->after();
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}

<?php

namespace Alfresco\Render;

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

    /**
     * The content before the main content.
     */
    public function before(): string
    {
        return $this->content;
    }

    /**
     * The content after the main content.
     */
    public function after(): string
    {
        return '';
    }

    /**
     * Convert to a string.
     */
    public function toString(): string
    {
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

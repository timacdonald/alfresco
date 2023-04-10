<?php

namespace Alfresco;

use Closure;

class TranslationFactory
{
    public function __construct(
        protected Closure $resolver,
    ) {
        //
    }

    public function make(string $language): Translation
    {
        return ($this->resolver)($language);
    }
}

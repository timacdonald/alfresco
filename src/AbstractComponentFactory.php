<?php

namespace Alfresco;

class AbstractComponentFactory
{
    /**
     * Create a new instance.
     */
    public function __construct(protected string $path, protected TranslationFactory $translationFactory)
    {
        //
    }

    public function make(string $language): ComponentFactory
    {
        return new ComponentFactory($this->path, $this->translationFactory->make($language));
    }
}

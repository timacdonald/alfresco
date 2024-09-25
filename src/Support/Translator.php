<?php

declare(strict_types=1);

namespace Alfresco\Support;

use Illuminate\Translation\Translator as BaseTranslator;

class Translator extends BaseTranslator
{
    /**
     * @param  array<string, string>  $replace
     */
    public function get($key, array $replace = [], $locale = null, $fallback = true): string
    {
        $value = parent::get(...func_get_args());

        assert(is_string($value));

        return $value;
    }
}

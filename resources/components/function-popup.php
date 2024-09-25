<?php

declare(strict_types=1);

namespace Alfresco;

use Alfresco\Render\Factory;
use Alfresco\Website\Method;
use Alfresco\Website\Parameter;
use Illuminate\Support\Str;

return function (
    string $id,
    Method $method,
    Factory $render,
) {
    $description = Str::of($method->description)->trim()->finish('.');

    return $render->html(<<<HTML
        <div role="tooltip" id="{$id}" class="text-slate-600 text-left pointer-events-none leading-5">
            <pre class="text-slate-400">
        /**
         * {$description}
         */</pre>
            <span class="text-blue-600">{$method->name}</span>({$method->parameters()->map(fn (Parameter $parameter) => <<<NESTED_HTML
                <span class="text-fuchsia-600">{$parameter->types->implode('|')}</span> <span class="text-rose-600">\${$parameter->name}</span>
                NESTED_HTML)->implode(', ')}): <span class="text-purple-600">{$method->returnTypes()->implode('|')}</span>
        </div>
        HTML
    );
};

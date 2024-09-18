<?php

namespace Alfresco;

use Alfresco\Render\Factory;

return fn (
    Factory $render,
    array $attributes = [],
) => $render->tag(
    as: 'code',
    attributes: array_merge_recursive([
        'class' => [
            'text-[0.875em] not-italic bg-violet-950/5 text-violet-950 rounded leading-none py-1 px-1.5 inline-block font-mono',
        ],
    ], $attributes),
);

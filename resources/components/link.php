<?php

namespace Alfresco;

use Alfresco\Render\Factory;
use Alfresco\Support\Link;

return fn (
    Factory $render,
    Link $link,
    string $text = '',
) => $render->tag(
    as: 'a',
    attributes: [
        'href' => $link->isInternal
            ? "{$link->destination}.html"
            : $link->destination,
        'rel' => $link->isInternal
            ? 'prefetch'
            : false,
        'class' => [
            'text-violet-700 hover:underline group',
            $link->isInternal ? '' : 'inline-flex items-center',
        ],
    ],
    before: $text,
    after: $link->isInternal ? '' : $render->component('icon.external-link'),
);

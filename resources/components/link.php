<?php

declare(strict_types=1);

namespace Alfresco;

use Alfresco\Support\Link;
use Alfresco\Render\Factory;

return fn (
    Factory $render,
    Link $link,
    string $text = '',
) => $render->tag(
    as: 'a',
    class: [
        'text-violet-700 hover:underline group',
        $link->isInternal ? '' : 'inline-flex items-center',
    ],
    attributes: [
        'href' => $link->isInternal
            ? "{$link->destination}.html"
            : $link->destination,
        'rel' => $link->isInternal
            ? 'prefetch'
            : false,
    ],
    before: $text,
    after: $link->isInternal ? '' : $render->component('icon.external-link'),
);

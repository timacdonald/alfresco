<?php

declare(strict_types=1);

namespace Alfresco;

use Alfresco\Support\Link;
use Alfresco\Render\Factory;

return fn (
    Factory $render,
    Link $link,
) => $render->tag(
    as: 'h3',
    class: 'text-xl text-blue-950 items-center text-lg font-bold leading-none inline-block',
    attributes: [
        'id' => $link->destinationWithoutFragmentHash(),
    ],
    slot: $render->tag(
        as: 'a',
        class: 'inline-flex items-center relative group hover:underline',
        attributes: [
            'href' => $link->destination,
        ],
        after: $render->tag(
            as: 'span',
            class: 'hidden items-center justify-center group-hover:flex leading-none absolute w-5 h-full -right-7 top-0',
            attributes: [
                'aria-hidden' => 'true',
            ],
            after: $render->component('icon.link'),
        ),
    ),
);

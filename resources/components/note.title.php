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
    attributes: [
        'id' => $link->destinationWithoutFragmentHash(),
        'class' => 'text-xl text-blue-950 items-center text-lg font-bold leading-none inline-block',
    ],
    slot: $render->tag(
        as: 'a',
        attributes: [
            'href' => $link->destination,
            'class' => 'inline-flex items-center relative group hover:underline',
        ],

        after: $render->tag(
            as: 'span',
            attributes: [
                'aria-hidden' => 'true',
                'class' => 'hidden items-center justify-center group-hover:flex leading-none absolute w-5 h-full -right-7 top-0',
            ],
            after: $render->component('icon.link'),
        ),
    )
);

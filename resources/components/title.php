<?php

declare(strict_types=1);

namespace Alfresco;

use Alfresco\Support\Link;
use Alfresco\Render\Factory;

return fn (
    Factory $render,
    Link $link,
    int $level,
) => $render->tag(
    as: match ($level) {
        1 => 'h1',
        2 => 'h2',
        6 => 'h6',
    },
    class: [
        'font-extrabold text-violet-950 leading-tight tracking-tight',
        match ($level) {
            // Due to line-height the first heading on a page doesn't have
            // consistent spacing on top and sides. We will just "pull" it
            // up a few pixels.
            1 => 'text-4xl -mt-3',
            2 => 'text-2xl my-6',
            6 => 'bg-red-500',
        },
    ],
    attributes: [
        'id' => $link->destinationWithoutFragmentHash(),
    ],
    slot: $render->tag(
        as: 'a',
        class: 'relative group hover:underline',
        attributes: [
            'href' => $link->destination,
        ],
        after: $render->tag(
            as: 'span',
            class: 'hidden items-center group-hover:flex leading-none absolute w-5 h-full -right-7 top-0',
            attributes: [
                'aria-hidden' => 'true',
            ],
            after: $render->component('icon.link'),
        ),
    ),
);

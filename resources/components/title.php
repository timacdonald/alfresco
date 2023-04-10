<?php

namespace Alfresco;

return fn (
    ComponentFactory $render,
    Link $link,
    int $level,
) => $render->tag(
    as: match ($level) {
        1 => 'h1',
        2 => 'h2',
        6 => 'h6',
    },
    attributes: [
        'id' => $link->destinationWithoutFragmentHash(),
        'class' => [
            'font-extrabold text-violet-950 leading-tight tracking-tight',
            match ($level) {
                // Due to line-height the first heading on a page doesn't have
                // consistent spacing on top and sides. We will just "pull" it
                // up a few pixels.
                1 => 'text-4xl -mt-3',
                2 => 'text-3xl my-6',
                6 => 'bg-red-500',
            },
        ],
    ],
    slot: $render->tag(
        as: 'a',
        attributes: [
            'href' => $link->destination,
            'class' => 'relative group hover:underline',
        ],
        after: $render->tag(
            as: 'span',
            attributes: [
                'aria-hidden' => true,
                'class' => 'hidden items-center group-hover:flex leading-none absolute w-5 h-full -right-7 top-0',
            ],
            after: $render->component('icon.link'),
        ),
    )
);

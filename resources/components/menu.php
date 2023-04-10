<?php

namespace Alfresco;

use Alfresco\Website\Title;
use Illuminate\Support\Collection;

return fn (
    ComponentFactory $render,
    Collection $items,
    Title $active,
    Collection $empty,
) => $render->tag(
    as: 'nav',
    attributes: [
        'class' => 'order-first py-12 pl-12 pr-6 max-w-[300px] bg-violet-50/50 border-r border-violet-100',
    ],
    before: $render->tag(
        as: 'ul',
        attributes: [
            'class' => 'list-none my-6 first:mt-0 last:mb-0 -ml-4',
        ],
        before: $render->component('menu-list', [
            'items' => $items,
            'active' => $active,
            'empty' => $empty,
        ]),
    )
);

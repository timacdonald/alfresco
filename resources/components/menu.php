<?php

declare(strict_types=1);

namespace Alfresco;

use Alfresco\Website\Title;
use Alfresco\Render\Factory;
use Illuminate\Support\Collection;

return fn (
    Factory $render,
    Collection $items,
    Title $active,
    Collection $empty,
) => $render->tag(
    as: 'nav',
    class: 'order-first py-4 pl-10 pr-6 w-[300px] bg-violet-25 border-r border-violet-100',
    attributes: [
        'id' => 'main-nav',
    ],
    before: $render->tag(
        as: 'div',
        class: 'list-none my-6 first:mt-0 last:mb-0 -ml-4',
        before: $render->component('menu-list', [
            'items' => $items,
            'active' => $active,
            'empty' => $empty,
        ]),
    ),
);

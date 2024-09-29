<?php

declare(strict_types=1);

namespace Alfresco;

use Alfresco\Render\Factory;

return fn (
    Factory $render,
) => $render->tag(
    as: 'div',
    attributes: [
        'class' => 'border border-violet-100 bg-violet-25 px-6 py-5 leading-7 rounded my-6 first:mt-0 last:mb-0 relative ',
    ],
    slot: $render->tag(
        as: 'pre',
        attributes: [
            'class' => 'overflow-x-auto',
        ],
        slot: $render->tag(
            as: 'code',
            attributes: [
                'class' => 'font-mono text-slate-950',
            ],
        ),
    ),
);

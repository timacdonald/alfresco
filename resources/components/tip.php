<?php

namespace Alfresco;

use Alfresco\Render\Factory;
use Illuminate\Translation\Translator;

return fn (
    Factory $render,
    Translator $translator,
) => $render->tag(
    as: 'div',
    attributes: [
        'aria-role' => 'note',
        'class' => 'bg-green-50/50 my-6 first:mt-0 last:mb-0 p-6 rounded border border-green-100 text-green-950 relative',
    ],
    before: $render->tag(
        as: 'strong',
        before: $translator->get('ui.tip.badge'),
        attributes: [
            'class' => 'absolute inline-block right-3 -top-3 rounded bg-green-200 leading-none py-1 px-2 text-sm font-semibold font-mono uppercase',
        ],
    ),
    // We wrap this in a `div` to ensure that the first / last element
    // margin changes are not impacted by the "note" badge we
    // append.
    slot: $render->tag('div'),
);

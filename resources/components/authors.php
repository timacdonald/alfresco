<?php

namespace Alfresco;

use Alfresco\Render\Factory;

return fn (
    Factory $render,
    Translation $translation,
) => $render->wrapper(
    before: $render->tag(
        as: 'p',
        attributes: [
            'class' => 'my-6 first:mt-0 last:mb-0',
        ],
        before: $translation->get('authors.by').':',
    ),
    slot: $render->component('unordered-list'),
    after: $render->tag(
        as: 'div',
        attributes: [
            'class' => 'mb-6',
        ],
    ),
);

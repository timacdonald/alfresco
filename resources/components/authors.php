<?php

namespace Alfresco;

use Alfresco\Render\Factory;
use Alfresco\Support\Translator;

return fn (
    Factory $render,
    Translator $translator,
) => $render->wrapper(
    before: $render->tag(
        as: 'p',
        attributes: [
            'class' => 'my-6 first:mt-0 last:mb-0',
        ],
        before: $translator->get('ui.authors.by').':',
    ),
    slot: $render->component('unordered-list'),
    after: $render->tag(
        as: 'div',
        attributes: [
            'class' => 'mb-6',
        ],
    ),
);

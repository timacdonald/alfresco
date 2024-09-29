<?php

declare(strict_types=1);

namespace Alfresco;

use Alfresco\Render\Factory;
use Alfresco\Support\Translator;

return fn (
    Factory $render,
    Translator $translator,
) => $render->wrapper(
    before: $render->tag(
        as: 'p',
        class: 'my-6 first:mt-0 last:mb-0',
        before: $translator->get('ui.authors.by').':',
    ),
    slot: $render->component('unordered-list'),
    after: $render->tag(
        as: 'div',
        class: 'mb-6',
    ),
);

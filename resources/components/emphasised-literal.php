<?php

declare(strict_types=1);

namespace Alfresco;

use Alfresco\Render\Factory;

return fn (
    Factory $render,
) => $render->tag(
    as: 'em',
    attributes: [
        'class' => 'not-italic',
    ],
    slot: $render->component('inline-code'),
);

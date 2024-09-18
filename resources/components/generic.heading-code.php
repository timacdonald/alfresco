<?php

namespace Alfresco;

use Alfresco\Render\Factory;

return fn (
    Factory $render,
) => $render->tag(
    as: 'code',
    attributes: [
        'class' => [
            'font-mono',
        ],
    ],
);

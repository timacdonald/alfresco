<?php

declare(strict_types=1);

namespace Alfresco;

use Alfresco\Render\Factory;

return fn (
    Factory $render,
    string $type = '1',
) => $render->tag(
    as: 'ol',
    class: 'list-decimal pl-5 my-2 last:mb-0 space-y-2',
    attributes: [
        'type' => $type,
    ],
);

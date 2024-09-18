<?php

namespace Alfresco;

use Alfresco\Render\Factory;

return fn (
    Factory $render,
) => $render->tag(
    as: 'ul',
    attributes: [
        'class' => 'list-disc pl-5 my-3 space-y-2',
    ],
);

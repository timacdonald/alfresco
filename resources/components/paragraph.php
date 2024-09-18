<?php

namespace Alfresco;

use Alfresco\Render\Factory;

return fn (
    Factory $render,
) => $render->tag(
    as: 'p',
    attributes: [
        'class' => 'my-6 first:mt-0 last:mb-0',
    ],
);

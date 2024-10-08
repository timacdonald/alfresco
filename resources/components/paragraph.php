<?php

declare(strict_types=1);

namespace Alfresco;

use Alfresco\Render\Factory;

return fn (
    Factory $render,
) => $render->tag(
    as: 'p',
    class: 'my-6 first:mt-0 last:mb-0',
);

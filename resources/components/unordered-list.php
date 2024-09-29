<?php

declare(strict_types=1);

namespace Alfresco;

use Alfresco\Render\Factory;

return fn (
    Factory $render,
) => $render->tag(
    as: 'ul',
    class: 'list-disc pl-5 my-3 space-y-2',
);

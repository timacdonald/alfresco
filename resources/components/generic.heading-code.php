<?php

declare(strict_types=1);

namespace Alfresco;

use Alfresco\Render\Factory;

return fn (
    Factory $render,
) => $render->tag(
    as: 'code',
    class: 'font-mono',
);

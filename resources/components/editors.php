<?php

declare(strict_types=1);

namespace Alfresco;

use Alfresco\Render\Factory;
use Alfresco\Support\Translator;

return fn (
    Factory $render,
    Translator $translator,
) => $render->wrapper(
    before: $render->inlineText($translator->get('ui.editors.by')),
    after: '.'
);

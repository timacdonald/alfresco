<?php

namespace Alfresco;

use Alfresco\Render\Factory;
use Illuminate\Translation\Translator;

return fn (
    Factory $render,
    Translator $translator,
) => $render->wrapper(
    before: $render->inlineText($translator->get('ui.editors.by')),
    after: '.'
);

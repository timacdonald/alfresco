<?php

namespace Alfresco;

use Alfresco\Render\Factory;

return fn (
    Factory $render,
    Translation $translation,
) => $render->wrapper(
    before: $render->inlineText($translation->get('editors.by')),
    after: '.'
);

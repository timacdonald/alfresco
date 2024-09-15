<?php

namespace Alfresco;

return fn (
    ComponentFactory $render,
    Translation $translation,
) => $render->wrapper(
    before: $render->inlineText($translation->get('editors.by')),
    after: '.'
);

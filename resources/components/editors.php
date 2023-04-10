<?php

namespace Alfresco;

return fn (
    ComponentFactory $render,
    callable $translate,
) => $render->wrapper(
    before: $render->inlineText($translate('editors.by')),
    after: '.'
);

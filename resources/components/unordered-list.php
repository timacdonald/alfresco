<?php

namespace Alfresco;

return fn (
    ComponentFactory $render,
) => $render->tag(
    as: 'ul',
    attributes: [
        'class' => 'list-disc pl-5 my-3 space-y-2',
    ],
);

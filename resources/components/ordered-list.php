<?php

namespace Alfresco;

return fn (
    ComponentFactory $render,
    string $type = '1',
) => $render->tag(
    as: 'ol',
    attributes: [
        'type' => $type,
        'class' => [
            'list-decimal pl-5 my-2 last:mb-0 space-y-2',
        ],
    ],
);

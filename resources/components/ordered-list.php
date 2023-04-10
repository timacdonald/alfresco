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
            'list-decimal pl-5 my-6 first:mt-0 last:mb-0',
        ],
    ],
);

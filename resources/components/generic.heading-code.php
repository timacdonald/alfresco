<?php

namespace Alfresco;

return fn (
    ComponentFactory $render,
) => $render->tag(
    as: 'code',
    attributes: [
        'class' => [
            'font-mono',
        ],
    ],
);

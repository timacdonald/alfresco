<?php

namespace Alfresco;

return fn (
    ComponentFactory $render,
) => $render->tag(
    as: 'code',
    attributes: [
        'class' => [
            'text-sm not-italic bg-violet-50 text-violet-950 rounded leading-none p-1 inline-block font-mono',
        ],
    ],
);

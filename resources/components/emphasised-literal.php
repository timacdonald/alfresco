<?php

namespace Alfresco;

return fn (
    ComponentFactory $render,
) => $render->tag(
    as: 'em',
    attributes: [
        'class' => 'not-italic',
    ],
    slot: $render->component('inline-code'),
);

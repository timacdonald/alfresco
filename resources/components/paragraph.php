<?php

namespace Alfresco;

return fn (
    ComponentFactory $render,
) => $render->tag(
    as: 'p',
    attributes: [
        'class' => 'my-6 first:mt-0 last:mb-0',
    ],
);

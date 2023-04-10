<?php

namespace Alfresco;

return fn (
    ComponentFactory $render,
) => $render->tag(
    as: 'ul',
    attributes: [
        'class' => 'list-disc pl-5 my-6 first:mt-0 last:mb-0',
    ],
);

<?php

namespace Alfresco;

return fn (
    ComponentFactory $render,
) => $render->tag(
    as: 'main',
    attributes: [
        'class' => 'max-w-4xl w-full p-12 text-slate-800 leading-relaxed text-base font-normal',
    ],
);

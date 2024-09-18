<?php

namespace Alfresco;

use Alfresco\Render\Factory;

return fn (
    Factory $render,
) => $render->tag(
    as: 'main',
    attributes: [
        'class' => 'max-w-4xl w-full px-12 pb-8 pt-[23px] text-slate-800 leading-relaxed text-base font-normal',
    ],
);

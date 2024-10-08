<?php

declare(strict_types=1);

namespace Alfresco;

use Alfresco\Support\Translator;

return fn (
    Translator $translator,
) => <<<HTML
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3 text-violet-300 group-hover:text-violet-700 ml-1 mr-0.5 inline-block">
        <title>{$translator->get('ui.link.external')}</title>
        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25">
    </svg>
    HTML;

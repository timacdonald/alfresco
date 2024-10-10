<?php

declare(strict_types=1);

namespace Alfresco\Website;

use Alfresco\Render\HtmlString;

class Image
{
    /**
     * Create a new instance.
     */
    public function __construct(
        public string $alt,
        public string $file1,
        public ?string $file2 = null,
        public ?HtmlString $caption = null,
    ) {
        //
    }
}

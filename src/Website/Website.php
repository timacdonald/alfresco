<?php

declare(strict_types=1);

namespace Alfresco\Website;

use Alfresco\Manual\Node;

class Website
{
    /**
     * Determine if the website should chunk on the given node.
     */
    public static function shouldChunk(Node $node): bool
    {
        return in_array($node->name, [
            'book',
            'chapter',
            'legalnotice',
            'preface',
            'sect1',
            'section',
        ], true) && $node->hasId() && ! $node->objectsToChunking();
    }
}

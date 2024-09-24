<?php

namespace Alfresco\Contracts;

use Alfresco\Manual\Node;
use Alfresco\Stream\Stream;

interface Generator
{
    /**
     * Set up.
     */
    public function setUp(): void;

    /**
     * Retrieve the stream for the given node.
     */
    public function stream(Node $node): Stream;

    /**
     * Render the given node.
     */
    public function render(Node $node): string|Slotable;

    /**
     * Determine if the generator should chunk.
     */
    public function shouldChunk(Node $node): bool;

    /**
     * Tear down.
     */
    public function tearDown(): void;
}

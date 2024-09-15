<?php

namespace Alfresco\Website;

use Alfresco\Configuration;
use Alfresco\Contracts\Generator;
use Alfresco\Contracts\Slotable;
use Alfresco\FileStreamFactory;
use Alfresco\Node;
use Alfresco\Stream;
use Illuminate\Support\Collection;

class EmptyChunkIndex implements Generator
{
    /**
     * The empty chunk output stream.
     */
    protected Stream $stream;

    /**
     * The cache of all ids.
     */
    protected ?Collection $idsCache;

    /**
     * Indicates that the current chunk is empty.
     */
    protected bool $isEmpty = true;

    /**
     * The current chunk.
     */
    protected ?Node $chunk = null;

    /**
     * Create a new instance.
     */
    public function __construct(
        protected FileStreamFactory $streamFactory,
        protected Configuration $config
    ) {
        $this->stream = $this->streamFactory->make(
            "{$this->config->get('index_directory')}/website/{$this->config->get('language')}/empty_pages.php",
            1000,
        );
    }

    /**
     * Set up.
     */
    public function setUp(): void
    {
        $this->stream->write(<<< 'PHP'
            <?php

            return [

            PHP);
    }

    /**
     * Retrieve the stream for the given node.
     */
    public function stream(Node $node): Stream
    {
        return $this->stream;
    }

    /**
     * Determine if the generator should chunk.
     */
    public function shouldChunk(Node $node): bool
    {
        return false;
    }

    /**
     * Render the given node.
     */
    public function render(Node $node): string|Slotable
    {
        if (Website::shouldChunk($node)) {
            return with($this->emptyChunk(), function (?Node $emptyChunk) use ($node) {
                $this->chunk = $node;
                $this->isEmpty = true;

                return $emptyChunk !== null
                    ? "    {$emptyChunk->exportId()},\n"
                    : '';
            });
        }

        if (! in_array($node->name, ['info', 'title', '#text'])) {
            $this->isEmpty = false;
        }

        return '';
    }

    /**
     * Tear down.
     */
    public function tearDown(): void
    {
        $this->stream->write('];');
    }

    /**
     * Retrieve the empty chunk IDs.
     *
     * @return Collection<int, string>
     */
    public function ids(): Collection
    {
        return $this->idsCache ??= collect(require_once $this->stream->path);
    }

    /**
     * Retrieve the current empty chunk.
     */
    protected function emptyChunk(): ?Node
    {
        return $this->isEmpty
            ? $this->chunk
            : null;
    }
}

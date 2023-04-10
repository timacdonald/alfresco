<?php

namespace Alfresco\Website;

use Alfresco\Configuration;
use Alfresco\Container;
use Alfresco\Contracts\Generator;
use Alfresco\Contracts\Slotable;
use Alfresco\FileStreamFactory;
use Alfresco\Node;
use Alfresco\Stream;
use Illuminate\Support\Collection;

class EmptyChunkIndex implements Generator
{
    /**
     * Indicates that the current chunk is empty.
     */
    protected bool $isEmpty = true;

    /**
     * The current chunk.
     */
    protected ?Node $chunk = null;

    /**
     * Resolve from the container.
     */
    public static function resolve(Container $container, string $language): EmptyChunkIndex
    {
        $config = $container->make(Configuration::class);

        $streamFactory = $container->make(FileStreamFactory::class);

        $path = "{$config->get('index_directory')}/website/{$language}/empty_pages.php";

        return new EmptyChunkIndex(
            stream: $streamFactory->make($path, 1000),
        );
    }

    /**
     * Create a new instance.
     */
    public function __construct(
        protected Stream $stream
    ) {
        //
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
        return collect(require $this->stream->path);
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

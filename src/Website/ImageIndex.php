<?php

declare(strict_types=1);

namespace Alfresco\Website;

use RuntimeException;
use Alfresco\Manual\Node;
use Alfresco\Stream\Stream;
use Illuminate\Support\Str;
use Alfresco\Render\Factory;
use Alfresco\Render\HtmlString;
use Alfresco\Contracts\Slotable;
use Alfresco\Contracts\Generator;
use Illuminate\Support\Collection;
use Alfresco\Stream\FileStreamFactory;
use Illuminate\Config\Repository as Configuration;

class ImageIndex implements Generator
{
    /**
     * The output stream.
     */
    protected Stream $stream;

    /**
     * The current file number.
     */
    protected int $fileNumber = 1;

    /**
     * The cache of all images.
     *
     * @var Collection<string, Image>
     */
    protected ?Collection $allCache;

    /**
     * Create a new instance.
     */
    public function __construct(
        protected FileStreamFactory $streamFactory,
        protected Factory $render,
        protected Configuration $config,
    ) {
        $this->stream = $this->streamFactory->make(
            "{$this->config->get('index_directory')}/website/{$this->config->get('language')}/images.php",
            1000,
        );
    }

    /**
     * Set up.
     */
    public function setUp(): void
    {
        $this->stream->write(<<<'PHP'
            <?php

            declare(strict_types=1);

            use Alfresco\Website\Image;
            use Alfresco\Render\HtmlString;

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
        $mediaObject = $node->name === 'mediaobject'
            ? $node
            : $node->ancestor('mediaobject');

        if ($mediaObject === null) {
            return '';
        }

        return match ($node->name) {
            'mediaobject' => $this->renderImageObject($node),
            'imagedata' => $this->renderImageData($node),
            'caption' => $this->renderCaption($node),
            'alt' => $this->renderAlt($node),
            'link' => $this->renderLink($node),
            '#text' => $this->renderText($node),
            'simpara',
            'imageobject' => '',
            default => throw new RuntimeException(<<<ERROR
                Unhandled [{$node->name}] tag found in imageobject. 
                Update the ImageIndex::render method.

                Content:
                {$node->innerContent()}
                ERROR),
        };
    }

    /**
     * Tear down.
     */
    public function tearDown(): void
    {
        $this->stream->write('];');
    }

    /**
     * Render an imageobject node.
     */
    protected function renderImageObject(Node $node): Slotable
    {
        $this->fileNumber = 1;

        return $this->render->wrapper(
            before: <<<'PHP'
                    new Image(
                PHP,
            after: <<<'PHP'

                    ),


                PHP,
        );
    }

    /**
     * Render an caption node.
     */
    protected function renderCaption(Node $node): Slotable
    {
        return $this->render->wrapper(
            before: "\n        caption: new HtmlString(<<<'HTML'\n",
            after: "\nHTML),",
        );
    }

    /**
     * Render an imagedata node.
     */
    protected function renderImageData(Node $node): string
    {
        return "\n        file".($this->fileNumber++).": {$this->render->export($node->attribute('fileref'))},";
    }

    /**
     * Render an alt node.
     */
    protected function renderLink(Node $node): Slotable
    {
        return $this->render->wrapper(
            before: <<<HTML
                <a href="{$node->link()->destination}">
                HTML,
            after: <<<'HTML'
                </a>
                HTML,
        );
    }

    /**
     * Render an alt node.
     */
    protected function renderAlt(Node $node): Slotable
    {
        return $this->render->wrapper(
            before: "\n        alt: new HtmlString('",
            after: "'),",
        );
    }

    /**
     * Render a text node.
     */
    protected function renderText(Node $node): string
    {
        return e(Str::squish($node->value));
    }

    /**
     * Retrieve all titles from the index.
     *
     * @return Collection<string, Title>
     */
    public function all(): Collection
    {
        return $this->allCache ??= collect(require $this->stream->path); // @phpstan-ignore argument.templateType, argument.templateType
    }

    /**
     * Find the title based on it's ID.
     */
    public function find(string $id): Title
    {
        $title = $this->findMany(collect([$id]))->first();

        if ($title === null) {
            throw new RuntimeException("Could not find title with id [{$id}].");
        }

        return $title;
    }

    /**
     * Find many titles based on their ID.
     *
     * @param  Collection<int, string>  $ids
     * @return Collection<string, Title>
     */
    public function findMany(Collection $ids): Collection
    {
        return $this->all()->only($ids);
    }

    /**
     * Retrieve the title heirachy.
     *
     * @return Collection<int, Title>
     */
    public function heirachy(): Collection
    {
        return $this->all()
            ->reduce(function (Collection $result, Title $title) {
                $level = 1;
                $children = $result;

                while ($level < $title->level) {
                    $children = $children->reverse()->first()->children;

                    $level++;
                }

                $children->push(new Title(
                    id: $title->id,
                    lineage: $title->lineage,
                    level: $level,
                    html: $result->isEmpty() ? new HtmlString('Home') : $title->html,
                ));

                return $result;
            }, collect([]));
    }
}

<?php

namespace Alfresco\Website;

use Alfresco\ComponentFactory;
use Alfresco\Contracts\Generator;
use Alfresco\Contracts\Slotable;
use Alfresco\FileStreamFactory;
use Alfresco\HtmlString;
use Alfresco\Manual\Node;
use Alfresco\Stream;
use Illuminate\Config\Repository as Configuration;
use Illuminate\Support\Collection;
use RuntimeException;

class TitleIndex implements Generator
{
    /**
     * Modifier to adjust the heading level.
     */
    protected int $levelModifier = 0;

    /**
     * The output stream.
     */
    protected Stream $stream;

    /**
     * The cache of all titles.
     *
     * @var Collection<string, Title>
     */
    protected ?Collection $allCache;

    /**
     * Create a new instance.
     */
    public function __construct(
        protected FileStreamFactory $streamFactory,
        protected ComponentFactory $render,
        protected Configuration $config,
    ) {
        $this->stream = $this->streamFactory->make(
            "{$this->config->get('index_directory')}/website/{$this->config->get('language')}/titles.php",
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

            use Alfresco\Website\Title;
            use Alfresco\HtmlString;

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
        $title = $node->name === 'title'
            ? $node
            : $node->ancestor('title');

        if ($title === null) {
            return '';
        }

        [$section, $level] = $this->info($title);

        if ($section === null) {
            return '';
        }

        return match ($node->name) {
            'title' => $this->renderTitle($section, $level),
            '#text' => $this->renderText($node),
            'productname' => '',
            'literal', 'command', 'function' => $this->render->tag('code'),
            'classname' => $this->render->tag('var'),
            default => throw new RuntimeException(<<< ERROR
                Unhandled [{$node->name}] tag found in title. 
                Update the TitleIndex::render method.

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
        $this->stream->write("];\n");
    }

    /**
     * Render a title node.
     */
    protected function renderTitle(Node $section, int $level): Slotable
    {
        return $this->render->wrapper(
            before: <<< PHP
                    {$section->exportId()} => new Title(id: {$section->exportId()}, level: {$level}, html: new HtmlString(<<< 'HTML'

                PHP,
            after: <<< 'PHP'

                HTML)),

                PHP
        );
    }

    /**
     * Render a text node.
     */
    protected function renderText(Node $node): string
    {
        return e($node->value);
    }

    /**
     * Retrieve the node's title information.
     *
     * @return array{ 0: Node|null, 1: int }
     */
    public function info(Node $title): array
    {
        // Once we hit the function reference section, the rules here change.
        // We will modify each result by nesting it one level deeper.
        if ($isFunctionRef = ($title->parent('set')?->hasId() && $title->parent('set')->id() === 'funcref')) {
            $this->levelModifier = 1;
        }

        if ($title->parent('book')?->hasId() && $title->parent('book')->id() === 'faq') {
            $this->levelModifier = 0;
        }

        return match (true) {
            $isFunctionRef => [$title->parent('set'), 1],
            $title->hasParent('book.set') => [$title->parent('book'), 1 + $this->levelModifier],
            $title->hasParent('chapter.book.set.set.set') => [$title->parent('chapter'), 2 + $this->levelModifier],
            $title->hasParent('chapter.book.set') => [$title->parent('chapter'), 2 + $this->levelModifier],
            $title->hasParent('info.chapter.book.set') => [$title->parent('info.chapter'), 2 + $this->levelModifier],
            $title->hasParent('info.legalnotice.info.set') => [$title->parent('info.legalnotice'), 1 + $this->levelModifier],
            $title->hasParent('info.preface.book.set') => [$title->parent('info.preface'), 1 + $this->levelModifier],
            $title->hasParent('info.section.chapter.book.set') => [$title->parent('info.section'), 3 + $this->levelModifier],
            $title->hasParent('preface.book.set.set.set') => [$title->parent('preface'), 2 + $this->levelModifier],
            $title->hasParent('sect1.chapter.book.set') => [$title->parent('sect1'), 3 + $this->levelModifier],
            $title->hasParent('section.chapter.book.set.set.set') && $title->parent('section')->hasId() => [$title->parent('section'), 3 + $this->levelModifier],
            $title->hasParent('set') => [$title->parent('set'), 1 + $this->levelModifier],
            default => [null, 0]
        };
    }

    /**
     * Retrieve all titles from the index.
     *
     * @return Collection<string, Title>
     */
    public function all(): Collection
    {
        return $this->allCache ??= collect(require_once $this->stream->path);
    }

    /**
     * Find the title based on it's ID.
     */
    public function find(string $id): Title
    {
        return tap($this->findMany(collect([$id]))->first(), function (?Title $title) use ($id) {
            if ($title === null) {
                throw new RuntimeException("Could not find title with id [{$id}].");
            }
        });
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
                    level: $level,
                    html: $result->isEmpty() ? new HtmlString('Home') : $title->html,
                ));

                return $result;
            }, collect([]));
    }
}

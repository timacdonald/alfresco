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

class TitleIndex implements Generator
{
    /**
     * Modifier to adjust the current heading level.
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
        protected Factory $render,
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
        $this->stream->write(<<<'PHP'
            <?php

            declare(strict_types=1);

            use Alfresco\Website\Title;
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
        // We only care about nodes that are title tags or that are the
        // children of title tags.
        $title = $node->name === 'title'
            ? $node
            : $node->ancestor('title');

        if ($title === null) {
            return '';
        }

        // We do not want to capture the stray "PHP Manual" title.
        // if ($title->parent('book')?->hasId('manual')) {
        //     return '';
        // }

        [$section, $level] = $this->info($title);

        if ($section === null) {
            return '';
        }

        return match ($node->name) {
            'title' => $this->renderTitle($node, $section, $level),
            '#text' => $this->renderText($node),
            'productname' => '',
            'literal', 'command', 'function' => $this->render->tag('code'),
            'classname' => $this->render->tag('var', [
                'class' => 'not-italic',
            ]),
            default => throw new RuntimeException(<<<ERROR
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
        $this->stream->write('];');
    }

    /**
     * Render a title node.
     */
    protected function renderTitle(Node $title, Node $section, int $level): Slotable
    {
        return $this->render->wrapper(
            before: <<<PHP
                    {$this->render->export($section->id())} => new Title(
                        id: {$this->render->export($section->id())},
                        lineage: '{$title->lineage()}',
                        level: {$level},
                        html: new HtmlString(<<<'HTML'

                PHP,
            after: <<<'PHP'

                HTML)),


                PHP,
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
     * Retrieve the node's title information.
     *
     * @return array{ 0: Node|null, 1: int }
     */
    public function info(Node $title): array
    {
        // Once we hit the function reference section the rules change, so we
        // will check if we are in the function reference or not.
        $isFunctionRef = (bool) $title->parent('set')?->hasId('funcref');

        // When in the function reference, we modify each title within the
        // function reference to be one level deeper.
        if ($isFunctionRef) {
            $this->levelModifier = 1;
        }

        // The FAQs should then revert and use no level modifier.
        if ($title->parent('book')?->hasId('faq')) {
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
            $title->hasParent('section.chapter.book.set.set.set') && $title->expectParent('section')->hasId() => [$title->parent('section'), 3 + $this->levelModifier],
            $title->hasParent('set') => [$title->parent('set'), 1 + $this->levelModifier],
            default => [null, 0],
        };
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

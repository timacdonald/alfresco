<?php

namespace Alfresco\Website;

use Alfresco\AbstractComponentFactory;
use Alfresco\CodeReplacer;
use Alfresco\ComponentFactory;
use Alfresco\Configuration;
use Alfresco\Container;
use Alfresco\Contracts\DependsOnIndexes;
use Alfresco\Contracts\Generator as GeneratorContract;
use Alfresco\Contracts\Slotable;
use Alfresco\Date;
use Alfresco\FileStreamFactory;
use Alfresco\Highlighter;
use Alfresco\Link;
use Alfresco\Node;
use Alfresco\Output;
use Alfresco\Stream;
use Illuminate\Support\Collection;
use RuntimeException;
use Safe\DateTimeImmutable;

use function Safe\file_get_contents;
use function Safe\preg_match;
use function Safe\preg_replace;
use function Safe\system;

class Generator implements DependsOnIndexes, GeneratorContract
{
    /**
     * Resolve from the container.
     */
    public static function resolve(Container $container, string $language): Generator
    {
        $config = $container->make(Configuration::class);

        $abstractComponentFactory = $container->make(AbstractComponentFactory::class);

        return new Generator(
            buildDirectory: $config->get('build_directory'),
            debug: $config->get('debug'),
            indexes: [
                TitleIndex::class => $container->make(TitleIndex::class, [$language]),
                EmptyChunkIndex::class => $container->make(EmptyChunkIndex::class, [$language]),
            ],
            streamFactory: $container->make(FileStreamFactory::class),
            output: $container->make(Output::class),
            render: $abstractComponentFactory->make($language),
            highlighter: $container->make(Highlighter::class),
            replace: $container->make(CodeReplacer::class, [
                $config->get('resource_directory').'/replacements',
            ]),
        );
    }

    /**
     * Create a new instance.
     *
     * @param  array{ Alfresco\Website\TitleIndex: TitleIndex, Alfresco\Website\EmptyChunkIndex: EmptyChunkIndex }  $indexes
     */
    public function __construct(
        protected string $buildDirectory,
        protected bool $debug,
        protected array $indexes,
        protected FileStreamFactory $streamFactory,
        protected Output $output,
        protected ComponentFactory $render,
        protected Highlighter $highlighter,
        protected CodeReplacer $replace,
    ) {
        //
    }

    /**
     * Set up.
     */
    public function setUp(): void
    {
        //
    }

    /**
     * Retrieve the stream for the given node.
     */
    public function stream(Node $node): Stream
    {
        return new Stream(
            "{$this->buildDirectory}/{$node->id()}.html",
            fn (string $path) => with($this->streamFactory->make($path, 1000), fn (Stream $stream) => [
                $stream->write(file_get_contents('resources/header.html'))
                    ->write($this->render->component('main')->before())
                    ->write(...),
                fn () => $stream->write($this->render->component('main')->after())
                    ->write($this->render->component('menu', [
                        'active' => $this->indexes[TitleIndex::class]->find($node->id()),
                        'items' => $this->indexes[TitleIndex::class]->heirachy(),
                        'empty' => $this->indexes[TitleIndex::class]->findMany(
                            $this->indexes[EmptyChunkIndex::class]->ids()
                        ),
                    ])->toString())
                    ->write(file_get_contents('resources/footer.html'))
                    ->close(),
            ]),
        );
    }

    /**
     * Determine if the generator should chunk.
     */
    public function shouldChunk(Node $node): bool
    {
        return Website::shouldChunk($node);
    }

    /**
     * Render the given node.
     */
    public function render(Node $node): Slotable|string
    {
        return with(match ($node->name) {
            '#cdata-section' => $this->renderCData($node),
            '#text' => $this->renderText($node),
            'abbrev' => $this->renderAbbrev($node),
            'abstract' => $this->renderAbstract($node),
            'acronym' => $this->renderAcronym($node),
            'alt' => $this->renderAlt($node),
            'author' => $this->renderAuthor($node),
            'authorgroup' => $this->renderAuthorGroup($node),
            'book' => $this->renderBook($node),
            'caution' => $this->renderCaution($node),
            'chapter' => $this->renderChapter($node),
            'classname' => $this->renderClassName($node),
            'code' => $this->renderCode($node),
            'command' => $this->renderCommand($node),
            'computeroutput' => $this->renderComputerOutput($node),
            'constant' => $this->renderConstant($node),
            'copyright' => $this->renderCopyright($node),
            'dbtimestamp' => $this->renderDbTimestamp($node),
            'editor' => $this->renderEditor($node),
            'emphasis' => $this->renderEmphasis($node),
            'entry' => $this->renderEntry($node),
            'envar' => $this->renderEnVar($node),
            'example' => $this->renderExample($node),
            'filename' => $this->renderFilename($node),
            'firstname' => $this->renderFirstName($node),
            'function' => $this->renderFunction($node),
            'holder' => $this->renderHolder($node),
            'imageobject' => $this->renderImageObject($node),
            'imagedata' => $this->renderImageData($node),
            'info' => $this->renderInfo($node),
            'informalexample' => $this->renderInformalExample($node),
            'informaltable' => $this->renderInformalTable($node),
            'interfacename' => $this->renderInterfaceName($node),
            'itemizedlist' => $this->renderItemizedList($node),
            'legalnotice' => $this->renderLegalNotice($node),
            'link' => $this->renderLink($node),
            'listitem' => $this->renderListItem($node),
            'literal' => $this->renderLiteral($node),
            'literallayout' => $this->renderLiteralLayout($node),
            'mediaobject' => $this->renderMediaObject($node),
            'member' => $this->renderMember($node),
            'note' => $this->renderNote($node),
            'option' => $this->renderOption($node),
            'optional' => $this->renderOptional($node),
            'orderedlist' => $this->renderOrderedList($node),
            'othercredit' => $this->renderOtherCredit($node),
            'othername' => $this->renderOtherName($node),
            'para' => $this->renderPara($node),
            'parameter' => $this->renderParameter($node),
            'personname' => $this->renderPersonName($node),
            'phpdoc' => $this->renderPhpDoc($node),
            'preface' => $this->renderPreface($node),
            'procedure' => $this->renderProcedure($node),
            'productname' => $this->renderProductName($node),
            'programlisting' => $this->renderProgramListing($node),
            'pubdate' => $this->renderPubDate($node),
            'replaceable' => $this->renderReplaceable($node),
            'row' => $this->renderRow($node),
            'screen' => $this->renderScreen($node),
            'sect1' => $this->renderSect1($node),
            'sect2' => $this->renderSect2($node),
            'sect3' => $this->renderSect3($node),
            'sect4' => $this->renderSect4($node),
            'section' => $this->renderSection($node),
            'set' => $this->renderSet($node),
            'simpara' => $this->renderSimPara($node),
            'simplelist' => $this->renderSimpleList($node),
            'step' => $this->renderStep($node),
            'surname' => $this->renderSurname($node),
            'synopsis' => $this->renderSynopsis($node),
            'systemitem' => $this->renderSystemItem($node),
            'table' => $this->renderTable($node),
            'tbody' => $this->renderTBody($node),
            'term' => $this->renderTerm($node),
            'tgroup' => $this->renderTGroup($node),
            'thead' => $this->renderTHead($node),
            'tip' => $this->renderTip($node),
            'title' => $this->renderTitle($node),
            'titleabbrev' => $this->renderTitleAbbrev($node),
            'type' => $this->renderType($node),
            'userinput' => $this->renderUserInput($node),
            'variablelist' => $this->renderVariableList($node),
            'varlistentry' => $this->renderVarListEntry($node),
            'varname' => $this->renderVarName($node),
            'warning' => $this->renderWarning($node),
            'xref' => $this->renderXref($node),
            'year' => $this->renderYear($node),
            default => tap('', fn () => dump('Unknown node', $node->name, $node->parents())),
        }, fn (string|Slotable $content) => $this->debug
            ? $this->withDebuggingInfo($node, $content)
            : $content);
    }

    /**
     * Tear down.
     */
    public function tearDown(): void
    {
        $this->output->line(<<< 'BASH'
            <dim>Building assets.</dim>
            BASH);

        system(<<< 'BASH'
            npx tailwindcss -i ./resources/style.css -o ./build/output/style.css
            cp ./resources/script.js ./build/output/script.js
            BASH);
    }

    /**
     * Retrieve the generator's indexes.
     *
     * @return array<int, GeneratorContract>
     */
    public function indexes(): array
    {
        return array_values($this->indexes);
    }

    /**
     * Render the CDATA node.
     */
    protected function renderCData(Node $node): Slotable|string
    {
        $content = preg_replace('/^\\n/', '', $node->value);

        // Example code that you would write in your editor.
        if (($programlisting = $node->parent('programlisting')) && $programlisting->hasRole()) {
            return $this->highlighter->highlight(
                $this->replace->replace($content),
                $programlisting->role()
            );
        }

        // Output that you would see in your browser or terminal.
        if (($screen = $node->parent('screen')) && $screen->hasRole()) {
            return $this->highlighter->highlight(
                $this->replace->replace($content),
                $screen->role()
            );
        }

        return e($content);
    }

    /**
     * Render the text node.
     */
    protected function renderText(Node $node): Slotable|string
    {
        // The screen tag contains code, so we gonna render some code. Smart.
        if ($screen = $node->ancestor('screen')) {
            // $value = trim($node->value);
            $value = $node->value;

            if ($screen->hasRole()) {
                return $this->highlighter->highlight(
                    $this->replace->replace($value),
                    $screen->role()
                );
            }

            return $value;
        }

        return e($node->value);
    }

    /**
     * An abbreviation, especially one followed by a period.
     *
     * @see https://tdg.docbook.org/tdg/5.2/abbrev.html
     *
     * @todo It would be nice if these had a "title" tag for accessiblity. We
     *       will likely need more indexers for this.
     */
    protected function renderAbbrev(Node $node): Slotable|string
    {
        return $this->render->tag('abbr');
    }

    /**
     * A summary.
     *
     * @see https://tdg.docbook.org/tdg/5.2/abstract.html
     */
    protected function renderAbstract(Node $node): Slotable|string
    {
        return '';
    }

    /**
     * An often pronounceable word made from the initial (or selected) letters of a name or phrase.
     *
     * @see https://tdg.docbook.org/tdg/5.2/acronym.html
     *
     * @todo It would be nice if these had a "title" tag for accessiblity. We
     *       will likely need more indexers for this.
     */
    protected function renderAcronym(Node $node): Slotable|string
    {
        return $this->render->tag('abbr');
    }

    /**
     * A text-only annotation, often used for accessibility.
     *
     * @see https://tdg.docbook.org/tdg/5.2/alt.html
     */
    protected function renderAlt(Node $node): Slotable|string
    {
        return '';
    }

    /**
     * The name of an individual author.
     *
     * This tag only appears at he beginning of the documentation to credit the
     * authors, so it does not need to be generic and handle any situation.
     *
     * @see https://tdg.docbook.org/tdg/5.2/author.html
     * @see self::renderAuthorGroup()
     */
    protected function renderAuthor(Node $node): Slotable|string
    {
        if (! $authorgroup = $node->parent('authorgroup')) {
            $this->unhandledNode($node, 'Generic "author" component not implemented.');
        }

        if ($authorgroup->id() === 'authors') {
            return $this->render->tag('li');
        }

        if ($authorgroup->id() === 'editors') {
            return '';
        }

        $this->unhandledNode($node, 'Unknown parent ID for "authorgroup".');
    }

    /**
     * A book.
     *
     * @see https://tdg.docbook.org/tdg/5.2/book.html
     */
    protected function renderBook(Node $node): Slotable|string
    {
        return '';
    }

    /**
     * The name of a class, in the object-oriented programming sense.
     *
     * @see https://tdg.docbook.org/tdg/5.2/classname.html
     */
    protected function renderClassName(Node $node): Slotable|string
    {
        return $this->render->component('inline-code')
            ->wrapSlot($this->render->component('link', [
                'link' => Link::internal("class.{$node->innerContent()}"),
            ]));
    }

    /**
     * Wrapper for author information when a document has multiple authors or
     * collaborators.
     *
     * This tag only appears at he beginning of the documentation to credit the
     * authors, so it does not need to be generic and handle any situation.
     *
     * @see https://tdg.docbook.org/tdg/5.2/authorgroup.html
     */
    protected function renderAuthorGroup(Node $node): Slotable|string
    {
        if ((! $set = $node->parent('info.set')) || $set->hasParent()) {
            $this->unhandledNode($node, 'Generic "authorgroup" component not implemented.');
        }

        if ($node->id() === 'authors') {
            return $this->render->component('authors');
        }

        if ($node->id() === 'editors') {
            return '';
        }

        $this->unhandledNode($node, 'Unknown ID for "authorgroup".');
    }

    /**
     * A note of caution.
     *
     * @see https://tdg.docbook.org/tdg/5.2/caution.html
     */
    protected function renderCaution(Node $node): Slotable|string
    {
        return $this->render->component('caution');
    }

    /**
     * A chapter, as of a book.
     *
     * @see https://tdg.docbook.org/tdg/5.2/chapter.html
     */
    protected function renderChapter(Node $node): Slotable|string
    {
        return '';
    }

    /**
     * An inline code fragment.
     *
     * @see https://tdg.docbook.org/tdg/5.2/code.html
     */
    protected function renderCode(Node $node): Slotable|string
    {
        return $this->render->component('inline-code');
    }

    /**
     * The name of an executable program or other software command.
     *
     * @see https://tdg.docbook.org/tdg/5.2/command.html
     */
    protected function renderCommand(Node $node): Slotable|string
    {
        return $this->render->component('inline-code');
    }

    /**
     * Data, generally text, displayed or presented by a computer.
     *
     * @see https://tdg.docbook.org/tdg/5.2/computeroutput.html
     */
    protected function renderComputerOutput(Node $node): Slotable|string
    {
        return $this->render->component('inline-code');
    }

    /**
     * A programming or system constant.
     *
     * @see https://tdg.docbook.org/tdg/5.2/constant.html
     */
    protected function renderConstant(Node $node): Slotable|string
    {
        // When a constant appear in a "title" we want to keep the change
        // in design to a minimum. We will just wrap this in a code tag.
        if ($node->hasParent('title')) {
            return $this->render->tag(
                as: 'code',
                attributes: [
                    // 'class' => 'font-sans',
                ],
            );
        }

        return $this->render->component('inline-code')->as('var');
    }

    /**
     * Copyright information about a document.
     *
     * @see https://tdg.docbook.org/tdg/5.2/copyright.html
     */
    protected function renderCopyright(Node $node): Slotable|string
    {
        return ' &copy; ';
    }

    /**
     * A timestamp processing instruction.
     *
     * @see http://www.sagehill.net/docbookxsl/Datetime.html
     * @see https://www.php.net/manual/en/datetime.format.php
     */
    protected function renderDbTimestamp(Node $node): Slotable|string
    {
        preg_match('/.*?format="(.*?)"/', $node->value, $matches);

        return with(new DateTimeImmutable, fn (DateTimeImmutable $now) => collect(mb_str_split($matches[1]))
            ->map(fn (string $component) => match ($component) {
                'a' => $now->format('D'),
                'A' => $now->format('l'),
                'b' => $now->format('M'),
                'c' => $now->format('c'),
                'B' => $now->format('F'),
                'd' => str_contains($node->value, ' padding="0"')
                    ? ltrim($now->format('d'), '0')
                    : $now->format('d'),
                'H' => str_contains($node->value, ' padding="0"')
                    ? ltrim($now->format('H'), '0')
                    : $now->format('H'),
                'j' => $now->format('z'),
                'm' => str_contains($node->value, ' padding="0"')
                    ? ltrim($now->format('m'), '0')
                    : $now->format('m'),
                'M' => str_contains($node->value, ' padding="0"')
                    ? ltrim($now->format('i'), '0')
                    : $now->format('i'),
                'S' => str_contains($node->value, ' padding="0"')
                    ? ltrim($now->format('s'), '0')
                    : $now->format('s'),
                'U' => $now->format('W'),
                'w' => (string) ($now->format('w') + 1), // spec has Sunday at 1. PHP has Sunday at 0.
                'x' => $now->format('Y-m-dP'),
                'X' => $now->format('H:i:sP'),
                'Y' => $now->format('Y'),
                default => $component,
            })->pipe(fn (Collection $components) => $this->render->tag(
                as: 'time',
                before: $components->implode(''),
                attributes: [
                    'datetime' => $now->format('c'),
                ],
            )->toString()));
    }

    /**
     * The name of the editor of a document.
     *
     * This tag only appears at he beginning of the documentation to credit the
     * authors, so it does not need to be generic and handle any situation.
     *
     * @see https://tdg.docbook.org/tdg/5.2/editor.html
     */
    protected function renderEditor(Node $node): Slotable|string
    {
        if (! $node->parent('authorgroup.info.set')?->hasNoParent()) {
            $this->unhandledNode($node, 'Generic "editor" component not impemented.');
        }

        return $this->render->component('editors');
    }

    /**
     * Emphasized text.
     *
     * @see https://tdg.docbook.org/tdg/5.2/emphasis.html
     */
    protected function renderEmphasis(Node $node): Slotable|string
    {
        return $this->render->tag('em');
    }

    /**
     * A cell in a table.
     *
     * @see https://tdg.docbook.org/tdg/5.2/entry.html
     */
    protected function renderEntry(Node $node): Slotable|string
    {
        return $this->render->tag(
            $node->hasParent('row.thead') ? 'th' : 'td'
        );
    }

    /**
     * A software environment variable.
     *
     * @see https://tdg.docbook.org/tdg/5.2/envar.html
     */
    protected function renderEnVar(Node $node): Slotable|string
    {
        return $this->render->component('inline-code')->as('var');
    }

    /**
     * A formal example, with a title.
     *
     * @see https://tdg.docbook.org/tdg/5.2/example.html
     *
     * @todo I think this needs improving. It might be a figure, but any
     *       children might need to know about the figure and be figcaption or
     *       something.
     */
    protected function renderExample(Node $node): Slotable|string
    {
        return $this->render->tag(
            as: 'figure',
            attributes: [
                'class' => 'my-6',
            ],
        );
    }

    /**
     * The name of a file.
     *
     * @see https://tdg.docbook.org/tdg/5.2/filename.html
     */
    protected function renderFilename(Node $node): Slotable|string
    {
        // When a filename appear in a "title" we want to keep the change
        // in design to a minimum. We will just wrap this in a code tag.
        if ($node->hasParent('title')) {
            return $this->render->tag(
                as: 'code',
                attributes: [
                    // 'class' => 'font-sans',
                ],
            );
        }

        return $this->render->component('emphasised-literal');
    }

    /**
     * A given name of a person.
     *
     * This tag only appears at he beginning of the documentation to credit the
     * authors, so it does not need to be generic and handle any situation.
     *
     * @see https://tdg.docbook.org/tdg/5.2/firstname.html
     */
    protected function renderFirstName(Node $node): Slotable|string
    {
        return $this->render->inlineText();
    }

    /**
     * The name of a function or subroutine, as in a programming language.
     *
     * @see https://tdg.docbook.org/tdg/5.2/function.html
     */
    protected function renderFunction(Node $node): Slotable|string
    {
        return $this->render->component('inline-code')
            ->wrapSlot($this->render->component('link', [
                'link' => Link::internal("function.{$node->innerContent()}"),
            ]));
    }

    /**
     * Pointer to external image data.
     *
     * @see https://tdg.docbook.org/tdg/5.2/imagedata.html
     */
    protected function renderImageData(Node $node): Slotable|string
    {
        // TODO: this needs to point to a public image. We need an indexer that
        // publishes the files, then we can just `pull` the image when we encounter
        // it while generating. Should also include the `alt` text.
        return $this->render->tag(
            as: 'img',
            attributes: [
                'src' => $node->attribute('fileref'),
            ],
        );
    }

    /**
     * A wrapper for image data and its associated meta-information.
     *
     * @see https://tdg.docbook.org/tdg/5.2/imageobject.html
     */
    protected function renderImageObject(Node $node): Slotable|string
    {
        return '';
    }

    /**
     * The name of the individual or organization that holds a copyright.
     *
     * This tag is only used on the homepage to highlight the "PHP
     * Documentation Group"
     *
     * @see https://tdg.docbook.org/tdg/5.2/holder.html
     * @see self::copyright()
     */
    protected function renderHolder(Node $node): Slotable|string
    {
        return $this->render->inlineText(after: '.');
    }

    /**
     * A wrapper for information about a component or other block.
     *
     * @see https://tdg.docbook.org/tdg/5.2/info.html
     */
    protected function renderInfo(Node $node): Slotable|string
    {
        return '';
    }

    /**
     * A displayed example without a title.
     *
     * @todo I think this needs improving. It might be a figure, but any
     *       children might need to know about the figure and be figcaption or
     *       something.
     *
     * @see https://tdg.docbook.org/tdg/5.2/informalexample.html
     */
    protected function renderInformalExample(Node $node): Slotable|string
    {
        return $this->render->tag(
            as: 'figure',
            attributes: [
                'class' => 'my-6',
            ],
        );
    }

    /**
     * An HTML table without a title.
     *
     * @see https://tdg.docbook.org/tdg/5.2/html.informaltable.html
     */
    protected function renderInformalTable(Node $node): Slotable|string
    {
        return $this->render->tag(
            as: 'table',
            attributes: [
                'class' => 'my-6',
            ]
        );
    }

    /**
     * The name of an interface.
     *
     * @see https://tdg.docbook.org/tdg/5.2/interfacename.html
     */
    protected function renderInterfaceName(Node $node): Slotable|string
    {
        return $this->render->component('inline-code')
            ->wrapSlot($this->render->component('link', [
                'link' => Link::internal("class.{$node->innerContent()}"),
            ]));
    }

    /**
     * A list in which each entry is marked with a bullet or other dingbat.
     *
     * @see https://tdg.docbook.org/tdg/5.2/itemizedlist.html
     */
    protected function renderItemizedList(Node $node): Slotable|string
    {
        return $this->render->component('unordered-list');
    }

    /**
     * A statement of legal obligations or requirements.
     *
     * @see https://tdg.docbook.org/tdg/5.2/legalnotice.html
     */
    protected function renderLegalNotice(Node $node): Slotable|string
    {
        return '';
    }

    /**
     * A hypertext link.
     *
     * @see https://tdg.docbook.org/tdg/5.2/link.html
     */
    protected function renderLink(Node $node): Slotable|string
    {
        // All titles now contain "hash" links. I feel this is more valuable
        // than providing in-title links. We don't wanna be wrapping links
        // within links. That is a bad time for everyone.
        if ($node->hasAncestor('title')) {
            return '';
        }

        return $this->render->component('link', [
            'link' => $node->link(),
        ]);
    }

    /**
     * A wrapper for the elements of a list item.
     *
     * @see https://tdg.docbook.org/tdg/5.2/listitem.html
     */
    protected function renderListItem(Node $node): Slotable|string
    {
        return $this->render->tag('li');
    }

    /**
     * A displayed media object (video, audio, image, etc.).
     *
     * @see https://tdg.docbook.org/tdg/5.2/mediaobject.html
     */
    protected function renderMediaObject(Node $node): Slotable|string
    {
        return '';
    }

    /**
     * An element of a simple list.
     *
     * @see https://tdg.docbook.org/tdg/5.2/member.html
     */
    protected function renderMember(Node $node): Slotable|string
    {
        return $this->render->tag('li');
    }

    /**
     * Inline text that is some literal value.
     *
     * @see https://tdg.docbook.org/tdg/5.2/literal.html
     */
    protected function renderLiteral(Node $node): Slotable|string
    {
        return $this->render->component('inline-code');
    }

    /**
     * A block of text in which line breaks and white space are to be reproduced faithfully.
     *
     * @see https://tdg.docbook.org/tdg/5.2/literallayout.html
     */
    protected function renderLiteralLayout(Node $node): Slotable|string
    {
        return $this->render->tag('pre');
    }

    /**
     * A message set off from the text.
     *
     * @see https://tdg.docbook.org/tdg/5.2/note.html
     */
    protected function renderNote(Node $node): Slotable|string
    {
        return $this->render->component('note');
    }

    /**
     * An option for a software command.
     *
     * @see https://tdg.docbook.org/tdg/5.2/option.html
     */
    protected function renderOption(Node $node): Slotable|string
    {
        return $this->render->component('emphasised-literal');
    }

    /**
     * Optional information.
     *
     * @see https://tdg.docbook.org/tdg/5.2/optional.html
     */
    protected function renderOptional(Node $node): Slotable|string
    {
        if (! $node->hasAncestor('synopsis')) {
            $this->unhandledNode($node, 'Not sure how to handle this outside of synopsis');
        }

        return $this->render->wrapper(
            before: '[',
            after: ']',
        );
    }

    /**
     * A list in which each entry is marked with a sequentially incremented label.
     *
     * @see https://tdg.docbook.org/tdg/5.2/orderedlist.html
     */
    protected function renderOrderedList(Node $node): Slotable|string
    {
        return $this->render->component('ordered-list', [
            'type' => $node->numeration(),
        ]);
    }

    /**
     * A person or entity, other than an author or editor, credited in a document.
     *
     * This tag only appears at he beginning of the documentation to credit the
     * authors, so it does not need to be generic and handle any situation.
     *
     * @see https://tdg.docbook.org/tdg/5.2/othercredit.html
     */
    protected function renderOtherCredit(Node $node): Slotable|string
    {
        if (! $authorGroup = $node->parent('authorgroup')) {
            $this->unhandledNode($node, 'Generic "othercredit" component not implemented.');
        }

        if ($authorGroup->id() === 'authors') {
            return $this->render->tag('li');
        }

        $this->unhandledNode($node, 'Unknown parent ID for othercredit.');
    }

    /**
     * A component of a person's name that is not a first name, surname, or lineage.
     *
     * @see https://tdg.docbook.org/tdg/5.2/othername.html
     */
    protected function renderOtherName(Node $node): Slotable|string
    {
        return '';
    }

    /**
     * A paragraph.
     *
     * @see https://tdg.docbook.org/tdg/5.2/para.html
     */
    protected function renderPara(Node $node): Slotable|string
    {
        // Putting paragraph tags in a `<li>` breaks the HTML flow. We don't
        // need it here...as far as I can tell.
        if ($node->hasParent('listitem')) {
            return '';
        }

        return $this->render->component('paragraph');
    }

    /**
     * A value or a symbolic reference to a value.
     *
     * @see https://tdg.docbook.org/tdg/5.2/parameter.html
     */
    protected function renderParameter(Node $node): Slotable|string
    {
        return $this->render->component('emphasised-literal');
    }

    /**
     * The personal name of an individual.
     *
     * @see https://tdg.docbook.org/tdg/5.2/personname.html
     */
    protected function renderPersonName(Node $node): Slotable|string
    {
        return '';
    }

    /**
     * Unused tag.
     */
    protected function renderPhpDoc(Node $node): Slotable|string
    {
        return '';
    }

    /**
     * Introductory matter preceding the first chapter of a book.
     *
     * @see https://tdg.docbook.org/tdg/5.2/preface.html
     */
    protected function renderPreface(Node $node): Slotable|string
    {
        return '';
    }

    /**
     * A list of operations to be performed in a well-defined sequence.
     *
     * @see https://tdg.docbook.org/tdg/5.2/procedure.html
     */
    protected function renderProcedure(Node $node): Slotable|string
    {
        return $this->render->component('ordered-list');
    }

    /**
     * The formal name of a product.
     *
     * @see https://tdg.docbook.org/tdg/5.2/productname.html
     */
    protected function renderProductName(Node $node): Slotable|string
    {
        return '';
    }

    /**
     * A literal listing of all or part of a program.
     *
     * @see https://tdg.docbook.org/tdg/5.2/programlisting.html
     */
    protected function renderProgramListing(Node $node): Slotable|string
    {
        return $this->render->component('program-listing');
    }

    /**
     * The date of publication of a document.
     *
     * @see https://tdg.docbook.org/tdg/5.2/pubdate.html
     */
    protected function renderPubDate(Node $node): Slotable|string
    {
        if ($node->parent('info.set')?->hasNoParent()) {
            return $this->render->inlineText(
                before: 'Published ',
                after: '.'
            );
        }

        $this->unhandledNode($node, 'Generic pubdate component not implemented.');
    }

    /**
     * Content that may or must be replaced by the user.
     *
     * @see https://tdg.docbook.org/tdg/5.2/replaceable.html
     */
    protected function renderReplaceable(Node $node): Slotable|string
    {
        return $this->render->wrapper(
            before: '{',
            after: '}',
        );
    }

    /**
     * A row in a table.
     *
     * @see https://tdg.docbook.org/tdg/5.2/row.html
     */
    protected function renderRow(Node $node): Slotable|string
    {
        return $this->render->tag('tr');
    }

    /**
     * Text that a user sees or might see on a computer screen.
     *
     * @see https://tdg.docbook.org/tdg/5.2/screen.html
     */
    protected function renderScreen(Node $node): Slotable|string
    {
        return $this->render->component('screen');
    }

    /**
     * A top-level section of document.
     *
     * @see https://tdg.docbook.org/tdg/5.2/sect1.html
     */
    protected function renderSect1(Node $node): Slotable|string
    {
        return '';
    }

    /**
     * A subsection within a sect1.
     *
     * @see https://tdg.docbook.org/tdg/5.2/sect2.html
     */
    protected function renderSect2(Node $node): Slotable|string
    {
        return '';
    }

    /**
     * A subsection within a sect2.
     *
     * @see https://tdg.docbook.org/tdg/5.2/sect3.html
     */
    protected function renderSect3(Node $node): Slotable|string
    {
        return '';
    }

    /**
     * A subsection within a sect3.
     *
     * @see https://tdg.docbook.org/tdg/5.2/sect4.html
     */
    protected function renderSect4(Node $node): Slotable|string
    {
        return '';
    }

    /**
     * A recursive section.
     *
     * @see https://tdg.docbook.org/tdg/5.2/section.html
     */
    protected function renderSection(Node $node): Slotable|string
    {
        return '';
    }

    /**
     * A collection of books.
     *
     * @see https://tdg.docbook.org/tdg/5.2/set.html
     */
    protected function renderSet(Node $node): Slotable|string
    {
        return '';
    }

    /**
     * A paragraph that contains only text and inline markup, no block elements.
     *
     * @see https://tdg.docbook.org/tdg/5.2/simpara.html
     */
    protected function renderSimPara(Node $node): Slotable|string
    {
        return $this->renderPara($node);
    }

    /**
     * An undecorated list of single words or short phrases.
     *
     * @see https://tdg.docbook.org/tdg/5.2/simplelist.html
     */
    protected function renderSimpleList(Node $node): Slotable|string
    {
        return $this->render->component('unordered-list');
    }

    /**
     * A unit of action in a procedure.
     *
     * @see https://tdg.docbook.org/tdg/5.2/step.html
     * @see self::renderProcedure()
     */
    protected function renderStep(Node $node): Slotable|string
    {
        return $this->render->tag('li');
    }

    /**
     * An inherited or family name; in western cultures the last name.
     *
     * This tag only appears at he beginning of the documentation to credit the
     * authors, so it does not need to be generic and handle any situation.
     *
     * @see https://tdg.docbook.org/tdg/5.2/surname.html
     */
    protected function renderSurname(Node $node): Slotable|string
    {
        return $this->render->inlineText();
    }

    /**
     * A general-purpose element for representing the syntax of commands or functions.
     *
     * @see https://tdg.docbook.org/tdg/5.2/synopsis.html
     */
    protected function renderSynopsis(Node $node): Slotable|string
    {
        // Maybe not this?
        return $this->render->component('program-listing');
    }

    /**
     * A system-related item or term.
     *
     * @see https://tdg.docbook.org/tdg/5.2/systemitem.html
     */
    protected function renderSystemItem(Node $node): Slotable|string
    {
        return $this->render->component('emphasised-literal');
    }

    /**
     * A formal (captioned) HTML table in a document.
     *
     * @see https://tdg.docbook.org/tdg/5.2/html.table.html
     */
    protected function renderTable(Node $node): Slotable|string
    {
        return $this->render->tag(
            as: 'table',
            attributes: [
                'class' => 'my-6',
            ],
        );
    }

    /**
     * A wrapper for the rows of an HTML table or informal HTML table.
     *
     * @see https://tdg.docbook.org/tdg/5.2/html.tbody.html
     */
    protected function renderTBody(Node $node): Slotable|string
    {
        return $this->render->tag('tbody');
    }

    /**
     * The word or phrase being defined or described in a variable list.
     *
     * @see https://tdg.docbook.org/tdg/5.2/term.html
     */
    protected function renderTerm(Node $node): Slotable|string
    {
        return $this->render->tag(
            as: 'dt',
            attributes: [
                'id' => $node->parent('varlistentry')->hasId()
                    ? $node->parent('varlistentry')->id()
                    : false,
            ],
        );
    }

    /**
     * A wrapper for the main content of a table, or part of a table.
     *
     * @see https://tdg.docbook.org/tdg/5.2/tgroup.html
     */
    protected function renderTGroup(Node $node): Slotable|string
    {
        return '';
    }

    /**
     * A table header consisting of one or more rows in an HTML table.
     *
     * @see https://tdg.docbook.org/tdg/5.2/html.thead.html
     */
    protected function renderTHead(Node $node): Slotable|string
    {
        return $this->render->tag('thead');
    }

    /**
     * A suggestion to the user, set off from the text.
     *
     * @see https://tdg.docbook.org/tdg/5.2/tip.html
     */
    protected function renderTip(Node $node): Slotable|string
    {
        return $this->render->component('tip');
    }

    /**
     * The text of the title of a section of a document or of a formal block-level element.
     *
     * @see https://tdg.docbook.org/tdg/5.2/title.html
     */
    protected function renderTitle(Node $node): Slotable|string
    {
        // The headings in the preface are a special case. We will just force
        // them to be level:2 without trying to do anything special.
        if ($node->ancestor('section')?->hasAncestor('preface')) {
            return $this->render->component('title', [
                'level' => 2,
                'link' => Link::fragment($node->hasId() ? $node->id() : $node->innerContent()),
            ]);
        }

        // Currently we hard code all note titles `h3`. I would love this to be
        // improved.
        if ($node->hasAncestor('note')) {
            return $this->render->component('note.title', [
                'link' => Link::fragment($node->innerContent()),
            ]);
        }

        if ($node->hasAncestor('tip')) {
            return $this->render->component('tip.title', [
                'link' => Link::fragment($node->innerContent()),
            ]);
        }

        // Currently we hard code all example titles `h2`. I would love this to
        // be improved.
        if ($node->hasParent('info.example')) {
            return $this->render->component('title', [
                'level' => 2,
                'link' => Link::fragment($node->innerContent()),
            ]);
        }

        /*
         * Now we will check if the node is contained within the title index.
         * If it is, we assume it is a page title, as the index only contains
         * the main page chunked titles.
         */

        [$section] = $this->indexes[TitleIndex::class]->info($node);

        if ($section !== null) {
            return $this->render->component('title', [
                'level' => 1,
                'link' => Link::fragment($section->id()),
            ]);
        }

        /**
         * @todo this needs to caclculate the title depth to better set the "level"
         */

        return $this->render->component('title', [
            'level' => 2,
            'link' => Link::fragment($node->innerContent()),
        ]);
    }

    /**
     * The abbreviation of a title.
     *
     * @see https://tdg.docbook.org/tdg/5.2/titleabbrev.html
     */
    protected function renderTitleAbbrev(Node $node): Slotable|string
    {
        // TODO: would be cool if we could return a "skip" instruction to just skip over the entire node and its inner content.
        // This is only used for the menu.
        return '';
    }

    /**
     * Data entered by the user.
     *
     * @see https://tdg.docbook.org/tdg/5.2/userinput.html
     */
    protected function renderUserInput(Node $node): Slotable|string
    {
        return $this->render->component('inline-code');
    }

    /**
     * The classification of a value.
     *
     * @see https://tdg.docbook.org/tdg/5.2/type.html
     */
    protected function renderType(Node $node): Slotable|string
    {
        return $this->render->component('inline-code');
    }

    /**
     * A list in which each entry is composed of a set of one or more terms and an associated description.
     *
     * @see https://tdg.docbook.org/tdg/5.2/variablelist.html
     */
    protected function renderVariableList(Node $node): Slotable|string
    {
        return $this->render->tag('dl');
    }

    /**
     * A wrapper for a set of terms and the associated description in a variable list.
     *
     * @see https://tdg.docbook.org/tdg/5.2/varlistentry.html
     */
    protected function renderVarListEntry(Node $node): Slotable|string
    {
        return '';
    }

    /**
     * The name of a variable.
     *
     * @see https://tdg.docbook.org/tdg/5.2/varname.html
     *
     * @todo link to the variable within the documentation. This will likely
     *       require further indexing. We can use `wrapSlot` to wrap the slot
     *       in a link tag.
     */
    protected function renderVarName(Node $node): Slotable|string
    {
        return $this->render->component('inline-code')
            ->as('var');
        // ->wrapSlot($this->render->component('link', [
        //     'link' => Link::internal('#todo'),
        // ]));
    }

    /**
     * An admonition set off from the text.
     *
     * @see https://tdg.docbook.org/tdg/5.2/warning.html
     */
    protected function renderWarning(Node $node): Slotable|string
    {
        return $this->render->component('warning');
    }

    /**
     * A cross reference to another part of the document.
     *
     * @see https://tdg.docbook.org/tdg/5.2/xref.html
     */
    protected function renderXref(Node $node): Slotable|string
    {
        return $this->render->component('link', [
            'link' => $node->link(),
            'text' => $node->link()->destination,
        ]);
    }

    /**
     * The year of publication of a document.
     *
     * @see https://tdg.docbook.org/tdg/5.2/year.html
     * @see self::copyright()
     */
    protected function renderYear(Node $node): Slotable|string
    {
        return '';
    }

    /**
     * Bail on unhandled node.
     */
    protected function unhandledNode(Node $node, string $reason): never
    {
        throw new RuntimeException("Unhandled node of tag [{$node->name}]. Reason: {$reason}.");
    }

    /**
     * Add debugging information to the node.
     */
    protected function withDebuggingInfo(Node $node, string|Slotable $content): string|Slotable
    {
        if (in_array($node->name, ['#text', '#cdata-section'])) {
            return $content;
        }

        [$name, $id, $role] = [
            $node->name,
            $node->hasId() ? $node->id() : '',
            $node->hasRole() ? $node->role() : '',
        ];

        if (is_string($content)) {
            return <<< HTML
                <!-- data-name="{$name}" data-id="{$id}" data-role="{$role}" -->
                HTML.$content;
        }

        return $this->render->wrapper(
            before: <<< HTML
                <!-- data-name="{$name}" data-id="{$id}" data-role="{$role}" -->
                HTML.$content->before(),
            after: $content->after(),
        );
    }
}

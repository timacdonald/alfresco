<?php

namespace Alfresco;

use Alfresco\Contracts\Generator;
use Alfresco\Contracts\Slotable;
use Closure;
use RuntimeException;
use SplObjectStorage;

class Process
{
    /**
     * The currently active stream.
     *
     * @var SplObjectStorage<Generator, Stream>
     */
    protected SplObjectStorage $streams;

    /**
     * Pending "closers" to run.
     *
     * @var array<int, array{ 0: string, 1: Node }>
     */
    protected array $closers = [];

    /**
     * Process the manual against the given generators.
     *
     * @param  iterable<Generator>  $generators
     */
    public function __invoke(Manual $manual, iterable $generators, ?Closure $onTick = null): void
    {
        $this->streams = new SplObjectStorage;

        $onTick ??= fn () => null;
        $iteration = 0;

        foreach ($generators as $generator) {
            $generator->setUp();
        }

        while ($node = $manual->advance()) {
            if ($node->isDoctype()) {
                continue;
            }

            $onTick($node, $iteration++);

            foreach ($generators as $generator) {
                if ($iteration === 1) {
                    $this->streams->attach($generator, $generator->stream($node));
                }

                if ($node->isOpeningElement()) {
                    $this->handleOpeningElement($generator, $node);

                    continue;
                }

                if ($node->isTextContent()) {
                    $this->handleTextContent($generator, $node);

                    continue;
                }

                if ($node->isClosingElement()) {
                    $this->handleClosingElement($generator, $node);

                    continue;
                }

                if ($node->isWhitespace()) {
                    $this->handleWhitespace($generator, $node);

                    continue;
                }

                if ($node->isCData()) {
                    $this->handleCData($generator, $node);

                    continue;
                }

                if ($node->isProcessingInstruction()) {
                    $this->handleProcessingInstruction($generator, $node);

                    continue;
                }

                if ($node->isComment()) {
                    $this->handleComment($generator, $node);

                    continue;
                }

                throw new RuntimeException("Encountered an unhandled node of type [{$node->type}] with the name [{$node->name}].");
            }
        }

        foreach ($generators as $generator) {
            $this->writePendingClosers($generator);

            $generator->tearDown();

            $this->streams[$generator]->close();

            $this->streams->detach($generator);
        }
    }

    /**
     * Handle an opening element.
     */
    protected function handleOpeningElement(Generator $generator, Node $node): void
    {
        if ($generator->shouldChunk($node)) {
            $this->writePendingClosers($generator);

            $this->streams[$generator]->close();

            $this->streams->attach($generator, $generator->stream($node));
        }

        $this->write($generator, $generator->render($node), $node);
    }

    /**
     * Handle an closing element.
     */
    protected function handleClosingElement(Generator $generator, Node $node): void
    {
        if ($this->matchesNextPendingCloser($node)) {
            $this->writeNextCloser($generator);
        }
    }

    /**
     * Handle a whitespace node.
     */
    protected function handleWhitespace(Generator $generator, Node $node): void
    {
        //
    }

    /**
     * Handle a CDATA node.
     */
    protected function handleCData(Generator $generator, Node $node): void
    {
        $this->write($generator, $generator->render($node), $node);
    }

    /**
     * Handle a text node.
     */
    protected function handleTextContent(Generator $generator, Node $node): void
    {
        $this->write($generator, $generator->render($node), $node);
    }

    /**
     * Handle a processing instruction node.
     */
    protected function handleProcessingInstruction(Generator $generator, Node $node): void
    {
        $this->write($generator, $generator->render($node), $node);
    }

    /**
     * Handle a comment node.
     */
    protected function handleComment(Generator $generator, Node $node): void
    {
        //
    }

    /**
     * Determine if the node matches the next pending closer.
     */
    protected function matchesNextPendingCloser(Node $node): bool
    {
        if ($this->closers === []) {
            return false;
        }

        return with($this->closers[array_key_last($this->closers)][1], function (Node $openingNode) use ($node) {
            return $openingNode->name === $node->name && $openingNode->depth === $node->depth;
        });
    }

    /**
     * Write all pending closers.
     */
    protected function writePendingClosers(Generator $generator): void
    {
        while ($this->closers !== []) {
            $this->writeNextCloser($generator);
        }
    }

    /**
     * Write the next pending closer.
     */
    protected function writeNextCloser(Generator $generator): void
    {
        if ($this->closers !== []) {
            $this->write($generator, ...array_pop($this->closers));
        }
    }

    /**
     * Write the node's content for the generator.
     */
    protected function write(Generator $generator, string|Slotable $content, Node $node): void
    {
        if ($content === '') {
            return;
        }

        if (is_string($content)) {
            $this->streams[$generator]->write($content);

            return;
        }

        $this->write($generator, $content->before(), $node);

        if ($node->isSelfClosing) {
            $this->write($generator, $content->after(), $node);
        } else {
            $this->closers[] = [$content->after(), $node];
        }
    }
}

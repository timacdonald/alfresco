<?php

declare(strict_types=1);

namespace Alfresco\Website;

use Closure;
use Stringable;
use Alfresco\Manual\Node;
use Alfresco\Stream\Stream;
use Alfresco\Render\Factory;
use Alfresco\Contracts\Slotable;
use Alfresco\Contracts\Generator;
use Illuminate\Support\Collection;
use Alfresco\Stream\FileStreamFactory;
use Illuminate\Config\Repository as Configuration;

class FunctionIndex implements Generator
{
    /**
     * The empty chunk output stream.
     */
    protected Stream $stream;

    /**
     * The cache of all functions.
     *
     * @var Collection<string, Method>
     */
    protected ?Collection $allCache;

    protected int $paramNumber = 1;

    protected ?string $description = null;

    /**
     * Create a new instance.
     */
    public function __construct(
        protected Factory $render,
        protected FileStreamFactory $streamFactory,
        protected Configuration $config,
    ) {
        $this->stream = $this->streamFactory->make(
            "{$this->config->get('index_directory')}/website/{$this->config->get('language')}/functions.php",
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

            use Alfresco\Website\Method;
            use Alfresco\Website\Parameter;

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
        if ($node->name === 'refpurpose') {
            // Str::squish
            $this->description = $node->innerContent();
        }

        if ($node->name === 'methodsynopsis' && $node->parent('refsect1')) {
            $wrapper = $this->render->wrapper(
                before: "    new Method(\n        description:".$this->render->export($this->description).',',
                after: new class(fn () => ($this->paramNumber = 1)) implements Stringable
                {
                    public function __construct(protected Closure $callback)
                    {
                        //
                    }

                    public function __toString(): string
                    {
                        ($this->callback)();

                        return "\n    ),\n\n";
                    }
                },
            );

            $this->description = null;

            return $wrapper;
        }

        if ($node->name === 'methodparam' && $node->parent('methodsynopsis.refsect1')) {
            return $this->render->wrapper(
                before: "\n        p".($this->paramNumber++).': new Parameter([',
                after: '),',
            );
        }

        if (
            $node->name === 'type' &&
            $node->parent('methodsynopsis.refsect1') &&
            $node->hasAttribute('class') &&
            $node->attribute('class') === 'union'
        ) {
            return $this->render->wrapper(
                before: "\n        returnTypes: [",
                after: '],',
            );
        }

        if (
            $node->name === 'type' &&
            $node->parent('methodsynopsis.refsect1')
        ) {
            return $this->render->wrapper(
                before: "\n        returnTypes: [",
                after: '],',
            );
        }

        if (
            $node->name === 'type' &&
            $node->parent('methodparam.methodsynopsis.refsect1') &&
            $node->hasAttribute('class') &&
            $node->attribute('class') === 'union'
        ) {
            return $this->render->wrapper(
                before: '[',
                after: '],',
            );
        }

        if ($node->name === '#text') {
            // return type
            if ($node->parent('type.methodsynopsis.refsect1') || $node->parent('type.type.methodsynopsis.refsect1')) {
                return "'{$node->value}',";
            }

            // method name
            if ($node->parent('methodname.methodsynopsis.refsect1')) {
                return "\n        name:".$this->render->export($node->value).',';
            }

            // param type
            if ($node->parent('type.methodparam.methodsynopsis.refsect1') || $node->parent('type.type.methodparam.methodsynopsis.refsect1')) {
                return $this->render->export($node->value).',';
            }

            // param name
            if ($node->parent('parameter.methodparam.methodsynopsis.refsect1')) {
                return '],'.$this->render->export($node->value);
            }
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
     * Retrieve all functions from the index.
     *
     * @return Collection<string, Method>
     */
    public function all(): Collection
    {
        return $this->allCache ??= collect(require $this->stream->path)
            ->reduce(function ($carry, $function) {
                $carry[$function->name] = collect([
                    ...($carry[$function->name] ?? []),
                    $function,
                ]);

                return $carry;
            }, collect([]));
    }
}

<?php

declare(strict_types=1);

namespace Alfresco\Manual;

use RuntimeException;
use XMLReader;

class Manual
{
    /**
     * Docbook attribute namespace.
     */
    public const XMLNS_DOCBOOK = 'http://docbook.org/ns/docbook';

    /**
     * XLink attribute namespace.
     */
    public const XMLNS_XLINK = 'http://www.w3.org/1999/xlink';

    /**
     * XML attribute namespace.
     */
    public const XMLNS_XML = 'http://www.w3.org/XML/1998/namespace';

    /**
     * The node's parent nodes.
     *
     * @var list<Node>
     */
    protected array $parents = [];

    /**
     * The current node's index.
     */
    protected int $cursorIndex = -1;

    /**
     * The previous node's depth.
     */
    protected int $lastDepth = -1;

    /**
     * Create a new instance.
     */
    public function __construct(
        protected XMLReader $xml,
    ) {
        //
    }

    /**
     * Read the next node of the manual.
     */
    public function read(): ?Node
    {
        if (! $this->xml->read()) {
            $this->xml->close();

            return null;
        }

        $previousSibling = $this->previousSibling();

        $this->cursorIndex = $this->cursorIndex + 1;

        $this->lastDepth = $this->xml->depth;

        return tap($this->node($this->cursorIndex, $previousSibling), $this->rememberNode(...));
    }

    /**
     * The current node instance.
     */
    protected function node(int $index, ?string $previousSibling): Node
    {
        return new Node(
            name: $this->xml->name,
            type: $this->xml->nodeType,
            value: $this->xml->value,
            depth: $this->xml->depth,
            language: $this->xml->xmlLang,
            namespace: $this->xml->namespaceURI,
            isSelfClosing: $this->xml->isEmptyElement,
            attributes: $this->attributes(),
            previousSibling: $previousSibling,
            parent: $this->parents[$this->xml->depth - 1] ?? null,
            innerContentResolver: function () use ($index) {
                if ($index !== $this->cursorIndex) {
                    throw new RuntimeException('Unable to read the XML contents as the cursor has moved past the current node.');
                }

                return $this->xml->readInnerXml();
            },
        );
    }

    /**
     * Remember node for use as a parent.
     */
    protected function rememberNode(Node $node): void
    {
        if ($this->xml->nodeType === XMLReader::ELEMENT) {
            $this->parents[$this->xml->depth] = $node;
        }
    }

    /**
     * The current node's previous sibling tag name.
     */
    protected function previousSibling(): ?string
    {
        return $this->lastDepth >= $this->xml->depth
            ? $this->parents[$this->xml->depth]->name ?? null
            : null;
    }

    /**
     * The attributes for the current node.
     *
     * @return array<string, array<string, string>>
     */
    protected function attributes(): array
    {
        $attributes = [];

        if ($this->xml->hasAttributes) {
            $this->xml->moveToFirstAttribute();

            do {
                $attributes[$this->xml->namespaceURI ?: static::XMLNS_DOCBOOK][$this->xml->localName] = $this->xml->value;
            } while ($this->xml->moveToNextAttribute());

            $this->xml->moveToElement();
        }

        return $attributes; // @phpstan-ignore return.type
    }
}

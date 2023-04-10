<?php

namespace Alfresco;

use RuntimeException;
use XMLReader;

class Manual
{
    public const XMLNS_DOCBOOK = 'http://docbook.org/ns/docbook';

    public const XMLNS_PHD = 'http://www.php.net/ns/phd';

    public const XMLNS_XLINK = 'http://www.w3.org/1999/xlink';

    public const XMLNS_XML = 'http://www.w3.org/XML/1998/namespace';

    /**
     * @var array<int, Node>
     */
    protected array $stack = [];

    protected int $cursorIndex = -1;

    protected int $lastDepth = -1;

    public function __construct(protected XMLReader $xml)
    {
        //
    }

    public function advance(): ?Node
    {
        if (! $this->xml->read()) {
            $this->xml->close();

            return null;
        }

        $previousSibling = $this->previousSibling();

        $this->cursorIndex = $this->cursorIndex + 1;

        $this->lastDepth = $this->xml->depth;

        return tap($this->node($this->cursorIndex, $previousSibling), function (Node $node) {
            if ($this->xml->nodeType === XMLReader::ELEMENT) {
                $this->stack[$this->xml->depth] = $node;
            }
        });
    }

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
            parent: $this->stack[$this->xml->depth - 1] ?? null,
            innerContentResolver: function () use ($index) {
                if ($index !== $this->cursorIndex) {
                    throw new RuntimeException('Unable to read the XML contents as the cursor has moved past the current node.');
                }

                return $this->xml->readInnerXml();
            },
        );
    }

    protected function previousSibling(): ?string
    {
        return $this->lastDepth >= $this->xml->depth
            ? $this->stack[$this->xml->depth]->name ?? null
            : null;
    }

    /**
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

        return $attributes;
    }
}

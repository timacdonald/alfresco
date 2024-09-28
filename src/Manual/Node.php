<?php

declare(strict_types=1);

namespace Alfresco\Manual;

use Alfresco\Support\Link;
use Closure;
use RuntimeException;
use XMLReader;

class Node
{
    /**
     * Create a new instance.
     *
     * @param  array<string, array<string, string>>  $attributes
     */
    public function __construct(
        public string $name,
        public int $type,
        public string $value,
        public int $depth,
        public string $language,
        public string $namespace,
        public bool $isSelfClosing,
        public array $attributes,
        public ?string $previousSibling,
        public ?Node $parent,
        public Closure $innerContentResolver,
    ) {
        //
    }

    /**
     * Retrieve node's ID attribute.
     */
    public function id(): string
    {
        if (! $this->hasId()) {
            throw new RuntimeException('Node is missing ID');
        }

        return $this->attributes[Manual::XMLNS_XML]['id'];
    }

    /**
     * Determine if the node has an ID attribute.
     */
    public function hasId(?string $id = null): bool
    {
        if ($id === null) {
            return isset($this->attributes[Manual::XMLNS_XML]['id']);
        } else {
            return ($this->attributes[Manual::XMLNS_XML]['id'] ?? null) === $id;
        }
    }

    /**
     * Retrieve the node's role attribute.
     */
    public function role(): string
    {
        return $this->attributes[Manual::XMLNS_DOCBOOK]['role'];
    }

    /**
     * Determine if the node has a role attribute.
     */
    public function hasRole(): bool
    {
        return isset($this->attributes[Manual::XMLNS_DOCBOOK]['role']);
    }

    /**
     * Retrieve the referenced link.
     */
    public function link(): Link
    {
        if (isset($this->attributes[Manual::XMLNS_DOCBOOK]['linkend'])) {
            return Link::internal($this->attributes[Manual::XMLNS_DOCBOOK]['linkend']);
        }

        return with($this->attributes[Manual::XMLNS_XLINK]['href'], function (string $href) {
            if (str_starts_with('https://www.php.net/', $href)) {
                return Link::internal($href);
            }

            return Link::external($href);
        });
    }

    /**
     * Retrieve the given attribute.
     */
    public function attribute(string $name): string
    {
        return $this->attributes[Manual::XMLNS_DOCBOOK][$name];
    }

    /**
     * Determine if the node is whitespace.
     */
    public function isWhitespace(): bool
    {
        return in_array($this->type, [
            XMLReader::WHITESPACE,
            XMLReader::SIGNIFICANT_WHITESPACE,
        ], true);
    }

    /**
     * Determine if the node is an opening element.
     */
    public function isOpeningElement(): bool
    {
        return $this->type === XMLReader::ELEMENT;
    }

    /**
     * Determine if the node is a closing element.
     */
    public function isClosingElement(): bool
    {
        return $this->type === XMLReader::END_ELEMENT;
    }

    /**
     * Determine if the node is text content.
     */
    public function isTextContent(): bool
    {
        return $this->type === XMLReader::TEXT;
    }

    /**
     * Determine if the node is CDATA.
     */
    public function isCData(): bool
    {
        return $this->type === XMLReader::CDATA;
    }

    /**
     * Determine if the node is a processing instruction.
     */
    public function isProcessingInstruction(): bool
    {
        return $this->type === XMLReader::PI;
    }

    /**
     * Determine if the node is a comment.
     */
    public function isComment(): bool
    {
        return $this->type === XMLReader::COMMENT;
    }

    /**
     * Determine if the node is a doctype.
     */
    public function isDoctype(): bool
    {
        return $this->type === XMLReader::DOC_TYPE;
    }

    /**
     * Determine if the node has the given previous sibling.
     */
    public function hasPreviousSibling(string $sibling): bool
    {
        return $this->previousSibling === $sibling;
    }

    /**
     * Retrieve the given parent.
     *
     * Supports dot notation, e.g., path: "type.methodparameter"
     */
    public function parent(string $path): ?Node
    {
        $node = $this;

        foreach (explode('.', $path) as $name) {
            if ($node->parent?->name === $name) {
                $node = $node->parent;
            } else {
                return null;
            }
        }

        return $node;
    }

    /**
     * Determine if the node has the given parent.
     *
     * Supports dot notation, e.g., path: "type.methodparameter"
     */
    public function hasParent(?string $path = null): bool
    {
        if ($path === null) {
            return $this->parent !== null;
        }

        return $this->parent($path) !== null;
    }

    /**
     * Determine if the node has no parent.
     */
    public function hasNoParent(): bool
    {
        return $this->parent === null;
    }

    /**
     * Retrieve the node's lineage as a dot separated path.
     */
    public function lineage(): ?string
    {
        if ($this->parent === null) {
            return null;
        }

        $node = $this;
        $parents = '';

        while ($node = $node->parent) {
            $parents .= "{$node->name}";

            if ($node->hasId()) {
                $parents .= "[{$node->id()}]";
            }

            $parents .= '.';
        }

        return rtrim($parents, '.');
    }

    /**
     * Retrieve the number of ancestors the node has.
     */
    public function countAncestors(string $name): int
    {
        $node = $this;
        $count = 0;

        while ($node = $node->parent) {
            if ($node->name === $name) {
                $count++;
            }
        }

        return $count;
    }

    /**
     * Retrieve the given ancestor for the node.
     */
    public function ancestor(string $name): ?Node
    {
        $node = $this;

        while ($node = $node->parent) {
            if ($node->name === $name) {
                return $node;
            }
        }

        return null;
    }

    /**
     * Determine if the node has the given ancestor.
     */
    public function hasAncestor(string $name): bool
    {
        return $this->ancestor($name) !== null;
    }

    /**
     * Retrieve the list numeration type fo the given node.
     */
    public function numeration(): string
    {
        if ($this->name !== 'orderedlist') {
            throw new RuntimeException('Numeration is only accessible on orderedlist nodes.');
        }

        return match ($this->attributes[Manual::XMLNS_DOCBOOK]['numeration'] ?? null) {
            'upperalpha' => 'A',
            'loweralpha' => 'a',
            'upperroman' => 'I',
            'lowerroman' => 'i',
            null => '1',
            default => throw new RuntimeException('Unknown numeration type.'),
        };
    }

    /**
     * Determine if the node objects to chunking.
     */
    public function objectsToChunking(): bool
    {
        return isset($this->attributes[Manual::XMLNS_DOCBOOK]['annotations'])
            && str_contains($this->attributes[Manual::XMLNS_DOCBOOK]['annotations'], 'chunk:false');
    }

    /**
     * Retrieve the node's inner content.
     */
    public function innerContent(): string
    {
        return once($this->innerContentResolver);
    }

    /**
     * Determine if the node has the given attribute.
     */
    public function hasAttribute(string $name): bool
    {
        foreach ($this->attributes as $namespace => $attributes) {
            if (array_key_exists($name, $attributes)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the node has any attributes.
     */
    public function hasAnyAttributes(): bool
    {
        return $this->attributes !== [];
    }
}

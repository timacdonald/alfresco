<?php

namespace Alfresco;

use Closure;
use RuntimeException;
use XMLReader;

class Node
{
    protected ?string $innerContentCache;

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

    public function id(): string
    {
        if (! $this->hasId()) {
            throw new RuntimeException('Node is missing ID');
        }

        return $this->attributes[Manual::XMLNS_XML]['id'];
    }

    public function exportId(): string
    {
        return var_export($this->id(), true);
    }

    public function exportValue(): string
    {
        return var_export($this->value, true);
    }

    public function hasId(): bool
    {
        return isset($this->attributes[Manual::XMLNS_XML]['id']);
    }

    public function role(): string
    {
        return $this->attributes[Manual::XMLNS_DOCBOOK]['role'];
    }

    public function hasRole(): bool
    {
        return isset($this->attributes[Manual::XMLNS_DOCBOOK]['role']);
    }

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

    public function attribute(string $name): string
    {
        return $this->attributes[Manual::XMLNS_DOCBOOK][$name];
    }

    public function isWhitespace(): bool
    {
        return in_array($this->type, [
            XMLReader::WHITESPACE,
            XMLReader::SIGNIFICANT_WHITESPACE,
        ]);
    }

    public function isOpeningElement(): bool
    {
        return $this->type === XMLReader::ELEMENT;
    }

    public function isClosingElement(): bool
    {
        return $this->type === XMLReader::END_ELEMENT;
    }

    public function isTextContent(): bool
    {
        return $this->type === XMLReader::TEXT;
    }

    public function isCData(): bool
    {
        return $this->type === XMLReader::CDATA;
    }

    public function isProcessingInstruction(): bool
    {
        return $this->type === XMLReader::PI;
    }

    public function isComment(): bool
    {
        return $this->type === XMLReader::COMMENT;
    }

    public function isDoctype(): bool
    {
        return $this->type === XMLReader::DOC_TYPE;
    }

    public function hasPreviousSibling(string $sibling): bool
    {
        return $this->previousSibling === $sibling;
    }

    public function hasParent(?string $path = null): bool
    {
        if ($path === null) {
            return $this->parent !== null;
        }

        return $this->parent($path) !== null;
    }

    public function hasNoParent(): bool
    {
        return $this->parent === null;
    }

    public function parents(): ?string
    {
        if ($this->parent === null) {
            return null;
        }

        $node = $this;
        $parents = '';

        while ($node = $node->parent) {
            $parents .= "{$node->name}.";
        }

        return rtrim($parents, '.');
    }

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

    public function hasAncestor(string $name): bool
    {
        return $this->ancestor($name) !== null;
    }

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

    public function objectsToChunking(): bool
    {
        return isset($this->attributes[Manual::XMLNS_DOCBOOK]['annotations'])
            && str_contains($this->attributes[Manual::XMLNS_DOCBOOK]['annotations'], 'chunk:false');
    }

    public function innerContent(): string
    {
        return $this->innerContentCache ??= ($this->innerContentResolver)();
    }

    public function hasAttribute(string $name): bool
    {
        foreach ($this->attributes as $namespace => $attributes) {
            if (array_key_exists($name, $attributes)) {
                return true;
            }
        }

        return false;
    }

    public function hasAnyAttributes(): bool
    {
        return $this->attributes !== [];
    }
}

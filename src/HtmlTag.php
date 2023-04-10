<?php

namespace Alfresco;

use Alfresco\Contracts\Slotable;
use RuntimeException;
use Stringable;

class HtmlTag implements Slotable
{
    /**
     * @param  array<string, string|bool|array<int, string>>  $attributes
     */
    public function __construct(
        protected string $as,
        protected array $attributes,
        protected string|Stringable $before,
        protected string|Stringable $after,
        protected ?Slotable $slot,
    ) {
        //
    }

    public function before(): string
    {
        if ($this->isVoidTag()) {
            return "<{$this->as}{$this->attributeList()}>";
        }

        return "<{$this->as}{$this->attributeList()}>{$this->before}{$this->slot?->before()}";
    }

    public function after(): string
    {
        if ($this->isVoidTag()) {
            return '';
        }

        return "{$this->slot?->after()}{$this->after}</{$this->as}>";
    }

    protected function attributeList(): string
    {
        return with($this->attributes(), fn (array $attributes) => $attributes === []
            ? ''
            : ' '.implode(' ', $attributes));
    }

    /**
     * @return array<int, string>
     */
    protected function attributes(): array
    {
        $attributes = array_map(function (string|array|bool $value, string $key) {
            if ($value === false) {
                return false;
            }

            $key = trim($key);

            if ($value === true) {
                return $key;
            }

            if (! is_array($value)) {
                $value = explode(' ', $value);
            }

            $value = array_map(trim(...), $value);

            return $key.'="'.implode(' ', $value).'"';
        }, $this->attributes, array_keys($this->attributes));

        $attributes = array_filter($attributes, fn (string|false $value) => $value !== false);

        return array_values($attributes);
    }

    protected function isVoidTag(): bool
    {
        return in_array($this->as, [
            'area',
            'base',
            'br',
            'col',
            'embed',
            'hr',
            'img',
            'input',
            'link',
            'meta',
            'source',
            'track',
            'wbr',
        ]);
    }

    /**
     * @param  array<string, string|bool|array<int, string>>  $attributes
     */
    public function withAttributes(array $attributes): HtmlTag
    {
        return new HtmlTag(
            as: $this->as,
            attributes: array_merge_recursive($this->attributes, $attributes),
            before: $this->before,
            after: $this->after,
            slot: $this->slot,
        );
    }

    public function wrapSlot(Slotable $slot): HtmlTag
    {
        return new HtmlTag(
            as: $this->as,
            attributes: $this->attributes,
            before: $this->before,
            after: $this->after,
            slot: $slot,
        );
    }

    public function as(string $as): HtmlTag
    {
        return new HtmlTag(
            as: $as,
            attributes: $this->attributes,
            before: $this->before,
            after: $this->after,
            slot: $this->slot,
        );
    }

    public function toString(): string
    {
        if ($this->slot !== null) {
            throw new RuntimeException('Unable to render a tag with a content wrapper.');
        }

        return $this->before().$this->after();
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}

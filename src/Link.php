<?php

namespace Alfresco;

use Illuminate\Support\Str;

class Link
{
    public static function internal(string $destination): Link
    {
        return new Link($destination, true);
    }

    public static function external(string $destination): Link
    {
        return new Link($destination, false);
    }

    public static function fragment(string $destination): Link
    {
        return new Link('#'.Str::slug(strip_tags($destination)), true);
    }

    /**
     * Create a new instance.
     */
    public function __construct(
        public string $destination,
        public bool $isInternal,
    ) {
        //
    }

    public function destinationWithoutFragmentHash(): string
    {
        return Str::after($this->destination, '#');
    }
}

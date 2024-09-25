<?php

declare(strict_types=1);

namespace Alfresco\Support;

use Illuminate\Support\Str;

class Link
{
    /**
     * Create a link to an internal location.
     */
    public static function internal(string $destination): Link
    {
        return new Link($destination, true);
    }

    /**
     * Create a link to an extenal location.
     */
    public static function external(string $destination): Link
    {
        return new Link($destination, false);
    }

    /**
     * Create a link to a fragment on the current page.
     */
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

    /**
     * Retrieve the destination without the fragment.
     */
    public function destinationWithoutFragmentHash(): string
    {
        return Str::after($this->destination, '#');
    }
}

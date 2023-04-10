<?php

namespace Alfresco\Website;

use Alfresco\HtmlString;
use Illuminate\Support\Collection;

class Title
{
    /**
     * Create a new instance.
     *
     * @param  Collection<int, Title>  $children
     */
    public function __construct(
        public string $id,
        public int $level,
        public HtmlString $html,
        public Collection $children = new Collection
    ) {
        //
    }

    /**
     * Determine if the title is the same or has the given title as a child.
     */
    public function isOrHasChild(Title $title): bool
    {
        return $this->is($title) || $this->hasChild($title);
    }

    /**
     * Determine if the title is the same as the given title.
     */
    public function is(Title $title): bool
    {
        return $this->id === $title->id;
    }

    /**
     * Determine if the title has the given title as a child.
     */
    public function hasChild(Title $title): bool
    {
        return $this->children->contains(
            fn (Title $child) => $child->is($title) || $child->hasChild($title)
        );
    }
}

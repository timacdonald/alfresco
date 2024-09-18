<?php

namespace Alfresco\Website;

use Illuminate\Support\Collection;

class Method
{
    protected Collection $parameters;

    /**
     * Create a new instance.
     */
    public function __construct(
        public ?string $description,
        public string $name,
        public array $return = [],
        public ?Parameter $p1 = null,
        public ?Parameter $p2 = null,
        public ?Parameter $p3 = null,
        public ?Parameter $p4 = null,
        public ?Parameter $p5 = null,
        public ?Parameter $p6 = null,
        public ?Parameter $p7 = null,
        public ?Parameter $p8 = null,
        public ?Parameter $p9 = null,
        public ?Parameter $p10 = null,
        public ?Parameter $p11 = null,
        public ?Parameter $p12 = null,
        public ?Parameter $p13 = null,
        public ?Parameter $p14 = null,
        public ?Parameter $p15 = null,
        public ?Parameter $p16 = null,
        public ?Parameter $p17 = null,
        public ?Parameter $p18 = null,
        public ?Parameter $p19 = null,
        public ?Parameter $p20 = null,
    ) {
        $this->description = $this->description ?? '';
    }

    public function returnTypes(): Collection
    {
        return collect($this->return);
    }

    public function parameters(): Collection
    {
        return $this->parameters ??= collect([
            $this->p1,
            $this->p2,
            $this->p3,
            $this->p4,
            $this->p5,
            $this->p6,
            $this->p7,
            $this->p8,
            $this->p9,
            $this->p10,
            $this->p11,
            $this->p12,
            $this->p13,
            $this->p14,
            $this->p15,
            $this->p16,
            $this->p17,
            $this->p18,
            $this->p19,
            $this->p20,
        ])->filter()->values();
    }
}

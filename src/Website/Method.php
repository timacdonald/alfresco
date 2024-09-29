<?php

declare(strict_types=1);

namespace Alfresco\Website;

use Illuminate\Support\Collection;

class Method
{
    /**
     * The method parameters.
     *
     * @var Collection<int, Parameter>
     */
    public Collection $parameters;

    /**
     * The method return types.
     *
     * @var Collection<int, string>
     */
    public Collection $returnTypes;

    /**
     * The method description.
     */
    public string $description;

    /**
     * Create a new instance.
     *
     * @param  list<string>  $returnTypes
     */
    public function __construct(
        ?string $description,
        public string $name,
        array $returnTypes = [],
        ?Parameter $p1 = null,
        ?Parameter $p2 = null,
        ?Parameter $p3 = null,
        ?Parameter $p4 = null,
        ?Parameter $p5 = null,
        ?Parameter $p6 = null,
        ?Parameter $p7 = null,
        ?Parameter $p8 = null,
        ?Parameter $p9 = null,
        ?Parameter $p10 = null,
        ?Parameter $p11 = null,
        ?Parameter $p12 = null,
        ?Parameter $p13 = null,
        ?Parameter $p14 = null,
        ?Parameter $p15 = null,
        ?Parameter $p16 = null,
        ?Parameter $p17 = null,
        ?Parameter $p18 = null,
        ?Parameter $p19 = null,
        ?Parameter $p20 = null,
    ) {
        $this->description = $description ?? '';

        $this->returnTypes = collect($returnTypes);

        $this->parameters ??= collect([ // @phpstan-ignore assign.propertyType
            $p1, $p2, $p3, $p4, $p5,
            $p6, $p7, $p8, $p9, $p10,
            $p11, $p12, $p13, $p14, $p15,
            $p16, $p17, $p18, $p19, $p20,
        ])->filter()->values();
    }
}

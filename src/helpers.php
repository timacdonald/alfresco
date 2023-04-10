<?php

namespace Alfresco;

use Closure;
use Illuminate\Support\Benchmark;

function measure(Closure $callback): float
{
    return round(Benchmark::measure($callback) / 1000, 2);
}

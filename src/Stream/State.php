<?php

declare(strict_types=1);

namespace Alfresco\Stream;

enum State
{
    case Unopened;
    case Open;
    case Closed;
}

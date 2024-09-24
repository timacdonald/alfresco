<?php

namespace Alfresco\Stream;

enum State
{
    case Unopened;
    case Open;
    case Closed;
}

<?php

declare(strict_types=1);

namespace Medas\TaskRunner;

enum TaskStatus: int
{
    case Pending = 0;
    case Running = 1;
    case Complete = 2;
    case Failed = 3;
}

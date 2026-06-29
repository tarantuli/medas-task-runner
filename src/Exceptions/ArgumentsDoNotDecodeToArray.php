<?php

declare(strict_types=1);

namespace Medas\TaskRunner\Exceptions;

use Medas\Core\Exceptions\BaseException;

class ArgumentsDoNotDecodeToArray extends BaseException
{
    public function __construct(mixed $arguments)
    {
        parent::__construct(get_debug_type($arguments));
    }

    public function pattern(): string
    {
        return 'execution arguments must decode to array, %s given';
    }
}

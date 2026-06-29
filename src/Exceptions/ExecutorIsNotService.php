<?php

declare(strict_types=1);

namespace Medas\TaskRunner\Exceptions;

use Medas\Core\Exceptions\BaseException;

class ExecutorIsNotService extends BaseException
{
    public function __construct(string $className)
    {
        parent::__construct($className);
    }

    public function pattern(): string
    {
        return 'task executor with class name %s is not a service';
    }
}

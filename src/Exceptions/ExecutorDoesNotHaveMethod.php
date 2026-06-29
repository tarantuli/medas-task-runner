<?php

declare(strict_types=1);

namespace Medas\TaskRunner\Exceptions;

use Medas\Core\Exceptions\BaseException;

class ExecutorDoesNotHaveMethod extends BaseException
{
    public function __construct(string $className, string $methodName)
    {
        parent::__construct($className, $methodName);
    }

    public function pattern(): string
    {
        return 'executor %s does not have method %s';
    }
}

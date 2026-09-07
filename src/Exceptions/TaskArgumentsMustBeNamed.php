<?php

declare(strict_types=1);

namespace Medas\TaskRunner\Exceptions;

use Medas\Core\Exceptions\BaseException;

class TaskArgumentsMustBeNamed extends BaseException
{
    public function __construct(string $className, string $methodName, int $position)
    {
        parent::__construct($className, $methodName, $position);
    }

    public function pattern(): string
    {
        return "%s::%s was enqueued with a positional argument at index %d - task arguments must be "
            . "keyed by parameter name (e.g. ['order' => \$order]), so they bind by name even if the "
            . "method signature changes before the task runs";
    }
}

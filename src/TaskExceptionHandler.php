<?php

declare(strict_types=1);

namespace Medas\TaskRunner;

/**
 * An implementation of this interface should handle exceptions thrown by tasks in such a way
 * that the task runner can continue running other tasks.
 */
interface TaskExceptionHandler
{
    public function handle(\Throwable $e): void;
}

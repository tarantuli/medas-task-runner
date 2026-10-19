<?php

declare(strict_types=1);

namespace Medas\TaskRunner\Events;

use Medas\TaskRunner\Task;

// A task that failed for good: no retry is left, or none was allowed. Dispatched after
// the task is released as failed, so the code that queued it can learn that the work
// will not happen.
readonly class TaskFailed
{
    public function __construct(
        public Task       $task,
        public \Throwable $exception,
    )
    {
    }
}

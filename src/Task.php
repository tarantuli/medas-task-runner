<?php

declare(strict_types=1);

namespace Medas\TaskRunner;

use Medas\Core\Interfaces\HasId;

interface Task extends HasId
{
    /**
     * The fully qualified class name of the task
     */
    public string $className { get; }

    public string $methodName { get; }

    /**
     * The JSON encoded arguments to the method call
     */
    public string $arguments { get; }

    /**
     * Should default to TaskStatus::Pending
     */
    public TaskStatus $status { get; }

    /**
     * Should be null if the task may be run immediately
     */
    public \DateTime|null $scheduledAt { get; }

    /**
     * If not null, if executiom fails, the task should be retried after the given number of seconds.
     */
    public int|null $retryAfter { get; }

    /**
     * The maximum number of times the task may be attempted to retry. Null means no limit.
     */
    public int|null $maxAttempts { get; }

    /**
     * The number of times the task has been attempted to run.
     */
    public int $attempts { get; }

    /**
     * Should default to null. Its length must be equal to the value of the marker-length config option.
     */
    public string|null $lockMarker { get; }

    /**
     * Should default to null
     */
    public \DateTime|null $lockedAt { get; }
}

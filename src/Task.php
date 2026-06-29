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
     * Should default to null
     */
    public string|null $lockMarker { get; }

    /**
     * Should default to null
     */
    public \DateTime|null $lockedAt { get; }
}

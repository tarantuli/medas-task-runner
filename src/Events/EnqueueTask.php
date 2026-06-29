<?php

declare(strict_types=1);

namespace Medas\TaskRunner\Events;

readonly class EnqueueTask
{
    public function __construct(
        public string         $className,
        public string         $methodName,
        public array          $arguments = [],
        public \DateTime|null $scheduledAt = null,
    )
    {
    }
}

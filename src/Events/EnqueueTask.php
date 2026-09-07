<?php

declare(strict_types=1);

namespace Medas\TaskRunner\Events;

use Medas\TaskRunner\Exceptions\TaskArgumentsMustBeNamed;

readonly class EnqueueTask
{
    public function __construct(
        public string         $className,
        public string         $methodName,
        public array          $arguments = [],
        public \DateTime|null $scheduledAt = null,
        public int|null       $retryAfter = null,
        public int|null       $maxAttempts = null,
    )
    {
        foreach (array_keys($arguments) as $key) {
            if (is_int($key)) {
                throw new TaskArgumentsMustBeNamed($className, $methodName, $key);
            }
        }
    }
}

<?php

declare(strict_types=1);

namespace Medas\TaskRunner\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

#[Service]
readonly class MaximumLockDuration implements ConfigOption
{
    public function __construct(
        private TaskRunnerGroup $group,
    )
    {
    }

    public function group(): ConfigGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'maximum-lock-duration';
    }

    public function description(): string
    {
        return 'The maximum duration of a task lock in seconds. After this duration, the task may be released.';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): int
    {
        // 5 minutes
        return 300;
    }
}

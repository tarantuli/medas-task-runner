<?php

declare(strict_types=1);

namespace Medas\TaskRunner\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

#[Service]
readonly class SleepDuration implements ConfigOption
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
        return 'sleep-duration';
    }

    public function description(): string
    {
        return 'The number of seconds to sleep between checking for tasks to run if the queue is empty';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): int
    {
        return 5;
    }
}

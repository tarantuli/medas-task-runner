<?php

declare(strict_types=1);

namespace Medas\TaskRunner\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};
use Medas\TaskRunner\BasicTask;

#[Service]
readonly class TaskClass implements ConfigOption
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
        return 'task-class';
    }

    public function description(): string
    {
        return 'The name of the task class to use';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): string
    {
        return BasicTask::class;
    }
}

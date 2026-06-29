<?php

declare(strict_types=1);

namespace Medas\TaskRunner\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

#[Service]
readonly class MarkerLength implements ConfigOption
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
        return 'marker-length';
    }

    public function description(): string
    {
        return 'The length of the marker used to lock a task';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): int
    {
        return 16;
    }
}

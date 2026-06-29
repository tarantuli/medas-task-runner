<?php

declare(strict_types=1);

namespace Medas\TaskRunner\ConfigOptions;

use Medas\Core\{Attributes\Service, Interfaces\ConfigGroup, Interfaces\ConfigOption};

#[Service]
readonly class MaximumLifetime implements ConfigOption
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
        return 'maximum-lifetime';
    }

    public function description(): string
    {
        return 'The maximum lifetime of a task runner process in seconds.';
    }

    public function hasDefault(): bool
    {
        return true;
    }

    public function default(): int
    {
        return 60;
    }
}

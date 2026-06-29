<?php

declare(strict_types=1);

namespace Medas\TaskRunner\ConsoleCommands;

use Medas\Console\Commands\{BaseConsoleCommand, CommandInput, ConsoleCommandGroup};
use Medas\Core\Attributes\Service;
use Medas\TaskRunner\Releaser;

#[Service]
readonly class ReleaseExpiredLocks extends BaseConsoleCommand
{
    public function __construct(
        private Releaser        $taskReleaser,
        private TaskRunnerGroup $group,
    )
    {
    }

    public function group(): ConsoleCommandGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'release-expired-locks';
    }

    public function description(): string
    {
        return 'Releases expired locks';
    }

    public function process(CommandInput $input): void
    {
        $this->taskReleaser->releaseExpiredLocks();
    }
}

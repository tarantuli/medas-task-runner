<?php

declare(strict_types=1);

namespace Medas\TaskRunner\ConsoleCommands;

use Medas\Console\Commands\{BaseConsoleCommand, CommandInput, ConsoleCommandGroup};
use Medas\Core\Attributes\Service;
use Medas\TaskRunner\TaskRunner;

#[Service]
readonly class Run extends BaseConsoleCommand
{
    public function __construct(
        private TaskRunner      $taskRunner,
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
        return 'run';
    }

    public function description(): string
    {
        return 'Runs the task runner for the specified time';
    }

    public function process(CommandInput $input): void
    {
        $this->taskRunner->run();
    }
}

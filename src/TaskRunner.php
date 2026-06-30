<?php

declare(strict_types=1);

namespace Medas\TaskRunner;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\Entrypoint,
    Attributes\Service,
    Interfaces\ServiceManager
};
use Medas\Json\JsonEncoder;

#[Service, Entrypoint]
readonly class TaskRunner
{
    public function __construct(
        private JsonEncoder               $jsonEncoder,
        private Locker                    $locker,
        private Releaser                  $releaser,
        private ServiceManager            $serviceManager,
        private TaskExceptionHandler|null $exceptionHandler,

        #[ConfigValue(ConfigOptions\MaximumLifetime::class)]
        private int                       $maximumLifetime,

        #[ConfigValue(ConfigOptions\SleepDuration::class)]
        private int                       $sleepDuration,
    )
    {
    }

    public function run(): void
    {
        $startTime = microtime(true);
        $maxTime = $startTime + $this->maximumLifetime;

        while (microtime(true) < $maxTime) {
            $task = $this->locker->lock();

            if ($task === null) {
                sleep($this->sleepDuration);
            }
            else {
                try {
                    $this->execute($task);
                    $this->releaser->release($task, TaskStatus::Complete);
                }
                catch (\Throwable $e) {
                    if ($this->retryAllowed($task)) {
                        $this->releaser->scheduleRetry($task, $task->retryAfter);
                    }
                    else {
                        $this->releaser->release($task, TaskStatus::Failed);
                    }

                    if ($this->exceptionHandler !== null) {
                        $this->exceptionHandler->handle($e);
                    }
                    else {
                        throw $e;
                    }
                }
            }
        }
    }

    private function execute(Task $task): void
    {
        $executor = $this->serviceManager->resolve($task->className);

        if (!is_object($executor)) {
            throw new Exceptions\ExecutorIsNotService($task->className);
        }

        if (!method_exists($executor, $task->methodName)) {
            throw new Exceptions\ExecutorDoesNotHaveMethod($task->className, $task->methodName);
        }

        $arguments = $this->jsonEncoder->decode($task->arguments);

        if (!is_array($arguments)) {
            throw new Exceptions\ArgumentsDoNotDecodeToArray($arguments);
        }

        $executor->{$task->methodName}(...$arguments);
    }

    private function retryAllowed(Task $task): bool
    {
        if ($task->retryAfter === null) {
            return false;
        }

        if ($task->maxAttempts !== null && $task->attempts >= $task->maxAttempts) {
            return false;
        }

        return true;
    }
}

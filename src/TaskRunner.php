<?php

declare(strict_types=1);

namespace Medas\TaskRunner;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\Entrypoint,
    Attributes\Service,
    Interfaces\EntityManager,
    Interfaces\ObjectInstantiator,
    Interfaces\ServiceManager
};
use Medas\EntityManager\Attributes\Entity;
use Medas\Json\JsonEncoder;

#[Service, Entrypoint]
readonly class TaskRunner
{
    public function __construct(
        private EntityManager             $entityManager,
        private JsonEncoder               $jsonEncoder,
        private Locker                    $locker,
        private ObjectInstantiator        $objectInstantiator,
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
                        $this->exceptionHandler->handle($task, $e);
                    }
                    else {
                        throw $e;
                    }
                }
            }
        }
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

    /**
     * Processes queued tasks until the queue is empty, then returns - unlike run(),
     * which loops for its configured lifetime and sleeps when idle. Failures are
     * re-thrown rather than retried, so a synchronous caller (notably a test) sees
     * them immediately. For draining the queue on demand, not the daemon path.
     */
    public function drain(): void
    {
        while (($task = $this->locker->lock()) !== null) {
            try {
                $this->execute($task);
                $this->releaser->release($task, TaskStatus::Complete);
            }
            catch (\Throwable $e) {
                $this->releaser->release($task, TaskStatus::Failed);

                throw $e;
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

        $method = new \ReflectionMethod($task->className, $task->methodName);
        $arguments = $this->hydrateEntities($method, $arguments);
        $arguments = $this->objectInstantiator->resolveMethodParameters($method, $arguments);

        $executor->{$task->methodName}(...$arguments);
    }

    /**
     * @param array<string, mixed> $arguments
     * @return array<string, mixed>
     */
    private function hydrateEntities(\ReflectionMethod $method, array $arguments): array
    {
        foreach ($method->getParameters() as $parameter) {
            if (!array_key_exists($parameter->name, $arguments) || $arguments[$parameter->name] === null) {
                continue;
            }

            $type = $parameter->getType();

            if (!$type instanceof \ReflectionNamedType || $type->isBuiltin()) {
                continue;
            }

            $class = $type->getName();

            if (attribute(Entity::class, new \ReflectionClass($class)) === null) {
                continue;
            }

            $arguments[$parameter->name] = $this->entityManager->get(
                $class,
                $arguments[$parameter->name]
            );
        }

        return $arguments;
    }
}

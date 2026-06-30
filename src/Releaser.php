<?php

declare(strict_types=1);

namespace Medas\TaskRunner;

use Medas\Core\Attributes\{ConfigValue, Service};
use Medas\EntityManager\Filters\LessThan;
use Medas\StorageManager\Interfaces\{ActionExecutor, Builders\UpdateBuilder, Store};
use Medas\StorageManager\StorageManager;
use Medas\StorageManager\StoreController;

#[Service]
readonly class Releaser
{
    private Store $store;
    private UpdateBuilder $updateBuilder;
    private ActionExecutor $actionExecutor;

    public function __construct(
        #[ConfigValue(ConfigOptions\TaskClass::class)]
        private string  $taskClass,

        #[ConfigValue(ConfigOptions\MaximumLockDuration::class)]
        private int     $maximumLockDuration,
        StorageManager  $storageManager,
        StoreController $storeController,
    )
    {
        $this->store = $storeController->storeForEntity($this->taskClass);
        $controller = $storageManager->controller($this->store->storage());
        $this->updateBuilder = $controller->actionBuilders()->update();
        $this->actionExecutor = $controller->actionExecutor();
    }

    public function releaseExpiredLocks(): void
    {
        $updates = [
            'lockMarker' => null,
            'lockedAt' => null,
            'status' => TaskStatus::Pending,
        ];

        $conditions = [
            'status' => TaskStatus::Running,
            new LessThan('lockedAt', new \DateTime('-' . $this->maximumLockDuration . ' seconds')),
        ];

        $actions = $this->updateBuilder->build($this->store, $updates, $conditions);

        $this->actionExecutor->executeSet($actions);
    }

    public function release(Task $task, TaskStatus $status): void
    {
        $actions = $this->updateBuilder->build($this->store, [
            'lockMarker' => null,
            'lockedAt' => null,
            'status' => $status,
            'attempts' => $task->attempts + 1,
        ], ['id' => $task->id()]);

        $this->actionExecutor->executeSet($actions);
    }

    public function scheduleRetry(Task $task, int $retryAfter): void
    {
        $actions = $this->updateBuilder->build($this->store, [
            'lockMarker' => null,
            'lockedAt' => null,
            'status' => TaskStatus::Pending,
            'scheduledAt' => new \DateTime('+ ' . $retryAfter . ' seconds'),
            'attempts' => $task->attempts + 1,
        ], ['id' => $task->id()]);

        $this->actionExecutor->executeSet($actions);
    }
}

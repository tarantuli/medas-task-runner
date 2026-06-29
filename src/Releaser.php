<?php

declare(strict_types=1);

namespace Medas\TaskRunner;

use Medas\Core\Attributes\{ConfigValue, Service};
use Medas\EntityManager\Filters\LessThan;
use Medas\StorageManager\{
    Interfaces\StorageController,
    Interfaces\Store,
    StorageManager,
    StoreController
};

#[Service]
readonly class Releaser
{
    private Store $store;
    private StorageController $controller;

    public function __construct(
        #[ConfigValue(ConfigOptions\MaximumLockDuration::class)]
        private int     $maximumLockDuration,
        StorageManager  $storageManager,
        StoreController $storeController,
    )
    {
        $this->store = $storeController->storeForEntity(Task::class);
        $this->controller = $storageManager->controller($this->store->storage());
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

        $actions = $this->controller->actionBuilders()->update()->build(
            $this->store,
            $updates,
            $conditions
        );

        $this->controller->actionExecutor()->executeSet($actions);
    }

    public function release(Task $task, TaskStatus $status): void
    {
        $actions = $this->controller->actionBuilders()->update()->build(
            $this->store,
            ['lockMarker' => null, 'lockedAt' => null, 'status' => $status],
            ['id' => $task->id()]
        );

        $this->controller->actionExecutor()->executeSet($actions);
    }
}

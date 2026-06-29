<?php

declare(strict_types=1);

namespace Medas\TaskRunner;

use Medas\Core\{Attributes\ConfigValue, Attributes\Service, CodeGenerator};
use Medas\EntityManager\Filters\LessThanOrEqual;
use Medas\EntityManager\Repository;
use Medas\EntityManager\Selector\{Operants\Property, Selectors\WithValues, Slice, Sorting\SortBy};
use Medas\StorageManager\{
    Interfaces\StorageController,
    Interfaces\Store,
    StorageManager,
    StoreController
};

#[Service]
readonly class Locker
{
    private Store $store;
    private StorageController $controller;

    public function __construct(
        private CodeGenerator $codeGenerator,
        private Repository    $repository,

        #[ConfigValue(ConfigOptions\MarkerLength::class)]
        private int           $markerLength,
        StorageManager        $storageManager,
        StoreController       $storeController,
    )
    {
        $this->store = $storeController->storeForEntity(Task::class);
        $this->controller = $storageManager->controller($this->store->storage());
    }

    public function lock(): Task|null
    {
        $marker = $this->attemptToLock();

        /** @var Task|null $task */
        $task = $this->repository->fetchOne(WithValues::create(Task::class, ['lockMarker' => $marker]));

        return $task;
    }

    private function attemptToLock(): string
    {
        $marker = $this->codeGenerator->generate(
            $this->markerLength,
            CodeGenerator\CharacterSet::AlphaNumeric
        );

        $now = new \DateTime();

        $updates = [
            'status' => TaskStatus::Running,
            'lockMarker' => $marker,
            'lockedAt' => $now,
        ];

        $conditions = [
            'lockMarker' => null,
            'status' => TaskStatus::Pending,
            'scheduledAt' => new LessThanOrEqual('scheduledAt', $now),
        ];

        $sorts = [SortBy::c(Property::c('createdAt'))];
        $slice = Slice::c(1);

        $actions = $this->controller->actionBuilders()->update()->build(
            $this->store,
            $updates,
            $conditions,
            $sorts,
            $slice
        );

        $this->controller->actionExecutor()->executeSet($actions);

        return $marker;
    }
}

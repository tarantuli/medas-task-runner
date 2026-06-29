<?php

declare(strict_types=1);

namespace Medas\TaskRunner;

use Medas\Console\ConsolePackage;
use Medas\Core\{AsSingleton, BasePackage};
use Medas\EntityManager\EntityManagerPackage;
use Medas\Json\JsonPackage;
use Medas\StorageManager\StorageManagerPackage;

class TaskRunnerPackage extends BasePackage
{
    use AsSingleton;

    public function dependencies(): array
    {
        return [
            ConsolePackage::instance(),
            EntityManagerPackage::instance(),
            JsonPackage::instance(),
            StorageManagerPackage::instance(),
        ];
    }

    public function sourceDirectory(): string
    {
        return __DIR__;
    }
}

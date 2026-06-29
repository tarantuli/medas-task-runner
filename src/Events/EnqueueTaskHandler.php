<?php

declare(strict_types=1);

namespace Medas\TaskRunner\Events;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\EventListener,
    Attributes\Service,
    Interfaces\EntityManager
};
use Medas\Json\JsonEncoder;
use Medas\TaskRunner\{ConfigOptions\TaskClass, TaskStatus};

#[Service]
readonly class EnqueueTaskHandler
{
    public function __construct(
        #[ConfigValue(TaskClass::class)]
        private string        $taskClass,
        private EntityManager $entityManager,
        private JsonEncoder   $jsonEncoder,
    )
    {
    }

    #[EventListener]
    public function handle(EnqueueTask $event): void
    {
        $task = $this->entityManager->create($this->taskClass, [
            'className' => $event->className,
            'methodName' => $event->methodName,
            'arguments' => $this->jsonEncoder->encode($event->arguments),
            'status' => TaskStatus::Pending,
            'scheduledAt' => $event->scheduledAt,
        ]);

        $this->entityManager->persist($task);
        $this->entityManager->flush();
    }
}

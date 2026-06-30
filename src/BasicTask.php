<?php

declare(strict_types=1);

namespace Medas\TaskRunner;

use Medas\Core\{Interfaces\Uuid, Types\Binary, Types\Integer, Types\Text};
use Medas\EntityManager\{Attributes\Entity, Attributes\Id, Traits\Timestamps};

#[Entity(store: 'tasks')]
class BasicTask implements Task
{
    use Timestamps;

    #[Id]
    public Uuid $id;

    public string $className;
    public string $methodName;

    #[Text(maxLength: Binary::MAX_3_BYTE_LENGTH)]
    public string $arguments;

    public TaskStatus $status = TaskStatus::Pending;
    public \DateTime|null $scheduledAt = null;

    #[Integer(maxValue: Integer::UNSIGNED_4_BYTE_MAX)]
    public int|null $retryAfter = null;

    #[Integer(maxValue: Integer::UNSIGNED_1_BYTE_MAX)]
    public int|null $maxAttempts = null;

    #[Integer(maxValue: Integer::UNSIGNED_1_BYTE_MAX)]
    public int $attempts = 0;

    #[Text(length: 16)]
    public string|null $lockMarker = null;

    public \DateTime|null $lockedAt = null;

    public function id(): Uuid
    {
        return $this->id;
    }
}

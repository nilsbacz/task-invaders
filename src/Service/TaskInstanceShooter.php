<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\TaskInstance;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TaskInstanceShooter
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function shoot(TaskInstance $taskInstance, \DateTimeImmutable $shotAt): void
    {
        $taskInstance->shoot($shotAt);

        $task = $taskInstance->getTask();
        if ($task->isRespawnImmediatelyAfterDeath()) {
            $task->scheduleNextInstanceSpawnAfterShot($shotAt);
        }

        $this->entityManager->flush();
    }
}

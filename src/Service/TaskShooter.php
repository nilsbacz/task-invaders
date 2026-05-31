<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TaskShooter
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function shoot(Task $task, \DateTimeImmutable $shotAt): void
    {
        $task->complete($shotAt);

        if ($task->isRespawnImmediatelyAfterDeath()) {
            $task->scheduleNextSpawnAfterShot($shotAt);
        }

        $this->entityManager->flush();
    }
}

<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TaskRemover
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function remove(Task $task): void
    {
        $task->getBoardRow()?->removeTask($task);

        $this->entityManager->flush();
    }
}

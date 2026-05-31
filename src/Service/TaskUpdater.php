<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TaskUpdater
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function update(Task $task): Task
    {
        $task->setTitle(trim($task->getTitle()));
        $this->entityManager->flush();

        return $task;
    }
}

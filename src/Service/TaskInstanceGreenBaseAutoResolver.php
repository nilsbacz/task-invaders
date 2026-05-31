<?php

declare(strict_types=1);

namespace App\Service;

use App\Board\Domain\Board;
use App\Repository\TaskInstanceRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TaskInstanceGreenBaseAutoResolver
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TaskInstanceRepository $taskInstances,
    ) {
    }

    public function resolve(Board $board, \DateTimeImmutable $now): void
    {
        foreach ($this->taskInstances->findActiveGreenReachedBaseForBoard($board, $now) as $taskInstance) {
            $taskInstance->resolveGreenBaseRespawn($now);
        }

        $this->entityManager->flush();
    }
}

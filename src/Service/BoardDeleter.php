<?php

declare(strict_types=1);

namespace App\Service;

use App\Board\Domain\Board;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;

final readonly class BoardDeleter
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function delete(Board $board): void
    {
        foreach ($board->getBoardRows() as $boardRow) {
            /** @var list<Task> $tasks */
            $tasks = array_values($boardRow->getTasks()->toArray());
            foreach ($tasks as $task) {
                $boardRow->removeTask($task);
            }
        }

        $this->entityManager->remove($board);
        $this->entityManager->flush();
    }
}

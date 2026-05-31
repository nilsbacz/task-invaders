<?php

declare(strict_types=1);

namespace App\Service;

use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;

final readonly class BoardRowDeleter
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function delete(Board $board, BoardRow $boardRow): void
    {
        /** @var list<Task> $tasks */
        $tasks = array_values($boardRow->getTasks()->toArray());
        foreach ($tasks as $task) {
            $boardRow->removeTask($task);
        }

        $board->removeBoardRow($boardRow);

        $this->entityManager->remove($boardRow);
        $this->entityManager->flush();
    }
}

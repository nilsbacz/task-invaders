<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Board\Domain\BoardRow;
use App\Entity\Task;
use App\Service\TaskRemover;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaskRemover::class)]
#[UsesClass(BoardRow::class)]
#[UsesClass(Task::class)]
final class TaskRemoverTest extends TestCase
{
    #[Test]
    public function itDetachesTaskFromBoardRowAndFlushes(): void
    {
        // Arrange
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $boardRow = new BoardRow();
        $task = new Task();
        $boardRow->addTask($task);

        $entityManager->expects(self::once())->method('flush');

        $remover = new TaskRemover($entityManager);

        // Act
        $remover->remove($task);

        // Assert
        self::assertFalse($boardRow->getTasks()->contains($task));
        self::assertNull($task->getBoardRow());
    }
}

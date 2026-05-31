<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\Entity\Task;
use App\Service\BoardRowDeleter;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BoardRowDeleter::class)]
#[UsesClass(Board::class)]
#[UsesClass(BoardRow::class)]
#[UsesClass(Task::class)]
final class BoardRowDeleterTest extends TestCase
{
    #[Test]
    public function itRemovesBoardRowDetachesTasksAndFlushes(): void
    {
        // Arrange
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $board = new Board();
        $boardRow = new BoardRow();
        $task = new Task();
        $board->addBoardRow($boardRow);
        $boardRow->addTask($task);

        $entityManager->expects(self::once())->method('remove')->with($boardRow);
        $entityManager->expects(self::once())->method('flush');

        $deleter = new BoardRowDeleter($entityManager);

        // Act
        $deleter->delete($board, $boardRow);

        // Assert
        self::assertCount(0, $board->getBoardRows());
        self::assertNull($boardRow->getBoard());
        self::assertCount(0, $boardRow->getTasks());
        self::assertNull($task->getBoardRow());
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\Entity\Task;
use App\Service\BoardDeleter;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BoardDeleter::class)]
#[UsesClass(Board::class)]
#[UsesClass(BoardRow::class)]
#[UsesClass(Task::class)]
final class BoardDeleterTest extends TestCase
{
    #[Test]
    public function itRemovesBoardAndFlushes(): void
    {

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $board = new Board();

        $entityManager->expects(self::once())->method('remove')->with($board);
        $entityManager->expects(self::once())->method('flush');

        $deleter = new BoardDeleter($entityManager);


        $deleter->delete($board);
    }

    #[Test]
    public function itDetachesBoardTasksBeforeRemovingBoard(): void
    {

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $board = new Board();
        $boardRow = new BoardRow();
        $task = new Task();
        $board->addBoardRow($boardRow);
        $boardRow->addTask($task);

        $entityManager->expects(self::once())->method('remove')->with($board);
        $entityManager->expects(self::once())->method('flush');

        $deleter = new BoardDeleter($entityManager);


        $deleter->delete($board);


        self::assertCount(0, $boardRow->getTasks());
        self::assertNull($task->getBoardRow());
    }
}

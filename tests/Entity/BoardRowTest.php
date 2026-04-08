<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\Entity\Task;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(BoardRow::class)]
final class BoardRowTest extends TestCase
{
    #[Test]
    public function itExposesGettersAndSetters(): void
    {

        $boardRow = new BoardRow();
        $board = new Board();

        self::assertNull($boardRow->getId());


        $setTitleResult = $boardRow->setTitle('Board Row A');
        $setRowNumberResult = $boardRow->setRowNumber(3);
        $setBoardResult = $boardRow->setBoard($board);


        self::assertSame($boardRow, $setTitleResult);
        self::assertSame($boardRow, $setRowNumberResult);
        self::assertSame($boardRow, $setBoardResult);
        self::assertSame('Board Row A', $boardRow->getTitle());
        self::assertSame(3, $boardRow->getRowNumber());
        self::assertSame($board, $boardRow->getBoard());
        self::assertCount(0, $boardRow->getTasks());
    }

    #[Test]
    public function itManagesTasks(): void
    {

        $boardRow = new BoardRow();
        $task = new Task();


        $addTaskResult = $boardRow->addTask($task);


        self::assertSame($boardRow, $addTaskResult);
        self::assertCount(1, $boardRow->getTasks());
        self::assertSame($boardRow, $task->getBoardRow());


        $removeTaskResult = $boardRow->removeTask($task);


        self::assertSame($boardRow, $removeTaskResult);
        self::assertCount(0, $boardRow->getTasks());
        self::assertNull($task->getBoardRow());
    }
}

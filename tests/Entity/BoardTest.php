<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Board::class)]
final class BoardTest extends TestCase
{
    #[Test]
    public function itExposesGettersAndSetters(): void
    {

        $board = new Board();

        self::assertNull($board->getId());


        $setTitleResult = $board->setTitle('Main Board');
        $setTurretModeResult = $board->setIsTurretMode(true);


        self::assertSame($board, $setTitleResult);
        self::assertSame($board, $setTurretModeResult);
        self::assertSame('Main Board', $board->getTitle());
        self::assertTrue($board->isTurretMode());
        self::assertCount(0, $board->getBoardRows());
    }

    #[Test]
    public function itManagesBoardRows(): void
    {

        $board = new Board();
        $boardRow = new BoardRow();


        $addBoardRowResult = $board->addBoardRow($boardRow);


        self::assertSame($board, $addBoardRowResult);
        self::assertCount(1, $board->getBoardRows());
        self::assertSame($board, $boardRow->getBoard());


        $removeBoardRowResult = $board->removeBoardRow($boardRow);


        self::assertSame($board, $removeBoardRowResult);
        self::assertCount(0, $board->getBoardRows());
        self::assertNull($boardRow->getBoard());
    }
}

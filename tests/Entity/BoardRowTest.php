<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
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

        self::assertNull($boardRow->getId());

        $board = new Board();

        self::assertSame($boardRow, $boardRow->setTitle('Board Row A'));
        self::assertSame($boardRow, $boardRow->setRowNumber(3));
        self::assertSame($boardRow, $boardRow->setBoard($board));

        self::assertSame('Board Row A', $boardRow->getTitle());
        self::assertSame(3, $boardRow->getRowNumber());
        self::assertSame($board, $boardRow->getBoard());
    }
}

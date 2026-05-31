<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Board\Application\CreateBoardRow;
use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\Service\BoardRowCreator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BoardRowCreator::class)]
#[UsesClass(Board::class)]
#[UsesClass(BoardRow::class)]
#[UsesClass(CreateBoardRow::class)]
final class BoardRowCreatorTest extends TestCase
{
    #[Test]
    public function itCreatesNextBoardRowAndFlushes(): void
    {
        // Arrange
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $board = new Board();
        $existingRow = new BoardRow();
        $existingRow->setTitle('Sports');
        $existingRow->setRowNumber(4);
        $board->addBoardRow($existingRow);
        $command = new CreateBoardRow();
        $command->setTitle('  Household  ');

        $entityManager->expects(self::once())->method('persist')->with(self::isInstanceOf(BoardRow::class));
        $entityManager->expects(self::once())->method('flush');

        $creator = new BoardRowCreator($entityManager);

        // Act
        $boardRow = $creator->create($board, $command);

        // Assert
        self::assertSame('Household', $boardRow->getTitle());
        self::assertSame(5, $boardRow->getRowNumber());
        self::assertSame($board, $boardRow->getBoard());
        self::assertTrue($board->getBoardRows()->contains($boardRow));
    }
}

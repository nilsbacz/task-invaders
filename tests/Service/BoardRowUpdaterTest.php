<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Board\Domain\BoardRow;
use App\Service\BoardRowUpdater;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(BoardRowUpdater::class)]
final class BoardRowUpdaterTest extends TestCase
{
    #[Test]
    public function itTrimsBoardRowTitleFlushesAndReturnsBoardRow(): void
    {
        // Arrange
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $updater = new BoardRowUpdater($entityManager);
        $boardRow = new BoardRow();
        $boardRow->setTitle('  Updated Row  ');

        // Act
        $result = $updater->update($boardRow);

        // Assert
        self::assertSame($boardRow, $result);
        self::assertSame('Updated Row', $boardRow->getTitle());
    }
}

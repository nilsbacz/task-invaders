<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Board\Domain\Board;
use App\Service\BoardUpdater;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(BoardUpdater::class)]
final class BoardUpdaterTest extends TestCase
{
    #[Test]
    public function itTrimsBoardTitleFlushesAndReturnsBoard(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $updater = new BoardUpdater($entityManager);
        $board = new Board();
        $board->setTitle('  Updated Board  ');

        $result = $updater->update($board);

        self::assertSame($board, $result);
        self::assertSame('Updated Board', $board->getTitle());
    }
}

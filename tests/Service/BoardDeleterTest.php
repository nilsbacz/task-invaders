<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Board\Domain\Board;
use App\Service\BoardDeleter;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(BoardDeleter::class)]
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
}

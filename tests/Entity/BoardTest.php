<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Board;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Board::class)]
final class BoardTest extends TestCase
{
    #[Test]
    public function it_exposes_getters_and_setters(): void
    {
        $board = new Board();

        self::assertNull($board->getId());

        self::assertSame($board, $board->setTitle('Main Board'));
        self::assertSame($board, $board->setIsTurretMode(true));

        self::assertSame('Main Board', $board->getTitle());
        self::assertTrue($board->isTurretMode());
    }
}

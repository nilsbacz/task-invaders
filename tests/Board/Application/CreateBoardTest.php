<?php

declare(strict_types=1);

namespace App\Tests\Board\Application;

use App\Board\Application\CreateBoard;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreateBoard::class)]
final class CreateBoardTest extends TestCase
{
    #[Test]
    public function itExposesGettersAndSetters(): void
    {
        $command = new CreateBoard();

        self::assertSame('', $command->getTitle());
        self::assertFalse($command->isTurretMode());

        self::assertSame($command, $command->setTitle('Alpha Board'));
        self::assertSame($command, $command->setIsTurretMode(true));

        self::assertSame('Alpha Board', $command->getTitle());
        self::assertTrue($command->isTurretMode());
    }
}

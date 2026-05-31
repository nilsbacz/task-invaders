<?php

declare(strict_types=1);

namespace App\Tests\Board\Application;

use App\Board\Application\CreateBoardRow;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreateBoardRow::class)]
final class CreateBoardRowTest extends TestCase
{
    #[Test]
    public function itExposesGettersAndSetters(): void
    {
        // Arrange
        $command = new CreateBoardRow();

        self::assertSame('', $command->getTitle());

        // Act
        $setTitleResult = $command->setTitle('Sports');

        // Assert
        self::assertSame($command, $setTitleResult);
        self::assertSame('Sports', $command->getTitle());
    }
}

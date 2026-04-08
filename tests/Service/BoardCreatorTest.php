<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\BoardPreset\BoardPreset;
use App\BoardPreset\BoardRowPreset;
use App\Board\Application\BoardCreator;
use App\Board\Application\CreateBoard;
use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\Service\BoardPresetApplier;
use App\Service\BoardPresetLoader;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BoardCreator::class)]
#[UsesClass(Board::class)]
#[UsesClass(BoardRow::class)]
#[UsesClass(BoardPreset::class)]
#[UsesClass(BoardRowPreset::class)]
#[UsesClass(CreateBoard::class)]
#[UsesClass(BoardPresetApplier::class)]
#[UsesClass(BoardPresetLoader::class)]
final class BoardCreatorTest extends TestCase
{
    #[Test]
    public function itTrimsBoardTitleAndAppliesDefaultPresetWhenBoardHasNoRows(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist');
        $entityManager->expects(self::once())->method('flush');

        $creator = new BoardCreator(
            $entityManager,
            new BoardPresetApplier($this->createPresetLoader()),
        );

        $command = new CreateBoard();
        $command->setTitle('  Alpha Board  ');
        $command->setIsTurretMode(true);

        $result = $creator->create($command);

        self::assertInstanceOf(Board::class, $result);
        self::assertSame('Alpha Board', $result->getTitle());
        self::assertTrue($result->isTurretMode());
        self::assertSame(
            [
             'sports',
             'household',
             'running',
            ],
            array_map(
                static fn (BoardRow $boardRow): string => $boardRow->getTitle(),
                $result->getBoardRows()->toArray()
            )
        );
    }

    #[Test]
    public function itBuildsBoardWithDefaultTurretModeWhenCommandDoesNotEnableIt(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist');
        $entityManager->expects(self::once())->method('flush');

        $creator = new BoardCreator(
            $entityManager,
            new BoardPresetApplier($this->createPresetLoader()),
        );

        $command = new CreateBoard();
        $command->setTitle('  Custom Board  ');

        $createdBoard = $creator->create($command);

        self::assertSame('Custom Board', $createdBoard->getTitle());
        self::assertFalse($createdBoard->isTurretMode());
        self::assertCount(3, $createdBoard->getBoardRows());
    }

    private function createPresetLoader(): BoardPresetLoader
    {
        $directory = sys_get_temp_dir() . '/board-creator-preset-' . bin2hex(random_bytes(8));
        self::assertTrue(mkdir($directory, 0777, true));
        $contents = <<<'YAML'
key: default
name: Default Board
version: 1
boardRows:
  - key: sports
    title: sports
    position: 1
  - key: household
    title: household
    position: 2
  - key: running
    title: running
    position: 3
YAML;
        self::assertSame(strlen($contents), file_put_contents($directory . '/default.yaml', $contents));

        return new BoardPresetLoader($directory);
    }
}

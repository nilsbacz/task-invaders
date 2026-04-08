<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\BoardPreset\BoardPreset;
use App\BoardPreset\BoardRowPreset;
use App\Entity\Board;
use App\Entity\BoardRow;
use App\Service\BoardCreator;
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

        $board = new Board();
        $board->setTitle('  Alpha Board  ');

        $result = $creator->create($board);

        self::assertSame($board, $result);
        self::assertSame('Alpha Board', $board->getTitle());
        self::assertSame(
            ['sports', 'household', 'running'],
            array_map(
                static fn (BoardRow $boardRow): string => $boardRow->getTitle(),
                $board->getBoardRows()->toArray()
            )
        );
    }

    #[Test]
    public function itDoesNotApplyDefaultPresetWhenBoardAlreadyHasRows(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist');
        $entityManager->expects(self::once())->method('flush');

        $creator = new BoardCreator(
            $entityManager,
            new BoardPresetApplier($this->createPresetLoader()),
        );

        $board = new Board();
        $board->setTitle('  Custom Board  ');
        $boardRow = new BoardRow();
        $boardRow->setTitle('custom');
        $boardRow->setRowNumber(99);
        $board->addBoardRow($boardRow);

        $creator->create($board);

        self::assertSame('Custom Board', $board->getTitle());
        self::assertCount(1, $board->getBoardRows());
        $firstBoardRow = $board->getBoardRows()->first();
        self::assertInstanceOf(BoardRow::class, $firstBoardRow);
        self::assertSame('custom', $firstBoardRow->getTitle());
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

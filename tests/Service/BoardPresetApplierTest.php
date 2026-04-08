<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\BoardPreset\BoardPreset;
use App\BoardPreset\BoardRowPreset;
use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\Service\BoardPresetApplier;
use App\Service\BoardPresetLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BoardPresetApplier::class)]
#[UsesClass(Board::class)]
#[UsesClass(BoardRow::class)]
#[UsesClass(BoardPreset::class)]
#[UsesClass(BoardRowPreset::class)]
#[UsesClass(BoardPresetLoader::class)]
final class BoardPresetApplierTest extends TestCase
{
    #[Test]
    public function itAppliesDefaultPresetRowsToBoard(): void
    {
        $applier = new BoardPresetApplier($this->createPresetLoader());
        $board = new Board();

        $applier->applyDefaultPreset($board);

        self::assertSame(
            [
             'sports',
             'household',
             'projects',
            ],
            array_map(
                static fn (BoardRow $boardRow): string => $boardRow->getTitle(),
                $board->getBoardRows()->toArray()
            )
        );
        self::assertSame(
            [
             1,
             2,
             3,
            ],
            array_map(
                static fn (BoardRow $boardRow): int => $boardRow->getRowNumber(),
                $board->getBoardRows()->toArray()
            )
        );
    }

    private function createPresetLoader(): BoardPresetLoader
    {
        $directory = sys_get_temp_dir() . '/board-preset-applier-' . bin2hex(random_bytes(8));
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
  - key: projects
    title: projects
    position: 3
YAML;
        self::assertSame(strlen($contents), file_put_contents($directory . '/default.yaml', $contents));

        return new BoardPresetLoader($directory);
    }
}

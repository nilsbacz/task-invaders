<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\BoardPreset\BoardPreset;
use App\BoardPreset\BoardRowPreset;
use App\BoardPreset\BoardTaskPreset;
use App\Entity\Task;
use App\Service\BoardPresetApplier;
use App\Service\BoardPresetLoader;
use App\Tests\Support\BoardPresetFixture;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BoardPresetApplier::class)]
#[UsesClass(Board::class)]
#[UsesClass(BoardRow::class)]
#[UsesClass(BoardPreset::class)]
#[UsesClass(BoardRowPreset::class)]
#[UsesClass(BoardTaskPreset::class)]
#[UsesClass(BoardPresetLoader::class)]
final class BoardPresetApplierTest extends TestCase
{
    #[Test]
    public function itAppliesDefaultPresetRowsAndTasksToBoard(): void
    {

        $applier = new BoardPresetApplier($this->createPresetLoaderFixture());
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

        $boardRows = $board->getBoardRows()->toArray();
        self::assertCount(2, $boardRows[0]->getTasks());
        $firstTask = $boardRows[0]->getTasks()->first();
        self::assertInstanceOf(Task::class, $firstTask);
        self::assertSame('workout', $firstTask->getTitle());
        self::assertCount(1, $boardRows[1]->getTasks());
        self::assertCount(1, $boardRows[2]->getTasks());
    }

    private function createPresetLoaderFixture(): BoardPresetLoader
    {
        return BoardPresetFixture::createDefaultLoader('board-preset-applier');
    }
}

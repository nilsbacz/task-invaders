<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Board\Application\BoardCreator;
use App\Board\Application\CreateBoard;
use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\BoardPreset\BoardPreset;
use App\BoardPreset\BoardRowPreset;
use App\BoardPreset\BoardTaskPreset;
use App\Service\BoardPresetApplier;
use App\Service\BoardPresetLoader;
use App\Tests\Support\BoardPresetFixture;
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
#[UsesClass(BoardTaskPreset::class)]
#[UsesClass(CreateBoard::class)]
#[UsesClass(BoardPresetApplier::class)]
#[UsesClass(BoardPresetLoader::class)]
final class BoardCreatorTest extends TestCase
{
    #[Test]
    public function itTrimsBoardTitleAndAppliesDefaultPresetWhenBoardHasNoRows(): void
    {

        $creator = $this->createBoardCreatorFixture();
        $command = $this->createCommandFixture('  Alpha Board  ', true);


        $result = $creator->create($command);


        self::assertInstanceOf(Board::class, $result);
        self::assertSame('Alpha Board', $result->getTitle());
        self::assertTrue($result->isTurretMode());
        self::assertSame(
            [
             'sports',
             'household',
             'projects',
            ],
            array_map(
                static fn (BoardRow $boardRow): string => $boardRow->getTitle(),
                $result->getBoardRows()->toArray()
            )
        );
        $boardRows = $result->getBoardRows()->toArray();
        self::assertCount(2, $boardRows[0]->getTasks());
        self::assertCount(1, $boardRows[1]->getTasks());
        self::assertCount(1, $boardRows[2]->getTasks());
    }

    #[Test]
    public function itBuildsBoardWithDefaultTurretModeWhenCommandDoesNotEnableIt(): void
    {

        $creator = $this->createBoardCreatorFixture();
        $command = $this->createCommandFixture('  Custom Board  ');


        $createdBoard = $creator->create($command);


        self::assertSame('Custom Board', $createdBoard->getTitle());
        self::assertFalse($createdBoard->isTurretMode());
        self::assertCount(3, $createdBoard->getBoardRows());
    }

    private function createBoardCreatorFixture(): BoardCreator
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist');
        $entityManager->expects(self::once())->method('flush');

        return new BoardCreator(
            $entityManager,
            new BoardPresetApplier($this->createPresetLoaderFixture()),
        );
    }

    private function createCommandFixture(string $title, bool $isTurretMode = false): CreateBoard
    {
        $command = new CreateBoard();
        $command->setTitle($title);
        $command->setIsTurretMode($isTurretMode);

        return $command;
    }

    private function createPresetLoaderFixture(): BoardPresetLoader
    {
        return BoardPresetFixture::createDefaultLoader('board-creator-preset');
    }
}

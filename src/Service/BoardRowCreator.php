<?php

declare(strict_types=1);

namespace App\Service;

use App\Board\Application\CreateBoardRow;
use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use Doctrine\ORM\EntityManagerInterface;

final readonly class BoardRowCreator
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function create(Board $board, CreateBoardRow $command): BoardRow
    {
        $boardRow = new BoardRow();
        $boardRow->setTitle(trim($command->getTitle()));
        $boardRow->setRowNumber($this->nextRowNumber($board));

        $board->addBoardRow($boardRow);

        $this->entityManager->persist($boardRow);
        $this->entityManager->flush();

        return $boardRow;
    }

    private function nextRowNumber(Board $board): int
    {
        $highestRowNumber = 0;
        foreach ($board->getBoardRows() as $boardRow) {
            $highestRowNumber = max($highestRowNumber, $boardRow->getRowNumber());
        }

        return $highestRowNumber + 1;
    }
}

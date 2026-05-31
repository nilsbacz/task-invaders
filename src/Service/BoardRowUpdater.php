<?php

declare(strict_types=1);

namespace App\Service;

use App\Board\Domain\BoardRow;
use Doctrine\ORM\EntityManagerInterface;

final readonly class BoardRowUpdater
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function update(BoardRow $boardRow): BoardRow
    {
        $boardRow->setTitle(trim($boardRow->getTitle()));
        $this->entityManager->flush();

        return $boardRow;
    }
}

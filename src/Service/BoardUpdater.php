<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Board;
use Doctrine\ORM\EntityManagerInterface;

final readonly class BoardUpdater
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function update(Board $board): Board
    {
        $board->setTitle(trim($board->getTitle()));
        $this->entityManager->flush();

        return $board;
    }
}

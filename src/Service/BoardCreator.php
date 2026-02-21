<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Board;
use Doctrine\ORM\EntityManagerInterface;

final readonly class BoardCreator
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function create(Board $board): Board
    {
        $board->setTitle(trim($board->getTitle()));
        $this->entityManager->persist($board);
        $this->entityManager->flush();

        return $board;
    }
}

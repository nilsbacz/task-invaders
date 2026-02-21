<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Board;
use Doctrine\ORM\EntityManagerInterface;

final readonly class BoardDeleter
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function delete(Board $board): void
    {
        $this->entityManager->remove($board);
        $this->entityManager->flush();
    }
}

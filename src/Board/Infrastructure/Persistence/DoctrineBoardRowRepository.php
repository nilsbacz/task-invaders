<?php

declare(strict_types=1);

namespace App\Board\Infrastructure\Persistence;

use App\Board\Domain\BoardRow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BoardRow>
 */
class DoctrineBoardRowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BoardRow::class);
    }
}

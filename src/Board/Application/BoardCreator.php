<?php

declare(strict_types=1);

namespace App\Board\Application;

use App\Board\Domain\Board;
use App\Service\BoardPresetApplier;
use Doctrine\ORM\EntityManagerInterface;

final readonly class BoardCreator
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BoardPresetApplier $boardPresetApplier,
    ) {
    }

    public function create(CreateBoard $command): Board
    {
        $board = new Board();
        $board->setTitle(trim($command->getTitle()));
        $board->setIsTurretMode($command->isTurretMode());

        if ($board->getBoardRows()->isEmpty()) {
            $this->boardPresetApplier->applyDefaultPreset($board);
        }

        $this->entityManager->persist($board);
        $this->entityManager->flush();

        return $board;
    }
}

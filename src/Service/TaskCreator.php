<?php

declare(strict_types=1);

namespace App\Service;

use App\Board\Application\CreateTask;
use App\Board\Domain\BoardRow;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TaskCreator
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function create(BoardRow $boardRow, CreateTask $command): Task
    {
        $task = new Task();
        $this->applyCommand($task, $command);
        $task->setSpawnDate(new \DateTimeImmutable());

        $boardRow->addTask($task);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $task;
    }

    private function applyCommand(Task $task, CreateTask $command): void
    {
        $task->setTitle(trim($command->getTitle()));
        $task->setRiskLevel($command->getRiskLevel());
        $task->setRespawnsIn($command->getRespawnsIn());
        $task->setSpawnsEvery($command->getSpawnsEvery());
        $task->setReachesBaseIn($command->getReachesBaseIn());
        $task->setHasShield($command->hasShield());
        $task->setRespawnImmediatelyAfterDeath($command->isRespawnImmediatelyAfterDeath());
        $task->setSpeedFactor($command->getSpeedFactor());
    }
}

<?php

declare(strict_types=1);

namespace App\Service;

use App\Board\Domain\Board;
use App\Entity\Task;
use App\Entity\TaskInstance;
use App\Repository\TaskInstanceRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class TaskInstanceMaterializer
{
    private const DEFAULT_MAX_INSTANCES_PER_TASK = 500;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private TaskInstanceRepository $taskInstances,
        private int $maxInstancesPerTask = self::DEFAULT_MAX_INSTANCES_PER_TASK,
    ) {
    }

    public function materialize(Board $board, \DateTimeImmutable $now): void
    {
        foreach ($board->getBoardRows() as $boardRow) {
            foreach ($boardRow->getTasks() as $task) {
                if (!$task->shouldAppearOnBoard()) {
                    continue;
                }

                $this->materializeTask($task, $now);
            }
        }

        $this->entityManager->flush();
    }

    private function materializeTask(Task $task, \DateTimeImmutable $now): void
    {
        $nextSpawnAt = $task->getNextSpawnAt();
        $iterations = 0;

        while ($nextSpawnAt !== null && $nextSpawnAt <= $now) {
            if ($iterations >= $this->maxInstancesPerTask) {
                throw new \RuntimeException(sprintf(
                    'Task instance materialization exceeded the per-task cap of %d for task "%s".',
                    $this->maxInstancesPerTask,
                    $task->getTitle()
                ));
            }

            if (!$this->taskInstances->existsForTaskSpawnedAt($task, $nextSpawnAt)) {
                $taskInstance = new TaskInstance(
                    $task,
                    $nextSpawnAt,
                    $task->reachesBaseAt($nextSpawnAt)
                );
                $task->addTaskInstance($taskInstance);
                $this->entityManager->persist($taskInstance);
            }

            $iterations++;
            $nextSpawnAt = $this->nextSpawnAfter($task, $nextSpawnAt);
            $task->setNextSpawnAt($nextSpawnAt);
        }
    }

    private function nextSpawnAfter(Task $task, \DateTimeImmutable $spawnedAt): ?\DateTimeImmutable
    {
        if ($task->getSpawnsEvery() <= 0) {
            return null;
        }

        return $spawnedAt->add(new \DateInterval(sprintf('PT%dM', $task->getSpawnsEvery())));
    }
}

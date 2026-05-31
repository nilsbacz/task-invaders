<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\Entity\Task;
use App\Entity\TaskInstance;
use App\Enum\TaskInstanceResolution;
use App\Enum\TaskRiskLevel;
use App\Repository\TaskInstanceRepository;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(TaskInstance::class)]
#[UsesClass(Board::class)]
#[UsesClass(BoardRow::class)]
#[UsesClass(Task::class)]
#[UsesClass(TaskInstanceRepository::class)]
#[UsesClass(TaskInstanceResolution::class)]
#[UsesClass(TaskRiskLevel::class)]
final class TaskInstancePersistenceTest extends AbstractDatabaseWebTestCase
{
    #[Test]
    public function itPersistsTaskInstanceLifecycleFields(): void
    {
        // Arrange
        $board = $this->createBoard('Instance Persistence Board', false);
        $boardRow = new BoardRow();
        $boardRow->setTitle('Sports');
        $boardRow->setRowNumber(1);
        $board->addBoardRow($boardRow);
        $task = $this->createTask();
        $boardRow->addTask($task);
        $taskInstance = new TaskInstance(
            $task,
            new DateTimeImmutable('2026-05-31T10:00:00+00:00'),
            new DateTimeImmutable('2026-05-31T10:30:00+00:00'),
            new DateTimeImmutable('2026-05-31T09:59:00+00:00')
        );
        $task->addTaskInstance($taskInstance);
        $taskInstance->shoot(new DateTimeImmutable('2026-05-31T10:05:00+00:00'));

        // Act
        $this->entityManager->persist($taskInstance);
        $this->entityManager->flush();
        $taskInstanceId = $taskInstance->getId();
        self::assertNotNull($taskInstanceId);
        $this->entityManager->clear();

        $persisted = $this->entityManager->getRepository(TaskInstance::class)->find($taskInstanceId);

        // Assert
        self::assertInstanceOf(TaskInstance::class, $persisted);
        self::assertSame('Workout', $persisted->getTask()->getTitle());
        self::assertSame('2026-05-31T10:00:00+00:00', $persisted->getSpawnedAt()->format(DATE_ATOM));
        self::assertSame('2026-05-31T10:30:00+00:00', $persisted->getReachesBaseAt()->format(DATE_ATOM));
        self::assertSame('2026-05-31T10:05:00+00:00', $persisted->getCompletedAt()?->format(DATE_ATOM));
        self::assertSame('2026-05-31T10:05:00+00:00', $persisted->getResolvedAt()?->format(DATE_ATOM));
        self::assertSame(TaskInstanceResolution::SHOT, $persisted->getResolution());
        self::assertFalse($persisted->isActive());
    }

    private function createTask(): Task
    {
        $task = new Task();
        $task->setTitle('Workout');
        $task->setRiskLevel(TaskRiskLevel::RED);
        $task->setSpawnDate(new DateTimeImmutable('2026-05-31T10:00:00+00:00'));
        $task->setRespawnsIn(10);
        $task->setSpawnsEvery(20);
        $task->setReachesBaseIn(30);

        return $task;
    }
}

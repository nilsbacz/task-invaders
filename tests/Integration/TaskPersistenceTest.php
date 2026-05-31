<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\Entity\Task;
use App\Enum\TaskRiskLevel;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;

#[CoversClass(Task::class)]
#[UsesClass(Board::class)]
#[UsesClass(BoardRow::class)]
#[UsesClass(TaskRiskLevel::class)]
final class TaskPersistenceTest extends AbstractDatabaseWebTestCase
{
    #[Test]
    public function itRejectsDeletingTaskAttachedToBoard(): void
    {
        // Arrange
        $board = $this->createBoard('Protected Task Board', false);
        $boardRow = new BoardRow();
        $boardRow->setTitle('Sports');
        $boardRow->setRowNumber(1);
        $board->addBoardRow($boardRow);
        $task = $this->createTask();
        $boardRow->addTask($task);
        $this->entityManager->flush();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Tasks attached to a board cannot be deleted.');

        // Act
        $this->entityManager->remove($task);
        $this->entityManager->flush();
    }

    #[Test]
    public function itAllowsDeletingTaskWithoutAttachedBoard(): void
    {
        // Arrange
        $task = $this->createTask();
        $this->entityManager->persist($task);
        $this->entityManager->flush();
        $taskId = $task->getId();
        self::assertNotNull($taskId);

        // Act
        $this->entityManager->remove($task);
        $this->entityManager->flush();

        // Assert
        self::assertNull($this->entityManager->getRepository(Task::class)->find($taskId));
    }

    private function createTask(): Task
    {
        $task = new Task();
        $task->setTitle('Workout');
        $task->setRiskLevel(TaskRiskLevel::GREEN);
        $task->setSpawnDate(new DateTimeImmutable('2026-04-08T00:00:00+00:00'));
        $task->setRespawnsIn(10);
        $task->setSpawnsEvery(20);
        $task->setReachesBaseIn(30);

        return $task;
    }
}

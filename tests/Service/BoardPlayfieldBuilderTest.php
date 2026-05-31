<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\Entity\Task;
use App\Entity\TaskInstance;
use App\Enum\TaskInstanceResolution;
use App\Enum\TaskRiskLevel;
use App\Playfield\BoardPlayfield;
use App\Playfield\BoardPlayfieldFocus;
use App\Playfield\BoardPlayfieldLane;
use App\Playfield\BoardPlayfieldTaskInstance;
use App\Playfield\BoardPlayfieldUpcomingTask;
use App\Repository\TaskInstanceRepository;
use App\Service\BoardPlayfieldBuilder;
use App\Service\TaskInstanceGreenBaseAutoResolver;
use App\Service\TaskInstanceMaterializer;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BoardPlayfieldBuilder::class)]
#[UsesClass(Board::class)]
#[UsesClass(BoardRow::class)]
#[UsesClass(BoardPlayfield::class)]
#[UsesClass(BoardPlayfieldFocus::class)]
#[UsesClass(BoardPlayfieldLane::class)]
#[UsesClass(BoardPlayfieldTaskInstance::class)]
#[UsesClass(BoardPlayfieldUpcomingTask::class)]
#[UsesClass(Task::class)]
#[UsesClass(TaskInstance::class)]
#[UsesClass(TaskInstanceGreenBaseAutoResolver::class)]
#[UsesClass(TaskInstanceMaterializer::class)]
#[UsesClass(TaskInstanceResolution::class)]
#[UsesClass(TaskRiskLevel::class)]
final class BoardPlayfieldBuilderTest extends TestCase
{
    #[Test]
    public function itBuildsSortedLanesWithProgressAndUpcomingTasks(): void
    {

        $now = new DateTimeImmutable('2026-05-31T10:05:00+00:00');
        $board = $this->createBoard('Training Board', 10);
        $secondLane = $this->createBoardRow($board, 'Second', 2, 102);
        $firstLane = $this->createBoardRow($board, 'First', 1, 101);
        $task = $this->createTask($firstLane, 'Workout', TaskRiskLevel::RED, 201);
        $task->setHasShield(true);
        $task->setReachesBaseIn(10);
        $task->setNextSpawnAt(new DateTimeImmutable('2026-05-31T10:20:00+00:00'));
        $taskInstance = $this->createTaskInstance(
            $task,
            new DateTimeImmutable('2026-05-31T10:00:00+00:00'),
            new DateTimeImmutable('2026-05-31T10:10:00+00:00'),
            301
        );

        $builder = $this->createBuilder($board, $now, [$taskInstance]);


        $playfield = $builder->build($board, $now);


        self::assertSame(10, $playfield->boardId);
        self::assertSame('Training Board', $playfield->title);
        self::assertCount(2, $playfield->lanes);
        self::assertSame($firstLane->getId(), $playfield->lanes[0]->laneId);
        self::assertSame($secondLane->getId(), $playfield->lanes[1]->laneId);

        $projectedInstance = $playfield->lanes[0]->taskInstances[0];
        self::assertSame(201, $projectedInstance->taskId);
        self::assertSame(301, $projectedInstance->instanceId);
        self::assertSame('Workout', $projectedInstance->title);
        self::assertSame(101, $projectedInstance->laneId);
        self::assertSame(TaskRiskLevel::RED, $projectedInstance->taskRiskLevel);
        self::assertSame(TaskRiskLevel::RED, $projectedInstance->visualRiskLevel);
        self::assertSame(600, $projectedInstance->lifetimeSeconds);
        self::assertSame(300, $projectedInstance->elapsedSeconds);
        self::assertSame(0.5, $projectedInstance->progressRatio);
        self::assertSame(300, $projectedInstance->secondsUntilBase);
        self::assertFalse($projectedInstance->baseReached);
        self::assertTrue($projectedInstance->hasShield);

        $upcomingTask = $playfield->lanes[0]->upcomingTasks[0];
        self::assertSame(201, $upcomingTask->taskId);
        self::assertSame('2026-05-31T10:20:00+00:00', $upcomingTask->nextSpawnAt->format(DATE_ATOM));
        self::assertSame('2026-05-31T10:30:00+00:00', $upcomingTask->reachesBaseAt->format(DATE_ATOM));
        self::assertCount(0, $playfield->lanes[1]->taskInstances);
        self::assertFalse($playfield->focus->hasFocus());
    }

    #[Test]
    public function itProjectsYellowEscalationAfterTenPercentOvertime(): void
    {

        $now = new DateTimeImmutable('2026-05-31T10:11:05+00:00');
        $board = $this->createBoard('Yellow Board', 10);
        $lane = $this->createBoardRow($board, 'Main', 1, 101);
        $task = $this->createTask($lane, 'Escalating', TaskRiskLevel::YELLOW, 201);
        $task->setNextSpawnAt(null);
        $taskInstance = $this->createTaskInstance(
            $task,
            new DateTimeImmutable('2026-05-31T10:00:00+00:00'),
            new DateTimeImmutable('2026-05-31T10:10:00+00:00'),
            301
        );

        $builder = $this->createBuilder($board, $now, [$taskInstance]);


        $playfield = $builder->build($board, $now);


        $projectedInstance = $playfield->lanes[0]->taskInstances[0];
        self::assertSame(TaskRiskLevel::YELLOW, $projectedInstance->taskRiskLevel);
        self::assertSame(TaskRiskLevel::RED, $projectedInstance->visualRiskLevel);
        self::assertSame('2026-05-31T10:11:00+00:00', $projectedInstance->escalatesAt?->format(DATE_ATOM));
        self::assertSame(0, $projectedInstance->secondsUntilEscalation);
        self::assertSame(1.0, $projectedInstance->progressRatio);
        self::assertSame(0, $projectedInstance->secondsUntilBase);
        self::assertTrue($projectedInstance->baseReached);
        self::assertSame(301, $playfield->focus->taskInstance?->instanceId);
    }

    #[Test]
    public function itOrdersRedFocusByBaseDateBeforeTaskRecency(): void
    {

        $now = new DateTimeImmutable('2026-05-31T10:30:00+00:00');
        $board = $this->createBoard('Focus Board', 10);
        $lane = $this->createBoardRow($board, 'Main', 1, 101);
        $earlierTask = $this->createTask($lane, 'Earlier', TaskRiskLevel::RED, 201);
        $earlierTask->setCreatedAt(new DateTimeImmutable('2026-05-30T10:00:00+00:00'));
        $earlierTask->setNextSpawnAt(null);
        $laterTask = $this->createTask($lane, 'Later', TaskRiskLevel::RED, 202);
        $laterTask->setCreatedAt(new DateTimeImmutable('2026-05-31T10:00:00+00:00'));
        $laterTask->setNextSpawnAt(null);
        $earlierInstance = $this->createTaskInstance(
            $earlierTask,
            new DateTimeImmutable('2026-05-31T09:00:00+00:00'),
            new DateTimeImmutable('2026-05-31T09:30:00+00:00'),
            301
        );
        $laterInstance = $this->createTaskInstance(
            $laterTask,
            new DateTimeImmutable('2026-05-31T09:00:00+00:00'),
            new DateTimeImmutable('2026-05-31T09:45:00+00:00'),
            302
        );

        $builder = $this->createBuilder($board, $now, [$laterInstance, $earlierInstance]);


        $playfield = $builder->build($board, $now);


        self::assertSame(301, $playfield->focus->taskInstance?->instanceId);
    }

    #[Test]
    public function itBreaksExactRedFocusTiesByNewestTaskThenNewestInstance(): void
    {

        $now = new DateTimeImmutable('2026-05-31T10:30:00+00:00');
        $board = $this->createBoard('Focus Tie Board', 10);
        $lane = $this->createBoardRow($board, 'Main', 1, 101);
        $oldTask = $this->createTask($lane, 'Old', TaskRiskLevel::RED, 201);
        $oldTask->setCreatedAt(new DateTimeImmutable('2026-05-30T10:00:00+00:00'));
        $oldTask->setNextSpawnAt(null);
        $newTaskLowInstance = $this->createTask($lane, 'New low', TaskRiskLevel::RED, 202);
        $newTaskLowInstance->setCreatedAt(new DateTimeImmutable('2026-05-31T10:00:00+00:00'));
        $newTaskLowInstance->setNextSpawnAt(null);
        $newTaskHighInstance = $this->createTask($lane, 'New high', TaskRiskLevel::RED, 203);
        $newTaskHighInstance->setCreatedAt(new DateTimeImmutable('2026-05-31T10:00:00+00:00'));
        $newTaskHighInstance->setNextSpawnAt(null);
        $reachesBaseAt = new DateTimeImmutable('2026-05-31T10:00:00+00:00');
        $instances = [
                      $this->createTaskInstance(
                          $oldTask,
                          new DateTimeImmutable('2026-05-31T09:00:00+00:00'),
                          $reachesBaseAt,
                          301
                      ),
                      $this->createTaskInstance(
                          $newTaskLowInstance,
                          new DateTimeImmutable('2026-05-31T09:00:00+00:00'),
                          $reachesBaseAt,
                          302
                      ),
                      $this->createTaskInstance(
                          $newTaskHighInstance,
                          new DateTimeImmutable('2026-05-31T09:00:00+00:00'),
                          $reachesBaseAt,
                          303
                      ),
                     ];

        $builder = $this->createBuilder($board, $now, $instances);


        $playfield = $builder->build($board, $now);


        self::assertSame(303, $playfield->focus->taskInstance?->instanceId);
    }

    /**
     * @param list<TaskInstance> $activeTaskInstances
     */
    private function createBuilder(
        Board $board,
        DateTimeImmutable $now,
        array $activeTaskInstances
    ): BoardPlayfieldBuilder {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(TaskInstanceRepository::class);

        $repository->expects(self::never())->method('existsForTaskSpawnedAt');
        $repository->expects(self::once())
            ->method('findActiveGreenReachedBaseForBoard')
            ->with($board, $now)
            ->willReturn([]);
        $repository->expects(self::once())
            ->method('findActiveForBoard')
            ->with($board)
            ->willReturn($activeTaskInstances);
        $entityManager->expects(self::exactly(2))->method('flush');

        return new BoardPlayfieldBuilder(
            new TaskInstanceMaterializer($entityManager, $repository),
            new TaskInstanceGreenBaseAutoResolver($entityManager, $repository),
            $repository
        );
    }

    private function createBoard(string $title, int $id): Board
    {
        $board = new Board();
        $board->setTitle($title);
        $board->setIsTurretMode(true);
        $this->setEntityId($board, $id);

        return $board;
    }

    private function createBoardRow(Board $board, string $title, int $rowNumber, int $id): BoardRow
    {
        $boardRow = new BoardRow();
        $boardRow->setTitle($title);
        $boardRow->setRowNumber($rowNumber);
        $this->setEntityId($boardRow, $id);
        $board->addBoardRow($boardRow);

        return $boardRow;
    }

    private function createTask(BoardRow $boardRow, string $title, TaskRiskLevel $riskLevel, int $id): Task
    {
        $task = new Task();
        $task->setTitle($title);
        $task->setRiskLevel($riskLevel);
        $task->setSpawnDate(new DateTimeImmutable('2026-05-31T10:00:00+00:00'));
        $task->setRespawnsIn(10);
        $task->setSpawnsEvery(20);
        $task->setReachesBaseIn(10);
        $this->setEntityId($task, $id);
        $boardRow->addTask($task);

        return $task;
    }

    private function createTaskInstance(
        Task $task,
        DateTimeImmutable $spawnedAt,
        DateTimeImmutable $reachesBaseAt,
        int $id
    ): TaskInstance {
        $taskInstance = new TaskInstance($task, $spawnedAt, $reachesBaseAt);
        $this->setEntityId($taskInstance, $id);
        $task->addTaskInstance($taskInstance);

        return $taskInstance;
    }

    private function setEntityId(object $entity, int $id): void
    {
        $reflection = new \ReflectionProperty($entity, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($entity, $id);
    }
}

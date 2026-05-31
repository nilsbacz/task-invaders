<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\Entity\Task;
use App\Entity\TaskInstance;
use App\Enum\TaskRiskLevel;
use App\Repository\TaskInstanceRepository;
use App\Service\TaskInstanceMaterializer;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaskInstanceMaterializer::class)]
#[UsesClass(Board::class)]
#[UsesClass(BoardRow::class)]
#[UsesClass(Task::class)]
#[UsesClass(TaskInstance::class)]
#[UsesClass(TaskRiskLevel::class)]
final class TaskInstanceMaterializerTest extends TestCase
{
    #[Test]
    public function itMaterializesDueRecurringInstancesAndAdvancesTheCursor(): void
    {

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(TaskInstanceRepository::class);
        $board = new Board();
        $boardRow = new BoardRow();
        $task = $this->createTask(new DateTimeImmutable('2026-05-31T10:00:00+00:00'), 30);
        $task->setReachesBaseIn(15);
        $board->addBoardRow($boardRow);
        $boardRow->addTask($task);
        $persistedInstances = [];

        $repository->expects(self::exactly(3))
            ->method('existsForTaskSpawnedAt')
            ->with($task, self::isInstanceOf(DateTimeImmutable::class))
            ->willReturn(false);
        $entityManager->expects(self::exactly(3))
            ->method('persist')
            ->with(self::isInstanceOf(TaskInstance::class))
            ->willReturnCallback(
                static function (object $entity) use (&$persistedInstances): void {
                    self::assertInstanceOf(TaskInstance::class, $entity);
                    $persistedInstances[] = $entity;
                }
            );
        $entityManager->expects(self::once())->method('flush');

        $materializer = new TaskInstanceMaterializer($entityManager, $repository);


        $materializer->materialize($board, new DateTimeImmutable('2026-05-31T11:00:00+00:00'));


        self::assertCount(3, $persistedInstances);
        self::assertSame('2026-05-31T10:00:00+00:00', $persistedInstances[0]->getSpawnedAt()->format(DATE_ATOM));
        self::assertSame('2026-05-31T10:15:00+00:00', $persistedInstances[0]->getReachesBaseAt()->format(DATE_ATOM));
        self::assertSame('2026-05-31T10:30:00+00:00', $persistedInstances[1]->getSpawnedAt()->format(DATE_ATOM));
        self::assertSame('2026-05-31T11:00:00+00:00', $persistedInstances[2]->getSpawnedAt()->format(DATE_ATOM));
        self::assertSame('2026-05-31T11:30:00+00:00', $task->getNextSpawnAt()?->format(DATE_ATOM));
        self::assertCount(3, $task->getTaskInstances());
    }

    #[Test]
    public function itIsIdempotentForAlreadyMaterializedOneShotSpawns(): void
    {

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(TaskInstanceRepository::class);
        $board = new Board();
        $boardRow = new BoardRow();
        $task = $this->createTask(new DateTimeImmutable('2026-05-31T10:00:00+00:00'), 0);
        $board->addBoardRow($boardRow);
        $boardRow->addTask($task);

        $repository->expects(self::once())
            ->method('existsForTaskSpawnedAt')
            ->with($task, new DateTimeImmutable('2026-05-31T10:00:00+00:00'))
            ->willReturn(true);
        $entityManager->expects(self::never())->method('persist');
        $entityManager->expects(self::once())->method('flush');

        $materializer = new TaskInstanceMaterializer($entityManager, $repository);


        $materializer->materialize($board, new DateTimeImmutable('2026-05-31T10:05:00+00:00'));


        self::assertNull($task->getNextSpawnAt());
        self::assertCount(0, $task->getTaskInstances());
    }

    #[Test]
    public function itStopsMaterializingWhenThePerTaskCapIsExceeded(): void
    {

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(TaskInstanceRepository::class);
        $board = new Board();
        $boardRow = new BoardRow();
        $task = $this->createTask(new DateTimeImmutable('2026-05-31T10:00:00+00:00'), 1);
        $board->addBoardRow($boardRow);
        $boardRow->addTask($task);

        $repository->expects(self::exactly(2))
            ->method('existsForTaskSpawnedAt')
            ->willReturn(false);
        $entityManager->expects(self::never())->method('flush');

        $materializer = new TaskInstanceMaterializer($entityManager, $repository, 2);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Task instance materialization exceeded the per-task cap of 2');


        $materializer->materialize($board, new DateTimeImmutable('2026-05-31T10:05:00+00:00'));
    }

    private function createTask(DateTimeImmutable $nextSpawnAt, int $spawnsEvery): Task
    {
        $task = new Task();
        $task->setTitle('Workout');
        $task->setRiskLevel(TaskRiskLevel::RED);
        $task->setSpawnDate($nextSpawnAt);
        $task->setRespawnsIn(10);
        $task->setSpawnsEvery($spawnsEvery);
        $task->setReachesBaseIn(30);

        return $task;
    }
}

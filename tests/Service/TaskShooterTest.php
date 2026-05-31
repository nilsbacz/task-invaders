<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Board\Domain\BoardRow;
use App\Entity\Task;
use App\Enum\TaskRiskLevel;
use App\Service\TaskShooter;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaskShooter::class)]
#[UsesClass(BoardRow::class)]
#[UsesClass(Task::class)]
#[UsesClass(TaskRiskLevel::class)]
final class TaskShooterTest extends TestCase
{
    #[Test]
    public function itRemovesTaskWhenItDoesNotRespawnImmediately(): void
    {

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $task = $this->createTask(false);
        $boardRow = new BoardRow();
        $boardRow->addTask($task);

        $entityManager->expects(self::once())->method('remove')->with($task);
        $entityManager->expects(self::once())->method('flush');

        $shooter = new TaskShooter($entityManager);


        $shooter->shoot($task, new DateTimeImmutable('2026-05-31T10:00:00+00:00'));


        self::assertFalse($boardRow->getTasks()->contains($task));
        self::assertNull($task->getBoardRow());
    }

    #[Test]
    public function itUpdatesSpawnTimingWhenTaskRespawnsImmediately(): void
    {

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $task = $this->createTask(true);
        $task->setRespawnsIn(15);
        $task->setReachesBaseIn(45);

        $entityManager->expects(self::never())->method('remove');
        $entityManager->expects(self::once())->method('flush');

        $shooter = new TaskShooter($entityManager);


        $shooter->shoot($task, new DateTimeImmutable('2026-05-31T10:00:00+00:00'));


        self::assertSame('2026-05-31T10:15:00+00:00', $task->getSpawnDate()->format(DATE_ATOM));
        self::assertSame('2026-05-31T11:00:00+00:00', $task->getBaseDate()->format(DATE_ATOM));
    }

    private function createTask(bool $respawnImmediatelyAfterDeath): Task
    {
        $task = new Task();
        $task->setTitle('Workout');
        $task->setRiskLevel(TaskRiskLevel::GREEN);
        $task->setSpawnDate(new DateTimeImmutable('2026-04-08T00:00:00+00:00'));
        $task->setRespawnsIn(10);
        $task->setSpawnsEvery(20);
        $task->setReachesBaseIn(30);
        $task->setRespawnImmediatelyAfterDeath($respawnImmediatelyAfterDeath);

        return $task;
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Task;
use App\Entity\TaskInstance;
use App\Enum\TaskInstanceResolution;
use App\Enum\TaskRiskLevel;
use App\Service\TaskInstanceShooter;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaskInstanceShooter::class)]
#[UsesClass(Task::class)]
#[UsesClass(TaskInstance::class)]
#[UsesClass(TaskInstanceResolution::class)]
#[UsesClass(TaskRiskLevel::class)]
final class TaskInstanceShooterTest extends TestCase
{
    #[Test]
    public function itCompletesTheTaskInstanceAndFlushes(): void
    {

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $task = $this->createTask(false);
        $taskInstance = $this->createTaskInstance($task);
        $shotAt = new DateTimeImmutable('2026-05-31T10:05:00+00:00');

        $entityManager->expects(self::once())->method('flush');

        $shooter = new TaskInstanceShooter($entityManager);


        $shooter->shoot($taskInstance, $shotAt);


        self::assertSame($shotAt, $taskInstance->getCompletedAt());
        self::assertSame($shotAt, $taskInstance->getResolvedAt());
        self::assertSame(TaskInstanceResolution::SHOT, $taskInstance->getResolution());
        self::assertFalse($taskInstance->isActive());
        self::assertNull($task->getCompletedAt());
        self::assertSame('2026-05-31T10:00:00+00:00', $task->getNextSpawnAt()?->format(DATE_ATOM));
    }

    #[Test]
    public function itMovesTheParentTaskSpawnCursorForImmediateRespawnTasks(): void
    {

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $task = $this->createTask(true);
        $task->setRespawnsIn(15);
        $taskInstance = $this->createTaskInstance($task);
        $shotAt = new DateTimeImmutable('2026-05-31T10:05:00+00:00');

        $entityManager->expects(self::once())->method('flush');

        $shooter = new TaskInstanceShooter($entityManager);


        $shooter->shoot($taskInstance, $shotAt);


        self::assertSame('2026-05-31T10:20:00+00:00', $task->getNextSpawnAt()?->format(DATE_ATOM));
        self::assertSame('2026-05-31T10:00:00+00:00', $task->getSpawnDate()->format(DATE_ATOM));
    }

    private function createTask(bool $respawnImmediatelyAfterDeath): Task
    {
        $task = new Task();
        $task->setTitle('Workout');
        $task->setRiskLevel(TaskRiskLevel::RED);
        $task->setSpawnDate(new DateTimeImmutable('2026-05-31T10:00:00+00:00'));
        $task->setRespawnsIn(10);
        $task->setSpawnsEvery(20);
        $task->setReachesBaseIn(30);
        $task->setRespawnImmediatelyAfterDeath($respawnImmediatelyAfterDeath);

        return $task;
    }

    private function createTaskInstance(Task $task): TaskInstance
    {
        return new TaskInstance(
            $task,
            new DateTimeImmutable('2026-05-31T10:00:00+00:00'),
            new DateTimeImmutable('2026-05-31T10:30:00+00:00')
        );
    }
}

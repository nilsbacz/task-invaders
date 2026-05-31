<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\TaskInstance;
use App\Enum\TaskInstanceResolution;
use App\Enum\TaskRiskLevel;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaskInstance::class)]
#[UsesClass(Task::class)]
#[UsesClass(TaskInstanceResolution::class)]
#[UsesClass(TaskRiskLevel::class)]
final class TaskInstanceTest extends TestCase
{
    #[Test]
    public function itStartsAsAnActiveInstance(): void
    {

        $task = $this->createTask();
        $spawnedAt = new DateTimeImmutable('2026-05-31T10:00:00+00:00');
        $reachesBaseAt = new DateTimeImmutable('2026-05-31T10:30:00+00:00');
        $createdAt = new DateTimeImmutable('2026-05-31T09:59:00+00:00');


        $taskInstance = new TaskInstance($task, $spawnedAt, $reachesBaseAt, $createdAt);


        self::assertNull($taskInstance->getId());
        self::assertSame($task, $taskInstance->getTask());
        self::assertSame($spawnedAt, $taskInstance->getSpawnedAt());
        self::assertSame($reachesBaseAt, $taskInstance->getReachesBaseAt());
        self::assertSame($createdAt, $taskInstance->getCreatedAt());
        self::assertNull($taskInstance->getCompletedAt());
        self::assertNull($taskInstance->getResolvedAt());
        self::assertNull($taskInstance->getResolution());
        self::assertTrue($taskInstance->isActive());
    }

    #[Test]
    public function itShootsAnInstanceAsARealCompletion(): void
    {

        $taskInstance = $this->createTaskInstance();
        $shotAt = new DateTimeImmutable('2026-05-31T10:05:00+00:00');


        $result = $taskInstance->shoot($shotAt);


        self::assertSame($taskInstance, $result);
        self::assertSame($shotAt, $taskInstance->getCompletedAt());
        self::assertSame($shotAt, $taskInstance->getResolvedAt());
        self::assertSame(TaskInstanceResolution::SHOT, $taskInstance->getResolution());
        self::assertFalse($taskInstance->isActive());
    }

    #[Test]
    public function itResolvesGreenBaseRespawnWithoutCompletingTheInstance(): void
    {

        $taskInstance = $this->createTaskInstance();
        $resolvedAt = new DateTimeImmutable('2026-05-31T10:30:00+00:00');


        $result = $taskInstance->resolveGreenBaseRespawn($resolvedAt);


        self::assertSame($taskInstance, $result);
        self::assertNull($taskInstance->getCompletedAt());
        self::assertSame($resolvedAt, $taskInstance->getResolvedAt());
        self::assertSame(TaskInstanceResolution::GREEN_BASE_RESPAWN, $taskInstance->getResolution());
        self::assertFalse($taskInstance->isActive());
    }

    private function createTaskInstance(): TaskInstance
    {
        return new TaskInstance(
            $this->createTask(),
            new DateTimeImmutable('2026-05-31T10:00:00+00:00'),
            new DateTimeImmutable('2026-05-31T10:30:00+00:00')
        );
    }

    private function createTask(): Task
    {
        $task = new Task();
        $task->setTitle('Workout');
        $task->setRiskLevel(TaskRiskLevel::GREEN);
        $task->setSpawnDate(new DateTimeImmutable('2026-05-31T10:00:00+00:00'));
        $task->setReachesBaseIn(30);

        return $task;
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Sprite;
use App\Entity\Task;
use App\Entity\TaskDescription;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Task::class)]
final class TaskTest extends TestCase
{
    #[Test]
    public function itSetsDefaultsAndKeepsRelationsNullable(): void
    {
        $task = new Task();

        self::assertNull($task->getId());
        self::assertSame(0, $task->getRespawnsIn());
        self::assertSame(0, $task->getSpawnsEvery());
        self::assertFalse($task->hasShield());
        self::assertFalse($task->isRespawnImmediatelyAfterDeath());
        self::assertSame(0, $task->getSpeedFactor());
        self::assertNull($task->getTaskDescription());
        self::assertNull($task->getSprite());
    }

    #[Test]
    public function itExposesGettersAndSetters(): void
    {
        $task = new Task();
        $spawnDate = new DateTimeImmutable('2025-01-01T10:00:00+00:00');
        $description = new TaskDescription();
        $sprite = new Sprite();

        self::assertSame($task, $task->setTitle('Defend the base'));
        self::assertSame($task, $task->setRowId(7));
        self::assertSame($task, $task->setRiskLevel(2));
        self::assertSame($task, $task->setSpawnDate($spawnDate));
        self::assertSame($task, $task->setRespawnsIn(5));
        self::assertSame($task, $task->setSpawnsEvery(10));
        self::assertSame($task, $task->setReachesBaseIn(3));
        self::assertSame($task, $task->setHasShield(true));
        self::assertSame($task, $task->setRespawnImmediatelyAfterDeath(true));
        self::assertSame($task, $task->setSpeedFactor(4));
        self::assertSame($task, $task->setTaskDescription($description));
        self::assertSame($task, $task->setSprite($sprite));

        self::assertSame('Defend the base', $task->getTitle());
        self::assertSame(7, $task->getRowId());
        self::assertSame(2, $task->getRiskLevel());
        self::assertSame($spawnDate, $task->getSpawnDate());
        self::assertSame(5, $task->getRespawnsIn());
        self::assertSame(10, $task->getSpawnsEvery());
        self::assertSame(3, $task->getReachesBaseIn());
        self::assertTrue($task->hasShield());
        self::assertTrue($task->isRespawnImmediatelyAfterDeath());
        self::assertSame(4, $task->getSpeedFactor());
        self::assertSame($description, $task->getTaskDescription());
        self::assertSame($sprite, $task->getSprite());
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Board\Domain\BoardRow;
use App\Enum\TaskRiskLevel;
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
        $boardRow = new BoardRow();
        $description = new TaskDescription();
        $sprite = new Sprite();


        $setTitleResult = $task->setTitle('Defend the base');
        $setBoardRowResult = $task->setBoardRow($boardRow);
        $setRiskLevelResult = $task->setRiskLevel(TaskRiskLevel::YELLOW);
        $setSpawnDateResult = $task->setSpawnDate($spawnDate);
        $setRespawnsInResult = $task->setRespawnsIn(5);
        $setSpawnsEveryResult = $task->setSpawnsEvery(10);
        $setReachesBaseInResult = $task->setReachesBaseIn(3);
        $setHasShieldResult = $task->setHasShield(true);
        $setRespawnImmediatelyAfterDeathResult = $task->setRespawnImmediatelyAfterDeath(true);
        $setSpeedFactorResult = $task->setSpeedFactor(4);
        $setTaskDescriptionResult = $task->setTaskDescription($description);
        $setSpriteResult = $task->setSprite($sprite);


        self::assertSame($task, $setTitleResult);
        self::assertSame($task, $setBoardRowResult);
        self::assertSame($task, $setRiskLevelResult);
        self::assertSame($task, $setSpawnDateResult);
        self::assertSame($task, $setRespawnsInResult);
        self::assertSame($task, $setSpawnsEveryResult);
        self::assertSame($task, $setReachesBaseInResult);
        self::assertSame($task, $setHasShieldResult);
        self::assertSame($task, $setRespawnImmediatelyAfterDeathResult);
        self::assertSame($task, $setSpeedFactorResult);
        self::assertSame($task, $setTaskDescriptionResult);
        self::assertSame($task, $setSpriteResult);
        self::assertSame('Defend the base', $task->getTitle());
        self::assertSame($boardRow, $task->getBoardRow());
        self::assertSame(TaskRiskLevel::YELLOW, $task->getRiskLevel());
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

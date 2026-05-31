<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\Enum\TaskRiskLevel;
use App\Entity\Sprite;
use App\Entity\Task;
use App\Entity\TaskDescription;
use DateTimeImmutable;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Task::class)]
#[UsesClass(Board::class)]
#[UsesClass(BoardRow::class)]
#[UsesClass(Sprite::class)]
#[UsesClass(TaskDescription::class)]
#[UsesClass(TaskRiskLevel::class)]
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
        self::assertNull($task->getCompletedAt());
        self::assertFalse($task->isCompleted());
        self::assertTrue($task->shouldAppearOnBoard());
        self::assertTrue($task->canBeDeleted());
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

    #[Test]
    public function itMarksTaskCompleted(): void
    {

        $task = new Task();
        $completedAt = new DateTimeImmutable('2026-05-31T10:00:00+00:00');


        $result = $task->complete($completedAt);


        self::assertSame($task, $result);
        self::assertSame($completedAt, $task->getCompletedAt());
        self::assertTrue($task->isCompleted());
        self::assertFalse($task->shouldAppearOnBoard());
    }

    #[Test]
    public function itKeepsImmediatelyRespawningCompletedTasksVisibleOnTheBoard(): void
    {

        $task = new Task();
        $task->setRespawnImmediatelyAfterDeath(true);
        $task->complete(new DateTimeImmutable('2026-05-31T10:00:00+00:00'));


        self::assertTrue($task->isCompleted());
        self::assertTrue($task->shouldAppearOnBoard());
    }

    #[Test]
    public function itSchedulesNextSpawnAndBaseDatesFromShotTiming(): void
    {

        $task = new Task();
        $shotAt = new DateTimeImmutable('2026-05-31T10:00:00+00:00');
        $task->setRespawnsIn(30);
        $task->setReachesBaseIn(90);


        $result = $task->scheduleNextSpawnAfterShot($shotAt);


        self::assertSame($task, $result);
        self::assertSame('2026-05-31T10:30:00+00:00', $task->getSpawnDate()->format(DATE_ATOM));
        self::assertSame('2026-05-31T12:00:00+00:00', $task->getBaseDate()->format(DATE_ATOM));
    }

    #[Test]
    public function itRejectsDeletionWhenAttachedToBoard(): void
    {

        $board = new Board();
        $boardRow = new BoardRow();
        $task = new Task();
        $board->addBoardRow($boardRow);
        $boardRow->addTask($task);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Tasks attached to a board cannot be deleted.');


        $task->assertCanBeDeleted();
    }

    #[Test]
    public function itAllowsDeletionWhenNotAttachedToBoard(): void
    {

        $task = new Task();


        $task->assertCanBeDeleted();


        self::assertTrue($task->canBeDeleted());
    }
}

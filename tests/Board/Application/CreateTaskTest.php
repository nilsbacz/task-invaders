<?php

declare(strict_types=1);

namespace App\Tests\Board\Application;

use App\Board\Application\CreateTask;
use App\Enum\TaskRiskLevel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreateTask::class)]
final class CreateTaskTest extends TestCase
{
    #[Test]
    public function itExposesDefaultsGettersAndSetters(): void
    {
        // Arrange
        $command = new CreateTask();

        self::assertSame('', $command->getTitle());
        self::assertSame(TaskRiskLevel::GREEN, $command->getRiskLevel());
        self::assertSame(0, $command->getRespawnsIn());
        self::assertSame(0, $command->getSpawnsEvery());
        self::assertSame(60, $command->getReachesBaseIn());
        self::assertFalse($command->hasShield());
        self::assertFalse($command->isRespawnImmediatelyAfterDeath());
        self::assertSame(0, $command->getSpeedFactor());

        // Act
        $setTitleResult = $command->setTitle('Workout');
        $setRiskResult = $command->setRiskLevel(TaskRiskLevel::RED);
        $setRespawnsInResult = $command->setRespawnsIn(15);
        $setSpawnsEveryResult = $command->setSpawnsEvery(45);
        $setReachesBaseInResult = $command->setReachesBaseIn(90);
        $setShieldResult = $command->setHasShield(true);
        $setRespawnResult = $command->setRespawnImmediatelyAfterDeath(true);
        $setSpeedResult = $command->setSpeedFactor(2);

        // Assert
        self::assertSame($command, $setTitleResult);
        self::assertSame($command, $setRiskResult);
        self::assertSame($command, $setRespawnsInResult);
        self::assertSame($command, $setSpawnsEveryResult);
        self::assertSame($command, $setReachesBaseInResult);
        self::assertSame($command, $setShieldResult);
        self::assertSame($command, $setRespawnResult);
        self::assertSame($command, $setSpeedResult);
        self::assertSame('Workout', $command->getTitle());
        self::assertSame(TaskRiskLevel::RED, $command->getRiskLevel());
        self::assertSame(15, $command->getRespawnsIn());
        self::assertSame(45, $command->getSpawnsEvery());
        self::assertSame(90, $command->getReachesBaseIn());
        self::assertTrue($command->hasShield());
        self::assertTrue($command->isRespawnImmediatelyAfterDeath());
        self::assertSame(2, $command->getSpeedFactor());
    }
}

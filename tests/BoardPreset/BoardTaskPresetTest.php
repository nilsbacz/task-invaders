<?php

declare(strict_types=1);

namespace App\Tests\BoardPreset;

use App\BoardPreset\BoardTaskPreset;
use App\Enum\TaskRiskLevel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BoardTaskPreset::class)]
#[UsesClass(TaskRiskLevel::class)]
final class BoardTaskPresetTest extends TestCase
{
    #[Test]
    public function itUsesExplicitAndDefaultConstructorValues(): void
    {

        $taskPreset = new BoardTaskPreset('workout', 10, 20, 30, TaskRiskLevel::YELLOW);


        self::assertSame('workout', $taskPreset->title);
        self::assertSame(10, $taskPreset->respawnsIn);
        self::assertSame(20, $taskPreset->spawnsEvery);
        self::assertSame(30, $taskPreset->reachesBaseIn);
        self::assertSame(TaskRiskLevel::YELLOW, $taskPreset->riskLevel);
        self::assertFalse($taskPreset->hasShield);
        self::assertFalse($taskPreset->respawnImmediatelyAfterDeath);
        self::assertSame(0, $taskPreset->speedFactor);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\BoardPreset;

use App\BoardPreset\BoardRowPreset;
use App\BoardPreset\BoardTaskPreset;
use App\Enum\TaskRiskLevel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BoardRowPreset::class)]
#[UsesClass(BoardTaskPreset::class)]
final class BoardRowPresetTest extends TestCase
{
    #[Test]
    public function itExposesItsConstructorValues(): void
    {

        $taskPreset = new BoardTaskPreset('workout', 10, 20, 30, TaskRiskLevel::YELLOW);


        $boardRowPreset = new BoardRowPreset('sports', 1, [$taskPreset]);


        self::assertSame('sports', $boardRowPreset->title);
        self::assertSame(1, $boardRowPreset->position);
        self::assertSame([$taskPreset], $boardRowPreset->tasks);
    }
}

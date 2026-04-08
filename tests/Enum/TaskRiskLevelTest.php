<?php

declare(strict_types=1);

namespace App\Tests\Enum;

use App\Enum\TaskRiskLevel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaskRiskLevel::class)]
final class TaskRiskLevelTest extends TestCase
{
    #[Test]
    public function itDefinesExpectedBackedValues(): void
    {
        self::assertSame('GREEN', TaskRiskLevel::GREEN->value);
        self::assertSame('YELLOW', TaskRiskLevel::YELLOW->value);
        self::assertSame('RED', TaskRiskLevel::RED->value);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\TaskDescription;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaskDescription::class)]
final class TaskDescriptionTest extends TestCase
{
    #[Test]
    public function it_manages_description_and_tasks_collection(): void
    {
        $description = new TaskDescription();

        self::assertNull($description->getId());
        self::assertCount(0, $description->getTasks());

        self::assertSame($description, $description->setDescription('Complete the mission.'));
        self::assertSame('Complete the mission.', $description->getDescription());

        $task = new Task();
        self::assertSame($description, $description->addTask($task));
        self::assertSame($description, $description->addTask($task));

        self::assertCount(1, $description->getTasks());
        self::assertTrue($description->getTasks()->contains($task));
        self::assertSame($description, $task->getTaskDescription());

        self::assertSame($description, $description->removeTask($task));

        self::assertCount(0, $description->getTasks());
        self::assertFalse($description->getTasks()->contains($task));
        self::assertNull($task->getTaskDescription());
    }
}

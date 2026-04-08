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
    public function itManagesDescriptionAndTasksCollection(): void
    {

        $description = new TaskDescription();
        $task = new Task();

        self::assertNull($description->getId());
        self::assertCount(0, $description->getTasks());


        $setDescriptionResult = $description->setDescription('Complete the mission.');
        $firstAddTaskResult = $description->addTask($task);
        $secondAddTaskResult = $description->addTask($task);


        self::assertSame($description, $setDescriptionResult);
        self::assertSame('Complete the mission.', $description->getDescription());
        self::assertSame($description, $firstAddTaskResult);
        self::assertSame($description, $secondAddTaskResult);
        self::assertCount(1, $description->getTasks());
        self::assertTrue($description->getTasks()->contains($task));
        self::assertSame($description, $task->getTaskDescription());


        $removeTaskResult = $description->removeTask($task);


        self::assertSame($description, $removeTaskResult);
        self::assertCount(0, $description->getTasks());
        self::assertFalse($description->getTasks()->contains($task));
        self::assertNull($task->getTaskDescription());
    }
}

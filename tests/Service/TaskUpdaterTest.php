<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Task;
use App\Service\TaskUpdater;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaskUpdater::class)]
final class TaskUpdaterTest extends TestCase
{
    #[Test]
    public function itTrimsTaskTitleFlushesAndReturnsTask(): void
    {
        // Arrange
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $updater = new TaskUpdater($entityManager);
        $task = new Task();
        $task->setTitle('  Updated Task  ');

        // Act
        $result = $updater->update($task);

        // Assert
        self::assertSame($task, $result);
        self::assertSame('Updated Task', $task->getTitle());
    }
}

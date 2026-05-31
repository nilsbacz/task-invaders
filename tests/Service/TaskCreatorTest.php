<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Board\Application\CreateTask;
use App\Board\Domain\BoardRow;
use App\Entity\Task;
use App\Enum\TaskRiskLevel;
use App\Service\TaskCreator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaskCreator::class)]
#[UsesClass(BoardRow::class)]
#[UsesClass(CreateTask::class)]
#[UsesClass(Task::class)]
#[UsesClass(TaskRiskLevel::class)]
final class TaskCreatorTest extends TestCase
{
    #[Test]
    public function itCreatesTaskOnBoardRowAndFlushes(): void
    {
        // Arrange
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $boardRow = new BoardRow();
        $command = new CreateTask();
        $command->setTitle('  Workout  ');
        $command->setRiskLevel(TaskRiskLevel::RED);
        $command->setRespawnsIn(15);
        $command->setSpawnsEvery(45);
        $command->setReachesBaseIn(90);
        $command->setHasShield(true);
        $command->setRespawnImmediatelyAfterDeath(true);
        $command->setSpeedFactor(2);

        $entityManager->expects(self::once())->method('persist')->with(self::isInstanceOf(Task::class));
        $entityManager->expects(self::once())->method('flush');

        $creator = new TaskCreator($entityManager);

        // Act
        $task = $creator->create($boardRow, $command);

        // Assert
        self::assertSame('Workout', $task->getTitle());
        self::assertSame(TaskRiskLevel::RED, $task->getRiskLevel());
        self::assertSame(15, $task->getRespawnsIn());
        self::assertSame(45, $task->getSpawnsEvery());
        self::assertSame(90, $task->getReachesBaseIn());
        self::assertTrue($task->hasShield());
        self::assertTrue($task->isRespawnImmediatelyAfterDeath());
        self::assertSame(2, $task->getSpeedFactor());
        self::assertSame($boardRow, $task->getBoardRow());
        self::assertTrue($boardRow->getTasks()->contains($task));
    }
}

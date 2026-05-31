<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Board\Domain\Board;
use App\Entity\Task;
use App\Entity\TaskInstance;
use App\Enum\TaskInstanceResolution;
use App\Enum\TaskRiskLevel;
use App\Repository\TaskInstanceRepository;
use App\Service\TaskInstanceGreenBaseAutoResolver;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaskInstanceGreenBaseAutoResolver::class)]
#[UsesClass(Board::class)]
#[UsesClass(Task::class)]
#[UsesClass(TaskInstance::class)]
#[UsesClass(TaskInstanceResolution::class)]
#[UsesClass(TaskRiskLevel::class)]
final class TaskInstanceGreenBaseAutoResolverTest extends TestCase
{
    #[Test]
    public function itResolvesReachedGreenInstancesWithoutCompletingThem(): void
    {

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(TaskInstanceRepository::class);
        $board = new Board();
        $now = new DateTimeImmutable('2026-05-31T10:30:00+00:00');
        $firstInstance = $this->createGreenTaskInstance();
        $secondInstance = $this->createGreenTaskInstance();

        $repository->expects(self::once())
            ->method('findActiveGreenReachedBaseForBoard')
            ->with($board, $now)
            ->willReturn([$firstInstance, $secondInstance]);
        $entityManager->expects(self::once())->method('flush');

        $resolver = new TaskInstanceGreenBaseAutoResolver($entityManager, $repository);


        $resolver->resolve($board, $now);


        foreach ([$firstInstance, $secondInstance] as $taskInstance) {
            self::assertNull($taskInstance->getCompletedAt());
            self::assertSame($now, $taskInstance->getResolvedAt());
            self::assertSame(TaskInstanceResolution::GREEN_BASE_RESPAWN, $taskInstance->getResolution());
            self::assertFalse($taskInstance->isActive());
        }
    }

    private function createGreenTaskInstance(): TaskInstance
    {
        $task = new Task();
        $task->setTitle('Hydrate');
        $task->setRiskLevel(TaskRiskLevel::GREEN);
        $task->setSpawnDate(new DateTimeImmutable('2026-05-31T10:00:00+00:00'));
        $task->setReachesBaseIn(30);

        return new TaskInstance(
            $task,
            new DateTimeImmutable('2026-05-31T10:00:00+00:00'),
            new DateTimeImmutable('2026-05-31T10:30:00+00:00')
        );
    }
}

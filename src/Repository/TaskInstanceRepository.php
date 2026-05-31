<?php

declare(strict_types=1);

namespace App\Repository;

use App\Board\Domain\Board;
use App\Entity\Task;
use App\Entity\TaskInstance;
use App\Enum\TaskRiskLevel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TaskInstance>
 */
class TaskInstanceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskInstance::class);
    }

    public function existsForTaskSpawnedAt(Task $task, \DateTimeImmutable $spawnedAt): bool
    {
        $result = $this->createQueryBuilder('taskInstance')
            ->select('COUNT(taskInstance.id)')
            ->andWhere('taskInstance.task = :task')
            ->andWhere('taskInstance.spawnedAt = :spawnedAt')
            ->setParameter('task', $task)
            ->setParameter('spawnedAt', $spawnedAt)
            ->getQuery()
            ->getSingleScalarResult();

        if (!is_numeric($result)) {
            throw new \RuntimeException('Could not count task instances for spawn idempotency check.');
        }

        return (int) $result > 0;
    }

    /**
     * @return list<TaskInstance>
     */
    public function findActiveForBoard(Board $board): array
    {
        /** @var list<TaskInstance> $instances */
        $instances = $this->createQueryBuilder('taskInstance')
            ->join('taskInstance.task', 'taskDefinition')
            ->join('taskDefinition.boardRow', 'boardRow')
            ->andWhere('boardRow.board = :board')
            ->andWhere('taskInstance.resolvedAt IS NULL')
            ->andWhere('taskInstance.completedAt IS NULL')
            ->setParameter('board', $board)
            ->orderBy('boardRow.rowNumber', 'ASC')
            ->addOrderBy('taskInstance.reachesBaseAt', 'ASC')
            ->addOrderBy('taskInstance.id', 'ASC')
            ->getQuery()
            ->getResult();

        return $instances;
    }

    /**
     * @return list<TaskInstance>
     */
    public function findActiveGreenReachedBaseForBoard(Board $board, \DateTimeImmutable $now): array
    {
        /** @var list<TaskInstance> $instances */
        $instances = $this->createQueryBuilder('taskInstance')
            ->join('taskInstance.task', 'taskDefinition')
            ->join('taskDefinition.boardRow', 'boardRow')
            ->andWhere('boardRow.board = :board')
            ->andWhere('taskDefinition.riskLevel = :riskLevel')
            ->andWhere('taskInstance.resolvedAt IS NULL')
            ->andWhere('taskInstance.completedAt IS NULL')
            ->andWhere('taskInstance.reachesBaseAt <= :now')
            ->setParameter('board', $board)
            ->setParameter('riskLevel', TaskRiskLevel::GREEN)
            ->setParameter('now', $now)
            ->orderBy('taskInstance.reachesBaseAt', 'ASC')
            ->addOrderBy('taskInstance.id', 'ASC')
            ->getQuery()
            ->getResult();

        return $instances;
    }
}

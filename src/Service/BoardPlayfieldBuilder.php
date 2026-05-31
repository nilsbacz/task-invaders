<?php

declare(strict_types=1);

namespace App\Service;

use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\Entity\Task;
use App\Entity\TaskInstance;
use App\Enum\TaskRiskLevel;
use App\Playfield\BoardPlayfield;
use App\Playfield\BoardPlayfieldFocus;
use App\Playfield\BoardPlayfieldLane;
use App\Playfield\BoardPlayfieldTaskInstance;
use App\Playfield\BoardPlayfieldUpcomingTask;
use App\Repository\TaskInstanceRepository;

final readonly class BoardPlayfieldBuilder
{
    public function __construct(
        private TaskInstanceMaterializer $materializer,
        private TaskInstanceGreenBaseAutoResolver $greenBaseAutoResolver,
        private TaskInstanceRepository $taskInstances,
    ) {
    }

    public function build(Board $board, \DateTimeImmutable $now): BoardPlayfield
    {
        $this->materializer->materialize($board, $now);
        $this->greenBaseAutoResolver->resolve($board, $now);

        $activeTaskInstances = $this->taskInstances->findActiveForBoard($board);
        $taskInstancesByLane = $this->groupTaskInstancesByLane($activeTaskInstances);

        $lanes = [];
        $taskInstanceProjections = [];

        foreach ($this->sortedBoardRows($board) as $boardRow) {
            $laneKey = $this->laneKey($boardRow);
            $taskInstanceProjections[$laneKey] = [];

            foreach ($taskInstancesByLane[$laneKey] ?? [] as $taskInstance) {
                $taskInstanceProjections[$laneKey][] = $this->projectTaskInstance($taskInstance, $now);
            }

            $lanes[] = new BoardPlayfieldLane(
                $boardRow->getId(),
                $boardRow->getTitle(),
                $boardRow->getRowNumber(),
                $taskInstanceProjections[$laneKey],
                $this->projectUpcomingTasks($boardRow)
            );
        }

        return new BoardPlayfield(
            $board->getId(),
            $board->getTitle(),
            $now,
            $lanes,
            new BoardPlayfieldFocus($this->focusedTaskInstance($activeTaskInstances, $now))
        );
    }

    /**
     * @param list<TaskInstance> $taskInstances
     *
     * @return array<string, list<TaskInstance>>
     */
    private function groupTaskInstancesByLane(array $taskInstances): array
    {
        $taskInstancesByLane = [];

        foreach ($taskInstances as $taskInstance) {
            $boardRow = $taskInstance->getTask()->getBoardRow();
            if ($boardRow === null) {
                continue;
            }

            $taskInstancesByLane[$this->laneKey($boardRow)][] = $taskInstance;
        }

        return $taskInstancesByLane;
    }

    /**
     * @return list<BoardRow>
     */
    private function sortedBoardRows(Board $board): array
    {
        /** @var list<BoardRow> $boardRows */
        $boardRows = array_values($board->getBoardRows()->toArray());
        usort(
            $boardRows,
            static fn (BoardRow $left, BoardRow $right): int => $left->getRowNumber() <=> $right->getRowNumber()
        );

        return $boardRows;
    }

    /**
     * @return list<BoardPlayfieldUpcomingTask>
     */
    private function projectUpcomingTasks(BoardRow $boardRow): array
    {
        $upcomingTasks = [];

        foreach ($boardRow->getTasks() as $task) {
            if (!$task->shouldAppearOnBoard()) {
                continue;
            }

            $nextSpawnAt = $task->getNextSpawnAt();
            if ($nextSpawnAt === null) {
                continue;
            }

            $upcomingTasks[] = new BoardPlayfieldUpcomingTask(
                $task->getId(),
                $task->getTitle(),
                $boardRow->getId(),
                $task->getRiskLevel(),
                $nextSpawnAt,
                $task->reachesBaseAt($nextSpawnAt),
                $task->hasShield()
            );
        }

        usort(
            $upcomingTasks,
            static fn (
                BoardPlayfieldUpcomingTask $left,
                BoardPlayfieldUpcomingTask $right
            ): int => $left->nextSpawnAt->getTimestamp() <=> $right->nextSpawnAt->getTimestamp()
        );

        return $upcomingTasks;
    }

    private function projectTaskInstance(
        TaskInstance $taskInstance,
        \DateTimeImmutable $now
    ): BoardPlayfieldTaskInstance {
        $task = $taskInstance->getTask();
        $boardRow = $task->getBoardRow();
        $spawnedAt = $taskInstance->getSpawnedAt();
        $reachesBaseAt = $taskInstance->getReachesBaseAt();
        $lifetimeSeconds = max(0, $reachesBaseAt->getTimestamp() - $spawnedAt->getTimestamp());
        $elapsedSeconds = max(0, $now->getTimestamp() - $spawnedAt->getTimestamp());
        $secondsUntilBase = max(0, $reachesBaseAt->getTimestamp() - $now->getTimestamp());
        $baseReached = $now >= $reachesBaseAt;
        $progressRatio = $this->progressRatio($lifetimeSeconds, $elapsedSeconds, $baseReached);
        $escalatesAt = $this->yellowEscalatesAt($task, $taskInstance);
        $secondsUntilEscalation = null;
        if ($escalatesAt !== null) {
            $secondsUntilEscalation = max(0, $escalatesAt->getTimestamp() - $now->getTimestamp());
        }

        return new BoardPlayfieldTaskInstance(
            $task->getId(),
            $taskInstance->getId(),
            $task->getTitle(),
            $boardRow?->getId(),
            $task->getRiskLevel(),
            $this->visualRiskLevel($task, $escalatesAt, $now),
            $spawnedAt,
            $reachesBaseAt,
            $lifetimeSeconds,
            $elapsedSeconds,
            $progressRatio,
            $secondsUntilBase,
            $baseReached,
            $escalatesAt,
            $secondsUntilEscalation,
            $task->hasShield()
        );
    }

    private function progressRatio(int $lifetimeSeconds, int $elapsedSeconds, bool $baseReached): float
    {
        if ($lifetimeSeconds === 0) {
            return $baseReached ? 1.0 : 0.0;
        }

        return min(1.0, max(0.0, $elapsedSeconds / $lifetimeSeconds));
    }

    private function visualRiskLevel(
        Task $task,
        ?\DateTimeImmutable $escalatesAt,
        \DateTimeImmutable $now
    ): TaskRiskLevel {
        if ($task->getRiskLevel() !== TaskRiskLevel::YELLOW || $escalatesAt === null) {
            return $task->getRiskLevel();
        }

        return $now >= $escalatesAt ? TaskRiskLevel::RED : TaskRiskLevel::YELLOW;
    }

    private function yellowEscalatesAt(Task $task, TaskInstance $taskInstance): ?\DateTimeImmutable
    {
        if ($task->getRiskLevel() !== TaskRiskLevel::YELLOW) {
            return null;
        }

        $lifetimeSeconds = max(
            0,
            $taskInstance->getReachesBaseAt()->getTimestamp() - $taskInstance->getSpawnedAt()->getTimestamp()
        );
        $overtimeSeconds = (int) ceil($lifetimeSeconds * 0.1);

        return $taskInstance->getReachesBaseAt()->add(new \DateInterval(sprintf('PT%dS', $overtimeSeconds)));
    }

    /**
     * @param list<TaskInstance> $activeTaskInstances
     */
    private function focusedTaskInstance(
        array $activeTaskInstances,
        \DateTimeImmutable $now
    ): ?BoardPlayfieldTaskInstance {
        $candidates = array_values(array_filter(
            $activeTaskInstances,
            fn (TaskInstance $taskInstance): bool => $this->isFocusCandidate($taskInstance, $now)
        ));

        usort(
            $candidates,
            static function (TaskInstance $left, TaskInstance $right): int {
                $leftBaseTimestamp = $left->getReachesBaseAt()->getTimestamp();
                $rightBaseTimestamp = $right->getReachesBaseAt()->getTimestamp();
                $baseComparison = $leftBaseTimestamp <=> $rightBaseTimestamp;
                if ($baseComparison !== 0) {
                    return $baseComparison;
                }

                $rightTaskCreatedTimestamp = $right->getTask()->getCreatedAt()->getTimestamp();
                $leftTaskCreatedTimestamp = $left->getTask()->getCreatedAt()->getTimestamp();
                $taskCreatedComparison = $rightTaskCreatedTimestamp <=> $leftTaskCreatedTimestamp;
                if ($taskCreatedComparison !== 0) {
                    return $taskCreatedComparison;
                }

                return ($right->getId() ?? 0) <=> ($left->getId() ?? 0);
            }
        );

        if ($candidates === []) {
            return null;
        }

        return $this->projectTaskInstance($candidates[0], $now);
    }

    private function isFocusCandidate(TaskInstance $taskInstance, \DateTimeImmutable $now): bool
    {
        $task = $taskInstance->getTask();

        if ($task->getRiskLevel() === TaskRiskLevel::RED) {
            return $now >= $taskInstance->getReachesBaseAt();
        }

        if ($task->getRiskLevel() !== TaskRiskLevel::YELLOW) {
            return false;
        }

        $escalatesAt = $this->yellowEscalatesAt($task, $taskInstance);
        if ($escalatesAt === null) {
            return false;
        }

        return $now >= $escalatesAt;
    }

    private function laneKey(BoardRow $boardRow): string
    {
        $boardRowId = $boardRow->getId();
        if ($boardRowId !== null) {
            return 'id_' . $boardRowId;
        }

        return 'object_' . spl_object_id($boardRow);
    }
}

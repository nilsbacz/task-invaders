<?php

declare(strict_types=1);

namespace App\Playfield;

final readonly class BoardPlayfieldLane
{
    /**
     * @param list<BoardPlayfieldTaskInstance> $taskInstances
     * @param list<BoardPlayfieldUpcomingTask> $upcomingTasks
     */
    public function __construct(
        public ?int $laneId,
        public string $title,
        public int $rowNumber,
        public array $taskInstances,
        public array $upcomingTasks,
    ) {
    }
}

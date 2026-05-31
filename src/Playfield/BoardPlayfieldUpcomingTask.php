<?php

declare(strict_types=1);

namespace App\Playfield;

use App\Enum\TaskRiskLevel;

final readonly class BoardPlayfieldUpcomingTask
{
    public function __construct(
        public ?int $taskId,
        public string $title,
        public ?int $laneId,
        public TaskRiskLevel $riskLevel,
        public \DateTimeImmutable $nextSpawnAt,
        public \DateTimeImmutable $reachesBaseAt,
        public bool $hasShield,
    ) {
    }
}

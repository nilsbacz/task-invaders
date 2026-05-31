<?php

declare(strict_types=1);

namespace App\Playfield;

use App\Enum\TaskRiskLevel;

final readonly class BoardPlayfieldTaskInstance
{
    public function __construct(
        public ?int $taskId,
        public ?int $instanceId,
        public string $title,
        public ?int $laneId,
        public TaskRiskLevel $taskRiskLevel,
        public TaskRiskLevel $visualRiskLevel,
        public \DateTimeImmutable $spawnedAt,
        public \DateTimeImmutable $reachesBaseAt,
        public int $lifetimeSeconds,
        public int $elapsedSeconds,
        public float $progressRatio,
        public int $secondsUntilBase,
        public bool $baseReached,
        public ?\DateTimeImmutable $escalatesAt,
        public ?int $secondsUntilEscalation,
        public bool $hasShield,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace App\BoardPreset;

use App\Enum\TaskRiskLevel;

final readonly class BoardTaskPreset
{
    public function __construct(
        public string $title,
        public int $respawnsIn,
        public int $spawnsEvery,
        public int $reachesBaseIn,
        public TaskRiskLevel $riskLevel,
        public bool $hasShield = false,
        public bool $respawnImmediatelyAfterDeath = false,
        public int $speedFactor = 0,
    ) {
    }
}

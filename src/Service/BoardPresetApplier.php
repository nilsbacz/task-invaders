<?php

declare(strict_types=1);

namespace App\Service;

use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\Entity\Task;

final readonly class BoardPresetApplier
{
    public function __construct(
        private BoardPresetLoader $boardPresetLoader,
    ) {
    }

    public function applyDefaultPreset(Board $board): void
    {
        $preset = $this->boardPresetLoader->load('default');

        foreach ($preset->boardRows as $boardRowPreset) {
            $boardRow = new BoardRow();
            $boardRow->setTitle($boardRowPreset->title);
            $boardRow->setRowNumber($boardRowPreset->position);

            foreach ($boardRowPreset->tasks as $taskPreset) {
                $task = new Task();
                $task->setTitle($taskPreset->title);
                $task->setRespawnsIn($taskPreset->respawnsIn);
                $task->setSpawnsEvery($taskPreset->spawnsEvery);
                $task->setReachesBaseIn($taskPreset->reachesBaseIn);
                $task->setRiskLevel($taskPreset->riskLevel);
                $task->setHasShield($taskPreset->hasShield);
                $task->setRespawnImmediatelyAfterDeath($taskPreset->respawnImmediatelyAfterDeath);
                $task->setSpeedFactor($taskPreset->speedFactor);
                $task->setSpawnDate(new \DateTimeImmutable());
                $boardRow->addTask($task);
            }

            $board->addBoardRow($boardRow);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Board\Application;

use App\Enum\TaskRiskLevel;
use Symfony\Component\Validator\Constraints as Assert;

final class CreateTask
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 32)]
    private string $title = '';

    #[Assert\NotNull]
    private TaskRiskLevel $riskLevel = TaskRiskLevel::GREEN;

    #[Assert\GreaterThanOrEqual(value: 0)]
    private int $respawnsIn = 0;

    #[Assert\GreaterThanOrEqual(value: 0)]
    private int $spawnsEvery = 0;

    #[Assert\GreaterThanOrEqual(value: 0)]
    private int $reachesBaseIn = 60;

    private bool $hasShield = false;

    private bool $respawnImmediatelyAfterDeath = false;

    #[Assert\GreaterThanOrEqual(value: 0)]
    private int $speedFactor = 0;

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getRiskLevel(): TaskRiskLevel
    {
        return $this->riskLevel;
    }

    public function setRiskLevel(TaskRiskLevel $riskLevel): static
    {
        $this->riskLevel = $riskLevel;

        return $this;
    }

    public function getRespawnsIn(): int
    {
        return $this->respawnsIn;
    }

    public function setRespawnsIn(int $respawnsIn): static
    {
        $this->respawnsIn = $respawnsIn;

        return $this;
    }

    public function getSpawnsEvery(): int
    {
        return $this->spawnsEvery;
    }

    public function setSpawnsEvery(int $spawnsEvery): static
    {
        $this->spawnsEvery = $spawnsEvery;

        return $this;
    }

    public function getReachesBaseIn(): int
    {
        return $this->reachesBaseIn;
    }

    public function setReachesBaseIn(int $reachesBaseIn): static
    {
        $this->reachesBaseIn = $reachesBaseIn;

        return $this;
    }

    public function hasShield(): bool
    {
        return $this->hasShield;
    }

    public function setHasShield(bool $hasShield): static
    {
        $this->hasShield = $hasShield;

        return $this;
    }

    public function isRespawnImmediatelyAfterDeath(): bool
    {
        return $this->respawnImmediatelyAfterDeath;
    }

    public function setRespawnImmediatelyAfterDeath(bool $respawnImmediatelyAfterDeath): static
    {
        $this->respawnImmediatelyAfterDeath = $respawnImmediatelyAfterDeath;

        return $this;
    }

    public function getSpeedFactor(): int
    {
        return $this->speedFactor;
    }

    public function setSpeedFactor(int $speedFactor): static
    {
        $this->speedFactor = $speedFactor;

        return $this;
    }
}

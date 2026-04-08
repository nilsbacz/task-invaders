<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\TaskRiskLevel;
use App\Repository\TaskRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line */
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    private string $title;

    #[ORM\Column(name: 'board_row_id')]
    private int $boardRowId;

    #[ORM\Column(
        type: Types::STRING,
        length: 6,
        enumType: TaskRiskLevel::class,
        columnDefinition: "ENUM('GREEN', 'YELLOW', 'RED') NOT NULL"
    )]
    private TaskRiskLevel $riskLevel;

    #[ORM\Column]
    private \DateTimeImmutable $spawnDate;

    #[ORM\Column]
    private int $respawnsIn = 0;

    #[ORM\Column]
    private int $spawnsEvery = 0;

    #[ORM\Column]
    private int $reachesBaseIn;

    #[ORM\Column]
    private bool $hasShield = false;

    #[ORM\Column]
    private bool $respawnImmediatelyAfterDeath = false;

    #[ORM\Column]
    private int $speedFactor = 0;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    private ?TaskDescription $taskDescription = null;

    #[ORM\ManyToOne]
    private ?Sprite $sprite = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getBoardRowId(): int
    {
        return $this->boardRowId;
    }

    public function setBoardRowId(int $boardRowId): static
    {
        $this->boardRowId = $boardRowId;

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

    public function getSpawnDate(): \DateTimeImmutable
    {
        return $this->spawnDate;
    }

    public function setSpawnDate(\DateTimeImmutable $spawnDate): static
    {
        $this->spawnDate = $spawnDate;

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

    public function getTaskDescription(): ?TaskDescription
    {
        return $this->taskDescription;
    }

    public function setTaskDescription(?TaskDescription $taskDescription): static
    {
        $this->taskDescription = $taskDescription;

        return $this;
    }

    public function getSprite(): ?Sprite
    {
        return $this->sprite;
    }

    public function setSprite(?Sprite $sprite): static
    {
        $this->sprite = $sprite;

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\TaskInstanceResolution;
use App\Repository\TaskInstanceRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskInstanceRepository::class)]
#[ORM\Table(name: 'task_instance')]
#[ORM\Index(name: 'IDX_TASK_INSTANCE_ACTIVE', columns: ['resolved_at', 'completed_at', 'reaches_base_at'])]
#[ORM\Index(name: 'IDX_TASK_INSTANCE_TASK_ACTIVE', columns: ['task_id', 'resolved_at'])]
#[ORM\UniqueConstraint(name: 'UNIQ_TASK_INSTANCE_TASK_SPAWNED_AT', columns: ['task_id', 'spawned_at'])]
class TaskInstance
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line */
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'taskInstances')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Task $task;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $spawnedAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $reachesBaseAt;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $resolvedAt = null;

    #[ORM\Column(length: 32, nullable: true, enumType: TaskInstanceResolution::class)]
    private ?TaskInstanceResolution $resolution = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private \DateTimeImmutable $createdAt;

    public function __construct(
        Task $task,
        \DateTimeImmutable $spawnedAt,
        \DateTimeImmutable $reachesBaseAt,
        ?\DateTimeImmutable $createdAt = null
    ) {
        $this->task = $task;
        $this->spawnedAt = $spawnedAt;
        $this->reachesBaseAt = $reachesBaseAt;
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTask(): Task
    {
        return $this->task;
    }

    public function setTask(Task $task): static
    {
        $this->task = $task;

        return $this;
    }

    public function getSpawnedAt(): \DateTimeImmutable
    {
        return $this->spawnedAt;
    }

    public function getReachesBaseAt(): \DateTimeImmutable
    {
        return $this->reachesBaseAt;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function getResolvedAt(): ?\DateTimeImmutable
    {
        return $this->resolvedAt;
    }

    public function getResolution(): ?TaskInstanceResolution
    {
        return $this->resolution;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function shoot(\DateTimeImmutable $shotAt): static
    {
        $this->completedAt = $shotAt;
        $this->resolvedAt = $shotAt;
        $this->resolution = TaskInstanceResolution::SHOT;

        return $this;
    }

    public function resolveGreenBaseRespawn(\DateTimeImmutable $resolvedAt): static
    {
        $this->resolvedAt = $resolvedAt;
        $this->resolution = TaskInstanceResolution::GREEN_BASE_RESPAWN;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->resolvedAt === null;
    }
}

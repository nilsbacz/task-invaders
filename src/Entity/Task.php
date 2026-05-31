<?php

declare(strict_types=1);

namespace App\Entity;

use App\Board\Domain\BoardRow;
use App\Enum\TaskRiskLevel;
use App\Repository\TaskRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\Table(name: 'task')]
#[ORM\Index(name: 'IDX_TASK_NEXT_SPAWN_AT', columns: ['next_spawn_at'])]
#[ORM\HasLifecycleCallbacks]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line */
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 32)]
    private string $title;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    #[ORM\JoinColumn(name: 'board_row_id', nullable: true)]
    private ?BoardRow $boardRow = null;

    #[ORM\Column(
        type: Types::STRING,
        length: 6,
        enumType: TaskRiskLevel::class,
        columnDefinition: "ENUM('GREEN', 'YELLOW', 'RED') NOT NULL"
    )]
    #[Assert\NotNull]
    private TaskRiskLevel $riskLevel;

    #[ORM\Column]
    #[Assert\NotNull]
    private \DateTimeImmutable $spawnDate;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $nextSpawnAt = null;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual(value: 0)]
    private int $respawnsIn = 0;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual(value: 0)]
    private int $spawnsEvery = 0;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual(value: 0)]
    private int $reachesBaseIn;

    #[ORM\Column]
    private bool $hasShield = false;

    #[ORM\Column]
    private bool $respawnImmediatelyAfterDeath = false;

    #[ORM\Column]
    #[Assert\GreaterThanOrEqual(value: 0)]
    private int $speedFactor = 0;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\ManyToOne(inversedBy: 'tasks')]
    private ?TaskDescription $taskDescription = null;

    #[ORM\ManyToOne]
    private ?Sprite $sprite = null;

    /**
     * @var Collection<int, TaskInstance>
     */
    #[ORM\OneToMany(
        mappedBy: 'task',
        targetEntity: TaskInstance::class,
        cascade: ['persist'],
        orphanRemoval: false
    )]
    #[ORM\OrderBy(['spawnedAt' => 'ASC', 'id' => 'ASC'])]
    private Collection $taskInstances;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->taskInstances = new ArrayCollection();
    }

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

    public function getBoardRow(): ?BoardRow
    {
        return $this->boardRow;
    }

    public function setBoardRow(?BoardRow $boardRow): static
    {
        $this->boardRow = $boardRow;

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
        $this->nextSpawnAt = $spawnDate;

        return $this;
    }

    public function getBaseDate(): \DateTimeImmutable
    {
        return $this->spawnDate->add($this->minutesInterval($this->reachesBaseIn));
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getNextSpawnAt(): ?\DateTimeImmutable
    {
        return $this->nextSpawnAt;
    }

    public function setNextSpawnAt(?\DateTimeImmutable $nextSpawnAt): static
    {
        $this->nextSpawnAt = $nextSpawnAt;

        return $this;
    }

    public function reachesBaseAt(\DateTimeImmutable $spawnedAt): \DateTimeImmutable
    {
        return $spawnedAt->add($this->minutesInterval($this->reachesBaseIn));
    }

    public function scheduleNextSpawnAfterShot(\DateTimeImmutable $shotAt): static
    {
        $nextSpawnAt = $shotAt->add($this->minutesInterval($this->respawnsIn));
        $this->spawnDate = $nextSpawnAt;
        $this->nextSpawnAt = $nextSpawnAt;

        return $this;
    }

    public function scheduleNextInstanceSpawnAfterShot(\DateTimeImmutable $shotAt): static
    {
        $this->nextSpawnAt = $shotAt->add($this->minutesInterval($this->respawnsIn));

        return $this;
    }

    public function complete(\DateTimeImmutable $completedAt): static
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function isCompleted(): bool
    {
        return $this->completedAt !== null;
    }

    public function shouldAppearOnBoard(): bool
    {
        return !$this->isCompleted() || $this->isRespawnImmediatelyAfterDeath();
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

    /**
     * @return Collection<int, TaskInstance>
     */
    public function getTaskInstances(): Collection
    {
        return $this->taskInstances;
    }

    public function addTaskInstance(TaskInstance $taskInstance): static
    {
        if (!$this->taskInstances->contains($taskInstance)) {
            $this->taskInstances->add($taskInstance);
            $taskInstance->setTask($this);
        }

        return $this;
    }

    public function removeTaskInstance(TaskInstance $taskInstance): static
    {
        $this->taskInstances->removeElement($taskInstance);

        return $this;
    }

    public function canBeDeleted(): bool
    {
        return $this->boardRow?->getBoard() === null;
    }

    #[ORM\PreRemove]
    public function assertCanBeDeleted(): void
    {
        if (!$this->canBeDeleted()) {
            throw new \LogicException('Tasks attached to a board cannot be deleted. Mark them as completed instead.');
        }
    }

    private function minutesInterval(int $minutes): \DateInterval
    {
        return new \DateInterval(sprintf('PT%dM', max(0, $minutes)));
    }
}

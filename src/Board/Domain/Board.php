<?php

declare(strict_types=1);

namespace App\Board\Domain;

use App\Board\Infrastructure\Persistence\DoctrineBoardRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DoctrineBoardRepository::class)]
class Board
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line */
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull]
    #[Assert\Length(max: 255)]
    private string $title;

    #[ORM\Column]
    #[Assert\NotNull]
    private bool $isTurretMode = false;

    /**
     * @var Collection<int, BoardRow>
     */
    #[ORM\OneToMany(mappedBy: 'board', targetEntity: BoardRow::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['rowNumber' => 'ASC'])]
    private Collection $boardRows;

    public function __construct()
    {
        $this->boardRows = new ArrayCollection();
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

    public function isTurretMode(): bool
    {
        return $this->isTurretMode;
    }

    public function setIsTurretMode(bool $isTurretMode): static
    {
        $this->isTurretMode = $isTurretMode;

        return $this;
    }

    /**
     * @return Collection<int, BoardRow>
     */
    public function getBoardRows(): Collection
    {
        return $this->boardRows;
    }

    public function addBoardRow(BoardRow $boardRow): static
    {
        if (!$this->boardRows->contains($boardRow)) {
            $this->boardRows->add($boardRow);
            $boardRow->setBoard($this);
        }

        return $this;
    }

    public function removeBoardRow(BoardRow $boardRow): static
    {
        if ($this->boardRows->removeElement($boardRow)) {
            $boardRow->setBoard(null);
        }

        return $this;
    }
}

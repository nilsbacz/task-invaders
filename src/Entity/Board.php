<?php

namespace App\Entity;

use App\Repository\BoardRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BoardRepository::class)]
class Board
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line */
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column]
    private bool $isTurretMode;

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
}

<?php

declare(strict_types=1);

namespace App\Board\Domain;

use App\Board\Infrastructure\Persistence\DoctrineBoardRowRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DoctrineBoardRowRepository::class)]
#[ORM\Table(name: 'board_row')]
class BoardRow
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line */
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    private string $title;

    #[ORM\Column(name: 'sort_order')]
    private int $rowNumber;

    #[ORM\ManyToOne(inversedBy: 'boardRows')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Board $board = null;

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

    public function getRowNumber(): int
    {
        return $this->rowNumber;
    }

    public function setRowNumber(int $rowNumber): static
    {
        $this->rowNumber = $rowNumber;

        return $this;
    }

    public function getBoard(): ?Board
    {
        return $this->board;
    }

    public function setBoard(?Board $board): static
    {
        $this->board = $board;

        return $this;
    }
}

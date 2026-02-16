<?php

namespace App\Entity;

use App\Repository\RowRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RowRepository::class)]
class Row
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line */
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    private string $title;

    #[ORM\Column]
    private int $rowNumber;

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
}

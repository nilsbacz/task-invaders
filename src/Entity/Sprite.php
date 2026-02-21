<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\SpriteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SpriteRepository::class)]
class Sprite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    /** @phpstan-ignore-next-line */
    private ?int $id = null;

    #[ORM\Column(length: 32)]
    private ?string $title = null;

    #[ORM\Column(type: Types::BLOB)]
    private mixed $spriteData = null;

    #[ORM\Column(length: 9)]
    private ?string $color = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }


    public function getSpriteData(): ?string
    {
        if ($this->spriteData === null) {
            return null;
        }

        if (is_resource($this->spriteData)) {
            return (string) stream_get_contents($this->spriteData);
        }

        if (!is_string($this->spriteData)) {
            throw new \LogicException('Sprite data must be a string or stream resource.');
        }

        return $this->spriteData;
    }

    /**
     * @param string|resource $spriteData
     */
    public function setSpriteData(mixed $spriteData): static
    {
        $this->spriteData = $spriteData;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }
}

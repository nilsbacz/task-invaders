<?php

declare(strict_types=1);

namespace App\Board\Application;

final class CreateBoard
{
    private string $title = '';
    private bool $isTurretMode = false;

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

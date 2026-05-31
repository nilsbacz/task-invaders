<?php

declare(strict_types=1);

namespace App\Board\Application;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateBoardRow
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 32)]
    private string $title = '';

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }
}

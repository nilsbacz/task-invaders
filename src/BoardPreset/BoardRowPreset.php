<?php

declare(strict_types=1);

namespace App\BoardPreset;

final readonly class BoardRowPreset
{
    public function __construct(
        public string $key,
        public string $title,
        public int $position,
    ) {
    }
}

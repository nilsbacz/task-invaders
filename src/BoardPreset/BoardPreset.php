<?php

declare(strict_types=1);

namespace App\BoardPreset;

/**
 * @phpstan-type BoardPresetBoardRows list<BoardRowPreset>
 */
final readonly class BoardPreset
{
    /**
     * @param BoardPresetBoardRows $boardRows
     */
    public function __construct(
        public string $key,
        public string $name,
        public int $version,
        public array $boardRows,
    ) {
    }
}

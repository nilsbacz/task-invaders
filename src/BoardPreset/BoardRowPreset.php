<?php

declare(strict_types=1);

namespace App\BoardPreset;

/**
 * @phpstan-type BoardRowPresetTasks list<BoardTaskPreset>
 */
final readonly class BoardRowPreset
{
    /**
     * @param BoardRowPresetTasks $tasks
     */
    public function __construct(
        public string $title,
        public int $position,
        public array $tasks,
    ) {
    }
}

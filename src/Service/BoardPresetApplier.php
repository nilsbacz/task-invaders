<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Board;
use App\Entity\BoardRow;

final readonly class BoardPresetApplier
{
    public function __construct(
        private BoardPresetLoader $boardPresetLoader,
    ) {
    }

    public function applyDefaultPreset(Board $board): void
    {
        $preset = $this->boardPresetLoader->load('default');

        foreach ($preset->boardRows as $boardRowPreset) {
            $boardRow = new BoardRow();
            $boardRow->setTitle($boardRowPreset->title);
            $boardRow->setRowNumber($boardRowPreset->position);
            $board->addBoardRow($boardRow);
        }
    }
}

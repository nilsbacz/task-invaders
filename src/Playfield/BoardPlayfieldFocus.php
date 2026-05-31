<?php

declare(strict_types=1);

namespace App\Playfield;

final readonly class BoardPlayfieldFocus
{
    public function __construct(
        public ?BoardPlayfieldTaskInstance $taskInstance,
    ) {
    }

    public function hasFocus(): bool
    {
        return $this->taskInstance !== null;
    }
}

<?php

declare(strict_types=1);

namespace App\Playfield;

final readonly class BoardPlayfield
{
    /**
     * @param list<BoardPlayfieldLane> $lanes
     */
    public function __construct(
        public ?int $boardId,
        public string $title,
        public \DateTimeImmutable $projectedAt,
        public array $lanes,
        public BoardPlayfieldFocus $focus,
    ) {
    }
}

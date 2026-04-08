<?php

declare(strict_types=1);

namespace App\Tests\BoardPreset;

use App\BoardPreset\BoardPreset;
use App\BoardPreset\BoardRowPreset;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BoardPreset::class)]
#[UsesClass(BoardRowPreset::class)]
final class BoardPresetTest extends TestCase
{
    #[Test]
    public function itExposesItsConstructorValues(): void
    {

        $boardRowPreset = new BoardRowPreset('sports', 1, []);


        $preset = new BoardPreset('default', 'Default Board', 1, [$boardRowPreset]);


        self::assertSame('default', $preset->key);
        self::assertSame('Default Board', $preset->name);
        self::assertSame(1, $preset->version);
        self::assertSame([$boardRowPreset], $preset->boardRows);
    }
}

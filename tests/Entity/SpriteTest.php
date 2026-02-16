<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Sprite;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Sprite::class)]
final class SpriteTest extends TestCase
{
    #[Test]
    public function it_exposes_getters_and_setters_with_string_data(): void
    {
        $sprite = new Sprite();

        self::assertNull($sprite->getId());
        self::assertNull($sprite->getSpriteData());

        self::assertSame($sprite, $sprite->setTitle('Hero'));
        self::assertSame($sprite, $sprite->setColor('#ff00ff'));
        self::assertSame($sprite, $sprite->setSpriteData('raw-bytes'));

        self::assertSame('Hero', $sprite->getTitle());
        self::assertSame('#ff00ff', $sprite->getColor());
        self::assertSame('raw-bytes', $sprite->getSpriteData());
    }

    #[Test]
    public function it_reads_sprite_data_from_streams(): void
    {
        $sprite = new Sprite();

        $handle = fopen('php://memory', 'r+');
        self::assertIsResource($handle);

        fwrite($handle, 'stream-bytes');
        rewind($handle);

        self::assertSame($sprite, $sprite->setSpriteData($handle));

        self::assertSame('stream-bytes', $sprite->getSpriteData());

        fclose($handle);
    }
}

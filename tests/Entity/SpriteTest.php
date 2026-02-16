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
    public function itExposesGettersAndSettersWithStringData(): void
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
    public function itReadsSpriteDataFromStreams(): void
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

    #[Test]
    public function itRejectsInvalidSpriteDataType(): void
    {
        $sprite = new Sprite();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Sprite data must be a string or stream resource.');

        /** @phpstan-ignore-next-line */
        $sprite->setSpriteData(123);
        $sprite->getSpriteData();
    }
}

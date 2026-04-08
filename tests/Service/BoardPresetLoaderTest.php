<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\BoardPreset\BoardPreset;
use App\BoardPreset\BoardRowPreset;
use App\BoardPreset\BoardTaskPreset;
use App\Service\BoardPresetLoader;
use App\Tests\Support\BoardPresetFixture;
use App\Tests\Support\BoardPresetSerializerFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[CoversClass(BoardPresetLoader::class)]
#[UsesClass(BoardPreset::class)]
#[UsesClass(BoardRowPreset::class)]
#[UsesClass(BoardTaskPreset::class)]
final class BoardPresetLoaderTest extends TestCase
{
    #[Test]
    public function itLoadsBoardPresetFromYaml(): void
    {

        $directory = $this->createPresetDirectoryFixture(<<<'YAML'
name: Demo Preset
version: 2
boardRows:
  - title: sports
    position: 1
    tasks:
      - title: workout
        respawnsIn: 10
        spawnsEvery: 20
        reachesBaseIn: 30
        riskLevel: YELLOW
  - title: projects
    position: 2
    tasks: []
YAML);
        $loader = $this->createLoaderFixture($directory);


        $preset = $loader->load('default');


        self::assertSame('default', $preset->key);
        self::assertSame('Demo Preset', $preset->name);
        self::assertSame(2, $preset->version);
        self::assertCount(2, $preset->boardRows);
        self::assertSame('sports', $preset->boardRows[0]->title);
        self::assertSame(1, $preset->boardRows[0]->position);
        self::assertCount(1, $preset->boardRows[0]->tasks);
        self::assertSame('workout', $preset->boardRows[0]->tasks[0]->title);
        self::assertFalse($preset->boardRows[0]->tasks[0]->hasShield);
        self::assertFalse($preset->boardRows[0]->tasks[0]->respawnImmediatelyAfterDeath);
        self::assertSame(0, $preset->boardRows[0]->tasks[0]->speedFactor);
    }

    #[Test]
    public function itRejectsMissingPresetFiles(): void
    {

        $loader = $this->createLoaderFixture(sys_get_temp_dir());
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Board preset "missing" was not found.');


        $loader->load('missing');
    }

    #[Test]
    public function itRejectsInvalidPresetPayloads(): void
    {

        $directory = $this->createPresetDirectoryFixture('not-an-array');
        $loader = $this->createLoaderFixture($directory);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Board preset "default" is invalid.');


        $loader->load('default');
    }

    #[Test]
    public function itRejectsMalformedPresetDefinitions(): void
    {

        $directory = $this->createPresetDirectoryFixture(<<<'YAML'
name: Demo Preset
version: 1
boardRows:
  - title: sports
    position: 1
    tasks:
      - title: workout
        respawnsIn: 10
        spawnsEvery: 20
        reachesBaseIn: 30
        riskLevel: BLUE
YAML);
        $loader = $this->createLoaderFixture($directory);
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Board preset "default" is invalid:');


        $loader->load('default');
    }

    #[Test]
    public function itRejectsMissingBoardRowsList(): void
    {

        $loader = $this->createLoaderFixture($this->createPresetDirectoryFixture(<<<'YAML'
name: Demo Preset
version: 1
YAML));
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Board preset "default" is invalid.');


        $loader->load('default');
    }

    #[Test]
    public function itRejectsInvalidBoardRowEntries(): void
    {

        $loader = $this->createLoaderFixture($this->createPresetDirectoryFixture(<<<'YAML'
name: Demo Preset
version: 1
boardRows:
  - invalid
YAML));
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Board preset "default" is invalid.');


        $loader->load('default');
    }

    #[Test]
    public function itRejectsMissingTasksList(): void
    {

        $loader = $this->createLoaderFixture($this->createPresetDirectoryFixture(<<<'YAML'
name: Demo Preset
version: 1
boardRows:
  - title: sports
    position: 1
YAML));
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Board preset "default" is invalid.');


        $loader->load('default');
    }

    #[Test]
    public function itRejectsInvalidTaskEntries(): void
    {

        $loader = $this->createLoaderFixture($this->createPresetDirectoryFixture(<<<'YAML'
name: Demo Preset
version: 1
boardRows:
  - title: sports
    position: 1
    tasks:
      - invalid
YAML));
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Board preset "default" is invalid.');


        $loader->load('default');
    }

    #[Test]
    public function itRejectsUnexpectedSerializerResults(): void
    {

        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer
            ->expects(self::once())
            ->method('denormalize')
            ->willReturn(new \stdClass());
        $loader = new BoardPresetLoader(
            $denormalizer,
            $this->createPresetDirectoryFixture(<<<'YAML'
name: Demo Preset
version: 1
boardRows: []
YAML)
        );
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Board preset "default" is invalid.');


        $loader->load('default');
    }

    #[Test]
    public function itRejectsUnexpectedBoardRowResults(): void
    {

        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer
            ->expects(self::once())
            ->method('denormalize')
            ->willReturn(new \stdClass());
        $loader = new BoardPresetLoader(
            $denormalizer,
            $this->createPresetDirectoryFixture(<<<'YAML'
name: Demo Preset
version: 1
boardRows:
  - title: sports
    position: 1
    tasks: []
YAML)
        );
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Board preset "default" is invalid.');


        $loader->load('default');
    }

    #[Test]
    public function itRejectsUnexpectedTaskResults(): void
    {

        $denormalizer = $this->createMock(DenormalizerInterface::class);
        $denormalizer
            ->expects(self::once())
            ->method('denormalize')
            ->willReturn(new \stdClass());
        $loader = new BoardPresetLoader(
            $denormalizer,
            $this->createPresetDirectoryFixture(<<<'YAML'
name: Demo Preset
version: 1
boardRows:
  - title: sports
    position: 1
    tasks:
      - title: workout
        respawnsIn: 10
        spawnsEvery: 20
        reachesBaseIn: 30
        riskLevel: GREEN
YAML)
        );
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Board preset "default" is invalid.');


        $loader->load('default');
    }

    private function createPresetDirectoryFixture(string $contents): string
    {
        return BoardPresetFixture::createDirectory($contents, 'board-preset-loader');
    }

    private function createLoaderFixture(string $directory): BoardPresetLoader
    {
        return new BoardPresetLoader(
            BoardPresetSerializerFactory::create(),
            $directory,
        );
    }
}

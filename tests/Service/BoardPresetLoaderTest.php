<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\BoardPreset\BoardPreset;
use App\BoardPreset\BoardRowPreset;
use App\Service\BoardPresetLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(BoardPreset::class)]
#[CoversClass(BoardRowPreset::class)]
#[CoversClass(BoardPresetLoader::class)]
final class BoardPresetLoaderTest extends TestCase
{
    #[Test]
    public function itLoadsBoardPresetFromYaml(): void
    {
        $directory = $this->createPresetDirectory(<<<'YAML'
key: default
name: Demo Preset
version: 2
boardRows:
  - key: sports
    title: sports
    position: 1
  - key: projects
    title: projects
    position: 2
YAML);

        $loader = new BoardPresetLoader($directory);

        $preset = $loader->load('default');

        self::assertSame('default', $preset->key);
        self::assertSame('Demo Preset', $preset->name);
        self::assertSame(2, $preset->version);
        self::assertCount(2, $preset->boardRows);
        self::assertSame('sports', $preset->boardRows[0]->key);
        self::assertSame('sports', $preset->boardRows[0]->title);
        self::assertSame(1, $preset->boardRows[0]->position);
    }

    #[Test]
    public function itRejectsMissingPresetFiles(): void
    {
        $loader = new BoardPresetLoader(sys_get_temp_dir());

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Board preset "missing" was not found.');

        $loader->load('missing');
    }

    #[Test]
    public function itRejectsInvalidPresetPayloads(): void
    {
        $directory = $this->createPresetDirectory('not-an-array');
        $loader = new BoardPresetLoader($directory);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Board preset "default" is invalid.');

        $loader->load('default');
    }

    #[Test]
    #[DataProvider('invalidPresetProvider')]
    public function itRejectsMalformedPresetDefinitions(string $contents, string $expectedMessage): void
    {
        $directory = $this->createPresetDirectory($contents);
        $loader = new BoardPresetLoader($directory);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage($expectedMessage);

        $loader->load('default');
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function invalidPresetProvider(): array
    {
        return [
                'missing key'               => [
                                                <<<'YAML'
name: Demo Preset
version: 1
boardRows: []
YAML,
                                                'Board preset "default" field "key" must be a non-empty string.',
                                               ],
                'invalid version'           => [
                                                <<<'YAML'
key: default
name: Demo Preset
version: wrong
boardRows: []
YAML,
                                                'Board preset "default" field "version" must be an integer.',
                                               ],
                'missing boardRows'         => [
                                                <<<'YAML'
key: default
name: Demo Preset
version: 1
YAML,
                                                'Board preset "default" must define a boardRows list.',
                                               ],
                'invalid boardRow entry'    => [
                                                <<<'YAML'
key: default
name: Demo Preset
version: 1
boardRows:
  - invalid
YAML,
                                                'Board preset "default" boardRow 0 is invalid.',
                                               ],
                'invalid boardRow title'    => [
                                                <<<'YAML'
key: default
name: Demo Preset
version: 1
boardRows:
  - key: sports
    title: ""
    position: 1
YAML,
                                                'Board preset "default" field "title" must be a non-empty string.',
                                               ],
                'invalid boardRow position' => [
                                                <<<'YAML'
key: default
name: Demo Preset
version: 1
boardRows:
  - key: sports
    title: sports
    position: wrong
YAML,
                                                'Board preset "default" field "position" must be an integer.',
                                               ],
               ];
    }

    private function createPresetDirectory(string $contents): string
    {
        $directory = sys_get_temp_dir() . '/board-preset-loader-' . bin2hex(random_bytes(8));
        self::assertTrue(mkdir($directory, 0777, true));
        self::assertSame(strlen($contents), file_put_contents($directory . '/default.yaml', $contents));

        return $directory;
    }
}

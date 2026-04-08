<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Service\BoardPresetLoader;

final readonly class BoardPresetFixture
{
    public static function createDefaultLoader(string $prefix): BoardPresetLoader
    {
        return new BoardPresetLoader(
            BoardPresetSerializerFactory::create(),
            self::createDirectory(self::defaultYaml(), $prefix),
        );
    }

    public static function createDirectory(string $contents, string $prefix): string
    {
        $directory = sys_get_temp_dir() . '/' . $prefix . '-' . bin2hex(random_bytes(8));

        if (!mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new \RuntimeException(sprintf('Could not create fixture directory "%s".', $directory));
        }

        file_put_contents($directory . '/default.yaml', $contents);

        return $directory;
    }

    public static function defaultYaml(): string
    {
        return <<<'YAML'
name: Default Board
version: 1
boardRows:
  - title: sports
    position: 1
    tasks:
      - title: workout
        respawnsIn: 1440
        spawnsEvery: 2880
        reachesBaseIn: 1440
        riskLevel: YELLOW
      - title: running
        respawnsIn: 2880
        spawnsEvery: 2880
        reachesBaseIn: 2880
        riskLevel: GREEN
  - title: household
    position: 2
    tasks:
      - title: Mop the floor
        respawnsIn: 21600
        spawnsEvery: 21600
        reachesBaseIn: 43200
        riskLevel: GREEN
  - title: projects
    position: 3
    tasks:
      - title: Work on task Invaders
        respawnsIn: 20160
        spawnsEvery: 20160
        reachesBaseIn: 20160
        riskLevel: GREEN
YAML;
    }
}

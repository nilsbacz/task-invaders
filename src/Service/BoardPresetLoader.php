<?php

declare(strict_types=1);

namespace App\Service;

use App\BoardPreset\BoardPreset;
use App\BoardPreset\BoardRowPreset;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Yaml\Yaml;

final readonly class BoardPresetLoader
{
    public function __construct(
        #[Autowire('%kernel.project_dir%/config/board_presets')]
        private string $presetDirectory,
    ) {
    }

    public function load(string $presetKey): BoardPreset
    {
        $path = sprintf('%s/%s.yaml', rtrim($this->presetDirectory, '/'), $presetKey);
        if (!is_file($path)) {
            throw new \RuntimeException(sprintf('Board preset "%s" was not found.', $presetKey));
        }

        $data = Yaml::parseFile($path);
        if (!is_array($data)) {
            throw new \RuntimeException(sprintf('Board preset "%s" is invalid.', $presetKey));
        }
        /** @var array<string, mixed> $data */

        return new BoardPreset(
            key: $this->requireString($data, 'key', $presetKey),
            name: $this->requireString($data, 'name', $presetKey),
            version: $this->requireInt($data, 'version', $presetKey),
            boardRows: $this->hydrateBoardRows($data['boardRows'] ?? null, $presetKey),
        );
    }

    /**
     * @param mixed $boardRows
     * @return list<BoardRowPreset>
     */
    private function hydrateBoardRows(mixed $boardRows, string $presetKey): array
    {
        if (!is_array($boardRows)) {
            throw new \RuntimeException(sprintf('Board preset "%s" must define a boardRows list.', $presetKey));
        }

        $result = [];
        foreach ($boardRows as $index => $boardRow) {
            if (!is_array($boardRow)) {
                throw new \RuntimeException(sprintf('Board preset "%s" boardRow %d is invalid.', $presetKey, $index));
            }
            /** @var array<string, mixed> $boardRow */

            $result[] = new BoardRowPreset(
                key: $this->requireString($boardRow, 'key', $presetKey),
                title: $this->requireString($boardRow, 'title', $presetKey),
                position: $this->requireInt($boardRow, 'position', $presetKey),
            );
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requireString(array $data, string $field, string $presetKey): string
    {
        $value = $data[$field] ?? null;
        if (!is_string($value) || $value === '') {
            throw new \RuntimeException(sprintf(
                'Board preset "%s" field "%s" must be a non-empty string.',
                $presetKey,
                $field
            ));
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function requireInt(array $data, string $field, string $presetKey): int
    {
        $value = $data[$field] ?? null;
        if (!is_int($value)) {
            throw new \RuntimeException(sprintf(
                'Board preset "%s" field "%s" must be an integer.',
                $presetKey,
                $field
            ));
        }

        return $value;
    }
}

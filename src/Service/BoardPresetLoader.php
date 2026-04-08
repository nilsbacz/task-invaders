<?php

declare(strict_types=1);

namespace App\Service;

use App\BoardPreset\BoardPreset;
use App\BoardPreset\BoardRowPreset;
use App\BoardPreset\BoardTaskPreset;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Yaml\Yaml;

final readonly class BoardPresetLoader
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
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
        $data['key'] = $presetKey;

        try {
            $data['boardRows'] = $this->denormalizeBoardRows($presetKey, $data['boardRows'] ?? null);
            $preset = $this->denormalize($data, BoardPreset::class);
        } catch (ExceptionInterface | \TypeError | \ValueError $exception) {
            throw new \RuntimeException(sprintf(
                'Board preset "%s" is invalid: %s',
                $presetKey,
                $exception->getMessage()
            ), previous: $exception);
        }

        if (!$preset instanceof BoardPreset) {
            throw new \RuntimeException(sprintf('Board preset "%s" is invalid.', $presetKey));
        }

        return $preset;
    }

    /**
     * @return list<BoardRowPreset>
     * @throws ExceptionInterface
     */
    private function denormalizeBoardRows(string $presetKey, mixed $boardRows): array
    {
        if (!is_array($boardRows)) {
            throw new \RuntimeException(sprintf('Board preset "%s" is invalid.', $presetKey));
        }

        $boardRowPresets = [];

        foreach ($boardRows as $boardRowData) {
            if (!is_array($boardRowData)) {
                throw new \RuntimeException(sprintf('Board preset "%s" is invalid.', $presetKey));
            }

            $boardRowData['tasks'] = $this->denormalizeTasks($presetKey, $boardRowData['tasks'] ?? null);
            $boardRowPreset = $this->denormalize($boardRowData, BoardRowPreset::class);

            if (!$boardRowPreset instanceof BoardRowPreset) {
                throw new \RuntimeException(sprintf('Board preset "%s" is invalid.', $presetKey));
            }

            $boardRowPresets[] = $boardRowPreset;
        }

        return $boardRowPresets;
    }

    /**
     * @return list<BoardTaskPreset>
     * @throws ExceptionInterface
     */
    private function denormalizeTasks(string $presetKey, mixed $tasks): array
    {
        if (!is_array($tasks)) {
            throw new \RuntimeException(sprintf('Board preset "%s" is invalid.', $presetKey));
        }

        $taskPresets = [];

        foreach ($tasks as $taskData) {
            if (!is_array($taskData)) {
                throw new \RuntimeException(sprintf('Board preset "%s" is invalid.', $presetKey));
            }

            $taskPreset = $this->denormalize($taskData, BoardTaskPreset::class);

            if (!$taskPreset instanceof BoardTaskPreset) {
                throw new \RuntimeException(sprintf('Board preset "%s" is invalid.', $presetKey));
            }

            $taskPresets[] = $taskPreset;
        }

        return $taskPresets;
    }

    /**
     * @param array<int|string, mixed> $data
     * @throws ExceptionInterface
     */
    private function denormalize(array $data, string $type): mixed
    {
        return $this->denormalizer->denormalize($data, $type);
    }
}

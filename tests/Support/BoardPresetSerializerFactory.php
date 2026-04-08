<?php

declare(strict_types=1);

namespace App\Tests\Support;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final readonly class BoardPresetSerializerFactory
{
    public static function create(): DenormalizerInterface
    {
        return new Serializer([
                               new BackedEnumNormalizer(),
                               new ObjectNormalizer(),
                              ]);
    }
}

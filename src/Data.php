<?php

declare(strict_types=1);

namespace Rkt\MageData;

use Rkt\MageData\Trait\UseValidation;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

abstract class Data
{
    use UseValidation;

    public function toArray(): array
    {
        $serializer = new Serializer([new ObjectNormalizer()]);

        return $serializer->normalize($this);
    }

    /**
     * Creates an instance from an array.
     */
    public static function from(array $data): static
    {
        $serializer = new Serializer([new ObjectNormalizer()]);

        return $serializer->denormalize($data, static::class);
    }
}

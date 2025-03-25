<?php

declare(strict_types=1);

namespace Rkt\MageData;

use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

abstract class Data
{
    public function toArray(): array
    {
        $serializer = new Serializer([new ObjectNormalizer()]);

        return $serializer->normalize($this);
    }

    /**
     * Creates an instance from an array.
     */
    public static function from(array $data): self
    {
        $serializer = new Serializer([new ObjectNormalizer()]);

        return $serializer->denormalize($data, static::class);
    }
}

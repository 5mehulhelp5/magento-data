<?php

declare(strict_types=1);

namespace Rkt\MageData\Traits;

use Rkt\MageData\Model\DataObjectFactory;

trait CreatesSelfInstance
{
    /**
     * Creates an instance from an array.
     */
    public static function create(array $data): static
    {
        return DataObjectFactory::create(static::class, $data);
    }

    public static function from(array $data): static
    {
        return self::create($data);
    }
}

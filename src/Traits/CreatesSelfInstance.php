<?php

declare(strict_types=1);

namespace Rkt\MageData\Traits;

use Magento\Framework\Model\AbstractModel;
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

    public static function fromDefaults(array $extraData = []): static
    {
        return self::create(static::withDefaultValues($extraData));
    }

    public static function fromModel(AbstractModel $model, array $extraData = []): static
    {
        return self::create(static::withDefaultValues([
            ...array_filter($model->getData(), fn ($value) => $value !== null),
            ...$extraData,
        ]));
    }
}

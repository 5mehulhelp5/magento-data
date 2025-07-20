<?php

declare(strict_types=1);

namespace Rkt\MageData\Traits;

trait AddDefaultValues
{
    public static function defaultValues(): array
    {
        return [];
    }

    public static function withDefaultValues(array $extraData = []): array
    {
        $defaults = static::defaultValues();

        return static::mergeRecursiveDefaults($defaults, $extraData);
    }

    protected static function mergeRecursiveDefaults(array $defaults, array $overrides): array
    {
        foreach ($overrides as $key => $value) {
            if (isset($defaults[$key]) && is_array($defaults[$key]) && is_array($value)) {
                $defaults[$key] = static::mergeRecursiveDefaults($defaults[$key], $value);
            } else {
                $defaults[$key] = $value;
            }
        }

        return $defaults;
    }
}

<?php

declare(strict_types=1);

namespace Rkt\MageData\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;

class DataObjectFactory
{
    private static ?ObjectManagerInterface $om = null;

    /**
     * Can be used in tests to override the default ObjectManager.
     */
    public static function init(ObjectManagerInterface $om): void
    {
        self::$om = $om;
    }

    /**
     * Reset ObjectManager (e.g. between test cases).
     */
    public static function reset(): void
    {
        self::$om = null;
    }

    /**
     * Resolve the ObjectManager (internal).
     */
    private static function getObjectManager(): ObjectManagerInterface
    {
        if (!self::$om) {
            self::$om = ObjectManager::getInstance();
        }

        return self::$om;
    }

    /**
     * Create a data object (or any object) via Magento's DI.
     */
    public static function create(string $class, array $data = []): object
    {
        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return new $class(...[]);
        }

        $args = [];

        foreach ($constructor->getParameters() as $param) {
            $name = $param->getName();
            $type = $param->getType();

            $value = $data[$name] ?? null;

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin() && $value !== null) {
                $typeName = $type->getName();

                // If value is array and target is a Data object, recurse
                if (is_array($value) && is_subclass_of($typeName, \Rkt\MageData\Data::class)) {
                    $value = self::create($typeName, $value);
                }
            }

            $args[] = $value;
        }

        return $reflection->newInstanceArgs($args);
    }

    /**
     * Get a shared service instance (e.g. event manager).
     */
    public static function get(string $service): object
    {
        return self::getObjectManager()->get($service);
    }
}

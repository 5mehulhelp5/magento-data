<?php

declare(strict_types=1);

namespace Rkt\MageData\Model;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\ObjectManagerInterface;

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
        return self::getObjectManager()->create($class, $data);
    }

    /**
     * Get a shared service instance (e.g. event manager).
     */
    public static function get(string $service): object
    {
        return self::getObjectManager()->get($service);
    }
}

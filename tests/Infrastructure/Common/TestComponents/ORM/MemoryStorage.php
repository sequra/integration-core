<?php

namespace SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM;

use SeQura\Core\Infrastructure\ORM\Entity;

/**
 * Class MemoryStorage.
 *
 * @package SeQura\Core\Tests\Infrastructure\Common\TestComponents\ORM\Entity
 */
class MemoryStorage
{
    private static $incrementId = 1;

    /**
     * @var Entity[]
     */
    public static $storage = array();

    /**
     * @return int
     */
    public static function generateId()
    {
        return static::$incrementId++;
    }

    /**
     * Empties storage.
     */
    public static function reset()
    {
        static::$incrementId = 1;
        static::$storage = array();
    }
}

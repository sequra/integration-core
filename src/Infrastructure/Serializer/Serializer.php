<?php

namespace SeQura\Core\Infrastructure\Serializer;

use SeQura\Core\Infrastructure\ServiceRegister;

/**
 * Class Serializer
 *
 * @package SeQura\Core\Infrastructure\Serializer
 */
abstract class Serializer
{
    /**
     * Class name identifier.
     *
     * @var string CLASS_NAME
     */
    const CLASS_NAME = __CLASS__;

    /**
     * Serializes data.
     *
     * @param mixed $data Data to be serialized.
     *
     * @return string String representation of the serialized data.
     */
    public static function serialize($data)
    {
        /**
         * @var Serializer $instace
        */
        $instance = ServiceRegister::getService(self::CLASS_NAME);

        return $instance->doSerialize($data);
    }

    /**
     * Unserializes data.
     *
     * @param string $serialized Serialized data.
     *
     * @return mixed Unserialized data.
     */
    public static function unserialize($serialized)
    {
        /**
         * @var Serializer $instace
        */
        $instance = ServiceRegister::getService(self::CLASS_NAME);

        return $instance->doUnserialize($serialized);
    }

    /**
     * Serializes data.
     *
     * @param mixed $data Data to be serialized.
     *
     * @return string String representation of the serialized data.
     */
    abstract protected function doSerialize($data);

    /**
     * Unserializes data.
     *
     * @param string $serialized Serialized data.
     *
     * @return mixed Unserialized data.
     */
    abstract protected function doUnserialize($serialized);
}

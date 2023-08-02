<?php

namespace SeQura\Core\Infrastructure\Serializer\Interfaces;

/**
 * Interface Serializable
 *
 * @package SeQura\Core\Infrastructure\Serializer\Interfaces
 */
interface Serializable extends \Serializable
{
    /**
     * Transforms array into an serializable object,
     *
     * @param array $array Data that is used to instantiate serializable object.
     *
     * @return Serializable
     *      Instance of serialized object.
     */
    public static function fromArray(array $array);

    /**
     * Transforms serializable object into an array.
     *
     * @return array Array representation of a serializable object.
     */
    public function toArray();

    /**
     * @return array
     */
    public function __serialize();

    /**
     * @param array $data
     *
     * @return void
     */
    public function __unserialize($data);
}

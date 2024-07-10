<?php

namespace SeQura\Core\Infrastructure\Data;

use RuntimeException;

/**
 * Class DataTransferObject
 *
 * @package SeQura\Core\Infrastructure\Data
 */
abstract class DataTransferObject
{
    /**
     * Creates instance of the data transfer object from an array.
     *
     * @param array $data Raw data used for the object instantiation.
     *
     * @return DataTransferObject An instance of the data transfer object.
     *
     * @noinspection PhpDocSignatureInspection
     */
    public static function fromArray(array $data)
    {
        throw new RuntimeException('Method from array not implemented');
    }

    /**
     * Creates list of DTOs from a batch of raw data.
     *
     * @param array $batch Batch of raw data.
     *
     * @return array List of DTO instances.
     */
    public static function fromBatch(array $batch)
    {
        $result = array();

        foreach ($batch as $index => $item) {
            $result[$index] = static::fromArray($item);
        }

        return $result;
    }

    /**
     * Transforms batch of data transfer objects to array.
     *
     * @param static[] $batch Batch of data transfer objects.
     *
     * @return array Transformed data transfer objects batch.
     */
    public static function toBatchArray(array $batch)
    {
        $result = array();

        foreach ($batch as $index => $item) {
            $result[$index] = $item->toArray();
        }

        return $result;
    }

    /**
     * Transforms data transfer object to array.
     *
     * @return array Array representation of data transfer object.
     */
    abstract public function toArray();

    /**
     * Retrieves value from raw data.
     *
     * @param array $rawData Raw DTO data.
     * @param string $key Data key.
     * @param mixed $default Default value.
     *
     * @return mixed
     */
    protected static function getDataValue(array $rawData, string $key, $default = '')
    {
        return isset($rawData[$key]) ? $rawData[$key] : $default;
    }
}

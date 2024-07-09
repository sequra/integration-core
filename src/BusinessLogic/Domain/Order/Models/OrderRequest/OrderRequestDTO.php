<?php

namespace SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest;

use SeQura\Core\Infrastructure\Data\DataTransferObject;

/**
 * Class OrderRequestDTO
 *
 * @package SeQura\Core\BusinessLogic\Domain\Order\Models\OrderRequest
 */
abstract class OrderRequestDTO extends DataTransferObject
{
    /**
     * Returns an array of all class properties where array keys are equal to property names but in snake case,
     * excluding properties with null values.
     *
     * @param array $properties An array of key value pairs representing property name and property value.
     *
     * @return array
     */
    public function transformPropertiesToAnArray(array $properties): array
    {
        $snakeCaseProperties = [];

        foreach ($properties as $propertyName => $propertyValue) {
            if ($propertyValue !== null) {
                $snakeCasePropertyName = strtolower(preg_replace(
                    '/(?<!^)[A-Z]/',
                    '_$0',
                    lcfirst(preg_replace('/\d+/', '_$0', $propertyName))
                ));

                if (is_array($propertyValue)) {
                    $snakeCaseProperties = $this->handleArrayProperty(
                        $snakeCaseProperties,
                        $snakeCasePropertyName,
                        $propertyValue
                    );

                    continue;
                }

                is_object($propertyValue) && method_exists($propertyValue, 'toArray') ?
                    $snakeCaseProperties[$snakeCasePropertyName] = $propertyValue->toArray() :
                    $snakeCaseProperties[$snakeCasePropertyName] = $propertyValue;
            }
        }

        return $snakeCaseProperties;
    }

    /**
     * Handles case when property is of type array.
     *
     * @param array $arrayData
     * @param string $name
     * @param array $value
     *
     * @return array
     */
    protected function handleArrayProperty(array $arrayData, string $name, array $value): array
    {
        $arrayData[$name] = [];
        foreach ($value as $key => $item) {
            is_object($item) && method_exists($item, 'toArray') ?
                $arrayData[$name][] = $item->toArray() :
                $arrayData[$name][$key] = $item;
        }

        return $arrayData;
    }
}

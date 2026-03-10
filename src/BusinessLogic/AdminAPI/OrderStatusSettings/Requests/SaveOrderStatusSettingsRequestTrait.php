<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Requests;

use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Exceptions\EmptyOrderStatusMappingParameterException;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Exceptions\InvalidSeQuraOrderStatusException;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Models\OrderStatusMapping;

/**
 * Trait SaveOrderStatusSettingsRequestTrait
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Requests
 */
trait SaveOrderStatusSettingsRequestTrait
{
    /**
     * @var string[]
     */
    protected $orderStatusMappings;

    /**
     * @param string[] $orderStatusMappings
     */
    public function __construct(array $orderStatusMappings)
    {
        $this->orderStatusMappings = $orderStatusMappings;
    }

    /**
     * Transforms the request array to an array of OrderStatusMappings.
     *
     * @return OrderStatusMapping[]
     *
     * @throws EmptyOrderStatusMappingParameterException
     * @throws InvalidSeQuraOrderStatusException
     */
    public function transformToDomainModel(): array
    {
        $mappings = [];
        foreach ($this->orderStatusMappings as $orderStatusMapping) {
            $mappings[] = new OrderStatusMapping(
                $orderStatusMapping['sequraStatus'] ?? '',
                $orderStatusMapping['shopStatus'] ?? ''
            );
        }

        return $mappings;
    }
}

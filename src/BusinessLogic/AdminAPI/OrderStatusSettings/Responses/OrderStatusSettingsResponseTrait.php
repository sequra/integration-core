<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Responses;

use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Models\OrderStatusMapping;

/**
 * Trait OrderStatusSettingsResponseTrait
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Responses
 */
trait OrderStatusSettingsResponseTrait
{
    /**
     * @var OrderStatusMapping[]
     */
    protected $orderStatusMappings;

    /**
     * @param OrderStatusMapping[] $orderStatusMappings
     */
    public function __construct(array $orderStatusMappings)
    {
        $this->orderStatusMappings = $orderStatusMappings;
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        if (empty($this->orderStatusMappings)) {
            return [];
        }

        $mappings = [];
        foreach ($this->orderStatusMappings as $mapping) {
            $mappings[] = $mapping->toArray();
        }

        return $mappings;
    }
}

<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Models\OrderStatusMapping;

/**
 * Class OrderStatusSettingsResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Responses
 */
class OrderStatusSettingsResponse extends Response
{
    /**
     * @var OrderStatusMapping[]
     */
    protected $orderStatusMappings;

    /**
     * @param OrderStatusMapping[]|null $orderStatusMappings
     */
    public function __construct(?array $orderStatusMappings)
    {
        $this->orderStatusMappings = $orderStatusMappings;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        if (!$this->orderStatusMappings) {
            return [];
        }

        $mappings = [];
        foreach ($this->orderStatusMappings as $mapping) {
            $mappings[] = [
                'sequraStatus' => $mapping->getSequraStatus(),
                'shopStatus' => $mapping->getShopStatus()
            ];
        }

        return $mappings;
    }
}

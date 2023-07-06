<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Models\OrderStatusSettings;

/**
 * Class OrderStatusSettingsResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Responses
 */
class OrderStatusSettingsResponse extends Response
{
    /**
     * @var OrderStatusSettings
     */
    private $orderStatusSettings;

    /**
     * @param OrderStatusSettings|null $orderStatusSettings
     */
    public function __construct(?OrderStatusSettings $orderStatusSettings)
    {
        $this->orderStatusSettings = $orderStatusSettings;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $mappings = [];
        foreach ($this->orderStatusSettings->getOrderStatusMappings() as $mapping) {
            $mappings[] = [
                'sequraStatus' => $mapping->getSequraStatus(),
                'shopStatus' => $mapping->getShopStatus()
            ];
        }

        return [
            'orderStatusMappings' => $mappings,
            'informCancellations' => $this->orderStatusSettings->isInformCancellation()
        ];
    }
}

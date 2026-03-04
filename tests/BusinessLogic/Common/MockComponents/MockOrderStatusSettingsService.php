<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Models\OrderStatusMapping;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Services\OrderStatusSettingsService;

/**
 * Class MockOrderStatusSettingsService.
 *
 * @package Common\MockComponents
 */
class MockOrderStatusSettingsService extends OrderStatusSettingsService
{
    /**
     * @var OrderStatusMapping[] $orderStatusSettings
     */
    private $orderStatusSettings = [];

    /**
     * @return OrderStatusMapping[]
     */
    public function getOrderStatusSettings(): array
    {
        return $this->orderStatusSettings;
    }

    /**
     * @param OrderStatusMapping[] $orderStatusSettings
     *
     * @return void
     */
    public function setMockOrderStatusSettings(array $orderStatusSettings): void
    {
        $this->orderStatusSettings = $orderStatusSettings;
    }
}

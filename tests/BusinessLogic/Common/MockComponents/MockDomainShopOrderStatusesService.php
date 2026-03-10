<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\OrderStatus\Models\OrderStatus;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Services\ShopOrderStatusesService;

/**
 * Class MockDomainShopOrderStatusesService.
 *
 * @package Common\MockComponents
 */
class MockDomainShopOrderStatusesService extends ShopOrderStatusesService
{
    /**
     * @var OrderStatus[] $shopOrderStatuses
     */
    private $shopOrderStatuses = [];

    /**
     * @inheritDoc
     */
    public function getShopOrderStatuses(): array
    {
        return $this->shopOrderStatuses;
    }

    /**
     * @param OrderStatus[] $shopOrderStatuses
     *
     * @return void
     */
    public function setMockShopOrderStatuses(array $shopOrderStatuses): void
    {
        $this->shopOrderStatuses = $shopOrderStatuses;
    }
}

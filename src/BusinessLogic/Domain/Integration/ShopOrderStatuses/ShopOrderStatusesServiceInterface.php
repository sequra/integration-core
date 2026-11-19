<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\ShopOrderStatuses;

use SeQura\Core\BusinessLogic\Domain\OrderStatus\Models\OrderStatus;

/**
 * Interface ShopOrderStatusesServiceInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\Integration\ShopOrderStatuses
 */
interface ShopOrderStatusesServiceInterface
{
    /**
     * Returns all order statuses of the shop system.
     *
     * @return OrderStatus[]
     */
    public function getShopOrderStatuses(): array;
}

<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Services;

use SeQura\Core\BusinessLogic\Domain\Integration\ShopOrderStatuses\ShopOrderStatusesServiceInterface;
use SeQura\Core\BusinessLogic\Domain\OrderStatus\Models\OrderStatus;

/**
 * Class ShopOrderStatusesService
 *
 * @package SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Services
 */
class ShopOrderStatusesService
{
    /**
     * @var ShopOrderStatusesServiceInterface
     */
    private $integrationShopOrderStatusesService;

    public function __construct(ShopOrderStatusesServiceInterface $integrationShopOrderStatusesService)
    {
        $this->integrationShopOrderStatusesService = $integrationShopOrderStatusesService;
    }

    /**
     * Returns all order statuses from the shop system.
     *
     * @return OrderStatus[]
     */
    public function getShopOrderStatuses(): array
    {
        return $this->integrationShopOrderStatusesService->getShopOrderStatuses();
    }
}

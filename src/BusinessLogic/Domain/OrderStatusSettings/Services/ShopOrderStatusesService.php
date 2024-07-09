<?php

namespace SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Services;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Integration\ShopOrderStatuses\ShopOrderStatusesServiceInterface;
use SeQura\Core\BusinessLogic\Domain\OrderStatus\Models\OrderStatus;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Exceptions\FailedToRetrieveShopOrderStatusesException;
use SeQura\Core\BusinessLogic\Domain\Translations\Model\TranslatableLabel;

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
    protected $integrationShopOrderStatusesService;

    public function __construct(ShopOrderStatusesServiceInterface $integrationShopOrderStatusesService)
    {
        $this->integrationShopOrderStatusesService = $integrationShopOrderStatusesService;
    }

    /**
     * Returns all order statuses from the shop system.
     *
     * @return OrderStatus[]
     *
     * @throws FailedToRetrieveShopOrderStatusesException
     */
    public function getShopOrderStatuses(): array
    {
        try {
            return $this->integrationShopOrderStatusesService->getShopOrderStatuses();
        } catch (Exception $e) {
            throw new FailedToRetrieveShopOrderStatusesException(new TranslatableLabel('Failed to retrieve order statuses.', 'general.errors.orderStatusSettings.orderStatuses'));
        }
    }
}

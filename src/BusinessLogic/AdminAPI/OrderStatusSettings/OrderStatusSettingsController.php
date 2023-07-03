<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings;

use SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Requests\OrderStatusSettingsRequest;
use SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Responses\OrderStatusSettingsResponse;
use SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Responses\ShopOrderStatusResponse;
use SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Responses\SuccessfulOrderStatusSettingsResponse;
use SeQura\Core\BusinessLogic\Domain\OrderStatus\Models\OrderStatus;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Models\OrderStatusMapping;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Models\OrderStatusSettings;

/**
 * Class OrderStatusSettingsController
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings
 */
class OrderStatusSettingsController
{
    /**
     * Gets active order status settings.
     *
     * @return OrderStatusSettingsResponse
     */
    public function getOrderStatusSettings(): OrderStatusSettingsResponse
    {
        return new OrderStatusSettingsResponse(new OrderStatusSettings(
            [
                new OrderStatusMapping('approved','status1'),
                new OrderStatusMapping('needs_review','status3'),
                new OrderStatusMapping('cancelled','status2'),
            ],
            true)
        );
    }

    /**
     * Saves new order status settings.
     *
     * @param OrderStatusSettingsRequest $request
     *
     * @return SuccessfulOrderStatusSettingsResponse
     */
    public function saveCountryConfigurations(OrderStatusSettingsRequest $request): SuccessfulOrderStatusSettingsResponse
    {
        return new SuccessfulOrderStatusSettingsResponse();
    }

    /**
     * Gets all order statuses of the shop.
     *
     * @return ShopOrderStatusResponse
     */
    public function getShopOrderStatuses(): ShopOrderStatusResponse
    {
        return new ShopOrderStatusResponse([
            new OrderStatus('status1', 'paid'),
            new OrderStatus('status2', 'denied'),
            new OrderStatus('status3', 'pending'),
            new OrderStatus('status4', 'refunded'),
            new OrderStatus('status5', 'shipped'),
        ]);
    }
}

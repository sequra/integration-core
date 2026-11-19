<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings;

use SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Requests\OrderStatusSettingsRequest;
use SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Responses\OrderStatusSettingsResponse;
use SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Responses\ShopOrderStatusResponse;
use SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Responses\SuccessfulOrderStatusSettingsResponse;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Exceptions\EmptyOrderStatusMappingParameterException;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Exceptions\FailedToRetrieveShopOrderStatusesException;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Exceptions\InvalidSeQuraOrderStatusException;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Services\OrderStatusSettingsService;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Services\ShopOrderStatusesService;

/**
 * Class OrderStatusSettingsController
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings
 */
class OrderStatusSettingsController
{
    /**
     * @var OrderStatusSettingsService
     */
    protected $orderStatusSettingsService;

    /**
     * @var ShopOrderStatusesService
     */
    protected $shopOrderStatusesService;

    /**
     * @param OrderStatusSettingsService $orderStatusSettingsService
     * @param ShopOrderStatusesService $shopOrderStatusesService
     */
    public function __construct(
        OrderStatusSettingsService $orderStatusSettingsService,
        ShopOrderStatusesService $shopOrderStatusesService
    ) {
        $this->orderStatusSettingsService = $orderStatusSettingsService;
        $this->shopOrderStatusesService = $shopOrderStatusesService;
    }

    /**
     * Gets active order status settings.
     *
     * @return OrderStatusSettingsResponse
     */
    public function getOrderStatusSettings(): OrderStatusSettingsResponse
    {
        return new OrderStatusSettingsResponse($this->orderStatusSettingsService->getOrderStatusSettings());
    }

    /**
     * Saves new order status settings.
     *
     * @param OrderStatusSettingsRequest $request
     *
     * @return SuccessfulOrderStatusSettingsResponse
     *
     * @throws EmptyOrderStatusMappingParameterException
     * @throws InvalidSeQuraOrderStatusException
     */
    public function saveOrderStatusSettings(OrderStatusSettingsRequest $request): SuccessfulOrderStatusSettingsResponse
    {
        $this->orderStatusSettingsService->saveOrderStatusSettings($request->transformToDomainModel());

        return new SuccessfulOrderStatusSettingsResponse();
    }

    /**
     * Gets all order statuses of the shop.
     *
     * @return ShopOrderStatusResponse
     *
     * @throws FailedToRetrieveShopOrderStatusesException
     */
    public function getShopOrderStatuses(): ShopOrderStatusResponse
    {
        return new ShopOrderStatusResponse($this->shopOrderStatusesService->getShopOrderStatuses());
    }
}

<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\OrderStatus;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\OrderStatus\GetOrderStatusListResponse;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Exceptions\FailedToRetrieveShopOrderStatusesException;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Services\ShopOrderStatusesService;

/**
 * Class GetOrderStatusListHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\OrderStatus
 */
class GetOrderStatusListHandler implements TopicHandlerInterface
{
    /**
     * @var ShopOrderStatusesService
     */
    protected $shopOrderStatusesService;

    /**
     * @param ShopOrderStatusesService $shopOrderStatusesService
     */
    public function __construct(ShopOrderStatusesService $shopOrderStatusesService)
    {
        $this->shopOrderStatusesService = $shopOrderStatusesService;
    }

    /**
     * @inheritDoc
     *
     * @throws FailedToRetrieveShopOrderStatusesException
     */
    public function handle(array $payload): Response
    {
        return new GetOrderStatusListResponse($this->shopOrderStatusesService->getShopOrderStatuses());
    }
}

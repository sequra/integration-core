<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\OrderStatus;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\OrderStatus\GetOrderStatusSettingsResponse;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Services\OrderStatusSettingsService;

/**
 * Class GetOrderStatusSettingsHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\OrderStatus
 */
class GetOrderStatusSettingsHandler implements TopicHandlerInterface
{
    /**
     * @var OrderStatusSettingsService
     */
    protected $orderStatusSettingsService;

    /**
     * @param OrderStatusSettingsService $orderStatusSettingsService
     */
    public function __construct(OrderStatusSettingsService $orderStatusSettingsService)
    {
        $this->orderStatusSettingsService = $orderStatusSettingsService;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $payload): Response
    {
        return new GetOrderStatusSettingsResponse($this->orderStatusSettingsService->getOrderStatusSettings());
    }
}

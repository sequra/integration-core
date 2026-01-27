<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\OrderStatus;

use SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\OrderStatusSettingsController;
use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;

/**
 * Class GetOrderStatusListHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\OrderStatus
 */
class GetOrderStatusListHandler implements TopicHandlerInterface
{
    /**
     * @var OrderStatusSettingsController
     */
    protected $orderStatusSettingsController;

    /**
     * @param OrderStatusSettingsController $orderStatusSettingsController
     */
    public function __construct(OrderStatusSettingsController $orderStatusSettingsController)
    {
        $this->orderStatusSettingsController = $orderStatusSettingsController;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $payload): Response
    {
        return $this->orderStatusSettingsController->getShopOrderStatuses();
    }
}

<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\OrderStatus;

use SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\OrderStatusSettingsController;
use SeQura\Core\BusinessLogic\AdminAPI\OrderStatusSettings\Requests\OrderStatusSettingsRequest;
use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;

/**
 * Class SaveOrderStatusSettingsHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\OrderStatus
 */
class SaveOrderStatusSettingsHandler implements TopicHandlerInterface
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
        $data = $payload['data'] ?? [];

        $request = new OrderStatusSettingsRequest(
            $data['orderStatusMappings'] ?? []
        );

        return $this->orderStatusSettingsController->saveOrderStatusSettings($request);
    }
}

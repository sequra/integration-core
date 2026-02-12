<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\OrderStatus;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\OrderStatus\SaveOrderStatusRequest;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\OrderStatus\SaveOrderStatusSettingsResponse;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Exceptions\EmptyOrderStatusMappingParameterException;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Exceptions\InvalidSeQuraOrderStatusException;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\Services\OrderStatusSettingsService;

/**
 * Class SaveOrderStatusSettingsHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\OrderStatus
 */
class SaveOrderStatusSettingsHandler implements TopicHandlerInterface
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
     * @param mixed[] $payload
     *
     * @return Response
     *
     * @throws EmptyOrderStatusMappingParameterException
     * @throws InvalidSeQuraOrderStatusException
     */
    public function handle(array $payload): Response
    {
        $request = SaveOrderStatusRequest::fromPayload($payload);
        $this->orderStatusSettingsService->saveOrderStatusSettings($request->transformToDomainModel());

        return new SaveOrderStatusSettingsResponse();
    }
}

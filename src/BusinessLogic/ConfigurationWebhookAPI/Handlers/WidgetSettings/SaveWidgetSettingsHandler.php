<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\WidgetSettings;

use Exception;
use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\WidgetSettings\SaveWidgetSettingsRequest;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\WidgetSettings\SaveWidgetSettingsResponse;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetSettingsService;

/**
 * Class SaveWidgetSettingsHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\WidgetSettings
 */
class SaveWidgetSettingsHandler implements TopicHandlerInterface
{
    /**
     * @var WidgetSettingsService
     */
    protected $widgetSettingsService;

    /**
     * @param WidgetSettingsService $widgetSettingsService
     */
    public function __construct(WidgetSettingsService $widgetSettingsService)
    {
        $this->widgetSettingsService = $widgetSettingsService;
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function handle(array $payload): Response
    {
        $request = SaveWidgetSettingsRequest::fromPayload($payload);
        $this->widgetSettingsService->setWidgetSettings($request->transformToDomainModel());

        return new SaveWidgetSettingsResponse();
    }
}

<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\AdvancedSettings;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\AdvancedSettings\AdvancedSettingsResponse;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\SuccessResponse;
use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Services\AdvancedSettingsService;

/**
 * Class GetAdvancedSettingsHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\AdvancedSettings
 */
class GetAdvancedSettingsHandler implements TopicHandlerInterface
{
    /**
     * @var AdvancedSettingsService
     */
    protected $advancedSettingsService;

    /**
     * @param AdvancedSettingsService $advancedSettingsService
     */
    public function __construct(AdvancedSettingsService $advancedSettingsService)
    {
        $this->advancedSettingsService = $advancedSettingsService;
    }

    /**
     * @param mixed[] $payload
     *
     * @return Response
     */
    public function handle(array $payload): Response
    {
        $advancedSettings = $this->advancedSettingsService->getAdvancedSettings();

        if (!$advancedSettings) {
            return new SuccessResponse();
        }

        return new AdvancedSettingsResponse($advancedSettings);
    }
}

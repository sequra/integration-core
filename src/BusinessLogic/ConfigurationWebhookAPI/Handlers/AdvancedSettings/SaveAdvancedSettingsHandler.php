<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\AdvancedSettings;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Requests\AdvancedSettings\SaveAdvancedSettingsRequest;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\SuccessResponse;
use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Services\AdvancedSettingsService;

/**
 * Class SaveAdvancedSettingsHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\AdvancedSettings
 */
class SaveAdvancedSettingsHandler implements TopicHandlerInterface
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
     * @inheritDoc
     */
    public function handle(array $payload): Response
    {
        $request = SaveAdvancedSettingsRequest::fromPayload($payload);
        $this->advancedSettingsService->setAdvancedSettings($request->transformToDomainModel());

        return new SuccessResponse();
    }
}

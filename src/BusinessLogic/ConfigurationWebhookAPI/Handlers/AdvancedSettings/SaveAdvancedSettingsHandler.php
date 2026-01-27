<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\AdvancedSettings;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\SuccessResponse;
use SeQura\Core\BusinessLogic\Domain\Integration\AdvancedSettings\AdvancedSettingsServiceInterface;

/**
 * Class SaveAdvancedSettingsHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\AdvancedSettings
 */
class SaveAdvancedSettingsHandler implements TopicHandlerInterface
{
    /**
     * @var AdvancedSettingsServiceInterface
     */
    protected $advancedSettingsService;

    /**
     * @param AdvancedSettingsServiceInterface $advancedSettingsService
     */
    public function __construct(AdvancedSettingsServiceInterface $advancedSettingsService)
    {
        $this->advancedSettingsService = $advancedSettingsService;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $payload): Response
    {
        $data = $payload['data'] ?? [];

        $this->advancedSettingsService->saveAdvancedSettings(
            $data['isEnabled'] ?? false,
            $data['level'] ?? 3
        );

        return new SuccessResponse();
    }
}

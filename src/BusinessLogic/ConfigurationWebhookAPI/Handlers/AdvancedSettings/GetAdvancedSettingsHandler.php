<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\AdvancedSettings;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\AdvancedSettings\AdvancedSettingsResponse;
use SeQura\Core\BusinessLogic\Domain\Integration\AdvancedSettings\AdvancedSettingsServiceInterface;

/**
 * Class GetAdvancedSettingsHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\AdvancedSettings
 */
class GetAdvancedSettingsHandler implements TopicHandlerInterface
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
        return new AdvancedSettingsResponse($this->advancedSettingsService->getAdvancedSettings());
    }
}

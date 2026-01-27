<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\GeneralSettings;

use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\GeneralSettingsController;
use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;

/**
 * Class GetGeneralSettingsHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\GeneralSettings
 */
class GetGeneralSettingsHandler implements TopicHandlerInterface
{
    /**
     * @var GeneralSettingsController
     */
    protected $generalSettingsController;

    /**
     * @param GeneralSettingsController $generalSettingsController
     */
    public function __construct(GeneralSettingsController $generalSettingsController)
    {
        $this->generalSettingsController = $generalSettingsController;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $payload): Response
    {
        return $this->generalSettingsController->getGeneralSettings();
    }
}

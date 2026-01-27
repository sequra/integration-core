<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\GeneralSettings;

use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\GeneralSettingsController;
use SeQura\Core\BusinessLogic\AdminAPI\GeneralSettings\Requests\GeneralSettingsRequest;
use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;

/**
 * Class SaveGeneralSettingsHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\GeneralSettings
 */
class SaveGeneralSettingsHandler implements TopicHandlerInterface
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
        $data = $payload['data'] ?? [];

        $request = new GeneralSettingsRequest(
            $data['sendOrderReportsPeriodicallyToSeQura'] ?? false,
            $data['showSeQuraCheckoutAsHostedPage'] ?? null,
            $data['allowedIPAddresses'] ?? null,
            $data['excludedProducts'] ?? null,
            $data['excludedCategories'] ?? null,
            $data['defaultServicesEndDate'] ?? null
        );

        return $this->generalSettingsController->saveGeneralSettings($request);
    }
}

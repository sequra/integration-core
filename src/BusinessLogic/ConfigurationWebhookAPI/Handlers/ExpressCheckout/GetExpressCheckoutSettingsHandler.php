<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\ExpressCheckout;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\ExpressCheckout\GetExpressCheckoutSettingsResponse;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Services\ExpressCheckoutSettingsService;
use SeQura\Core\BusinessLogic\Domain\Integration\ExpressCheckout\ExpressCheckoutIntegrationInterface;

/**
 * Class GetExpressCheckoutSettingsHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\ExpressCheckout
 */
class GetExpressCheckoutSettingsHandler implements TopicHandlerInterface
{
    /**
     * @var ExpressCheckoutSettingsService
     */
    protected $expressCheckoutSettingsService;

    /**
     * @var ExpressCheckoutIntegrationInterface
     */
    protected $expressCheckoutIntegration;

    /**
     * @param ExpressCheckoutSettingsService $expressCheckoutSettingsService
     * @param ExpressCheckoutIntegrationInterface $expressCheckoutIntegration
     */
    public function __construct(
        ExpressCheckoutSettingsService $expressCheckoutSettingsService,
        ExpressCheckoutIntegrationInterface $expressCheckoutIntegration
    ) {
        $this->expressCheckoutSettingsService = $expressCheckoutSettingsService;
        $this->expressCheckoutIntegration = $expressCheckoutIntegration;
    }

    /**
     * @param mixed[] $payload
     *
     * @return Response
     */
    public function handle(array $payload): Response
    {
        $availablePages = $this->expressCheckoutIntegration->getAvailablePages();
        $settings = $this->expressCheckoutSettingsService->getExpressCheckoutSettings();

        return new GetExpressCheckoutSettingsResponse($availablePages, $settings);
    }
}

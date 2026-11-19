<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\ExpressCheckout;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\ExpressCheckout\GetExpressCheckoutSettingsResponse;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Services\ExpressCheckoutService;
use SeQura\Core\BusinessLogic\Domain\Integration\ExpressCheckout\ExpressCheckoutIntegrationInterface;

/**
 * Class GetExpressCheckoutSettingsHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\ExpressCheckout
 */
class GetExpressCheckoutSettingsHandler implements TopicHandlerInterface
{
    /**
     * @var ExpressCheckoutService
     */
    protected $expressCheckoutService;

    /**
     * @var ExpressCheckoutIntegrationInterface
     */
    protected $expressCheckoutIntegration;

    /**
     * @param ExpressCheckoutService $expressCheckoutService
     * @param ExpressCheckoutIntegrationInterface $expressCheckoutIntegration
     */
    public function __construct(
        ExpressCheckoutService $expressCheckoutService,
        ExpressCheckoutIntegrationInterface $expressCheckoutIntegration
    ) {
        $this->expressCheckoutService = $expressCheckoutService;
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
        $settings = $this->expressCheckoutService->getExpressCheckoutSettings();

        return new GetExpressCheckoutSettingsResponse($availablePages, $settings);
    }
}

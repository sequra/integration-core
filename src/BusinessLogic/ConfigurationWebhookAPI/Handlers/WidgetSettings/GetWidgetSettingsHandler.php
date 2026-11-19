<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\WidgetSettings;

use Exception;
use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\WidgetSettings\GetWidgetSettingsResponse;
use SeQura\Core\BusinessLogic\Domain\PaymentMethod\Services\PaymentMethodsService;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetSettingsService;

/**
 * Class GetWidgetSettingsHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\WidgetSettings
 */
class GetWidgetSettingsHandler implements TopicHandlerInterface
{
    /**
     * @var WidgetSettingsService $widgetSettingsService
     */
    protected $widgetSettingsService;

    /**
     * @var PaymentMethodsService $paymentMethodsService
     */
    protected $paymentMethodsService;

    /**
     * @param WidgetSettingsService $widgetSettingsService
     * @param PaymentMethodsService $paymentMethodsService
     */
    public function __construct(
        WidgetSettingsService $widgetSettingsService,
        PaymentMethodsService $paymentMethodsService
    ) {
        $this->widgetSettingsService = $widgetSettingsService;
        $this->paymentMethodsService = $paymentMethodsService;
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function handle(array $payload): Response
    {
        return new GetWidgetSettingsResponse(
            $this->widgetSettingsService->getWidgetSettings(),
            $this->paymentMethodsService->getGroupedPaymentMethodsForAllMerchants(true)
        );
    }
}

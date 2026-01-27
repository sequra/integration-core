<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\WidgetSettings;

use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\PromotionalWidgetsController;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Requests\WidgetSettingsRequest;
use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;

/**
 * Class SaveWidgetSettingsHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\WidgetSettings
 */
class SaveWidgetSettingsHandler implements TopicHandlerInterface
{
    /**
     * @var PromotionalWidgetsController
     */
    protected $promotionalWidgetsController;

    /**
     * @param PromotionalWidgetsController $promotionalWidgetsController
     */
    public function __construct(PromotionalWidgetsController $promotionalWidgetsController)
    {
        $this->promotionalWidgetsController = $promotionalWidgetsController;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $payload): Response
    {
        $data = $payload['data'] ?? [];

        $request = new WidgetSettingsRequest(
            $data['displayOnProductPage'] ?? false,
            $data['showInstallmentsInProductListing'] ?? false,
            $data['showInstallmentsInCartPage'] ?? false,
            $data['widgetConfiguration'] ?? '',
            $data['productPriceSelector'] ?? '',
            $data['defaultProductLocationSelector'] ?? '',
            $data['cartPriceSelector'] ?? '',
            $data['cartLocationSelector'] ?? '',
            $data['widgetOnCartPage'] ?? '',
            $data['widgetOnListingPage'] ?? '',
            $data['listingPriceSelector'] ?? '',
            $data['listingLocationSelector'] ?? '',
            $data['altProductPriceSelector'] ?? '',
            $data['altProductPriceTriggerSelector'] ?? '',
            $data['customLocations'] ?? []
        );

        return $this->promotionalWidgetsController->setWidgetSettings($request);
    }
}

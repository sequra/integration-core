<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings;

/**
 * Class WidgetConfigurationResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Responses
 */
class WidgetSettingsResponse extends Response
{
    /**
     * @var WidgetSettings
     */
    protected $widgetSettings;

    /**
     * @param WidgetSettings|null $widgetSettings
     */
    public function __construct(?WidgetSettings $widgetSettings)
    {
        $this->widgetSettings = $widgetSettings;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        if (!$this->widgetSettings) {
            return [];
        }

        $locationConfig = $this->widgetSettings->getLocationConfig();
        $nonDefaultLocations = [];
        if ($locationConfig?->getLocations()) {
            foreach ($locationConfig->getLocations() as $location) {
                if (!$location->isDefaultLocation()) {
                    $nonDefaultLocations[] = $location->toArray();
                }
            }
        }

        return [
            'useWidgets' => $this->widgetSettings->isEnabled(),
            'displayWidgetOnProductPage' => $this->widgetSettings->isDisplayOnProductPage(),
            'showInstallmentAmountInProductListing' => $this->widgetSettings->isShowInstallmentsInProductListing(),
            'showInstallmentAmountInCartPage' => $this->widgetSettings->isShowInstallmentsInCartPage(),
            'assetsKey' => $this->widgetSettings->getAssetsKey(),
            'miniWidgetSelector' => $this->widgetSettings->getMiniWidgetSelector(),
            'widgetConfiguration' => $this->widgetSettings->getWidgetConfig(),
            'widgetLabels' => $this->widgetSettings->getWidgetLabels() ? [
                'messages' => $this->widgetSettings->getWidgetLabels()->getMessages(),
                'messagesBelowLimit' => $this->widgetSettings->getWidgetLabels()->getMessagesBelowLimit(),
            ] : [],
            'selForPrice' => $locationConfig?->getSelForPrice(),
            'selForAltPrice' => $locationConfig?->getSelForAltPrice(),
            'selForAltPriceTrigger' => $locationConfig?->getSelForAltPriceTrigger(),
            'defaultLocationSel' => $locationConfig?->getDefaultLocation()?->getSelForTarget(),
            'locations' => $nonDefaultLocations,
        ];
    }
}

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
        $customLocations = [];
        if ($locationConfig) {
            foreach ($locationConfig->getCustomLocations() as $loc) {
                $customLocations[] = $loc->toArray();
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
            'selForPrice' => $locationConfig ? $locationConfig->getSelForPrice() : null,
            'selForAltPrice' => $locationConfig ? $locationConfig->getSelForAltPrice() : null,
            'selForAltPriceTrigger' => $locationConfig ? $locationConfig->getSelForAltPriceTrigger() : null,
            'selForDefaultLocation' => $locationConfig ? $locationConfig->getSelForDefaultLocation() : null,
            'customLocations' => $customLocations,
        ];
    }
}

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

        $widgetSettingsArray = [
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
            ] : []
        ];

        $widgetSettingsForProduct = $this->widgetSettings->getWidgetSettingsForProduct();
        $widgetSettingsForCart = $this->widgetSettings->getWidgetSettingsForCart();
        $widgetSettingsForListing = $this->widgetSettings->getWidgetSettingsForListing();

        if ($widgetSettingsForProduct) {
            $widgetSettingsArray['productPriceSelector'] = $widgetSettingsForProduct->getPriceSelector();
            $widgetSettingsArray['defaultProductLocationSelector'] = $widgetSettingsForProduct->getLocationSelector();
            $widgetSettingsArray['altProductPriceSelector'] = $widgetSettingsForProduct->getAltPriceSelector();
            $widgetSettingsArray['altProductPriceTriggerSelector'] = $widgetSettingsForProduct->getAltPriceTriggerSelector();
            $widgetSettingsArray['customLocations'] = [];

            foreach ($widgetSettingsForProduct->getCustomWidgetsSettings() as $customWidgetSettings) {
                $widgetSettingsArray['customLocations'][] = [
                    'product' => $customWidgetSettings->getProduct(),
                    'selForTarget' => $customWidgetSettings->getCustomLocationSelector(),
                    'displayWidget' => $customWidgetSettings->isDisplayWidget(),
                    'widgetStyles' => $customWidgetSettings->getCustomWidgetStyle()
                ];
            }
        }

        if ($widgetSettingsForCart) {
            $widgetSettingsArray['cartPriceSelector'] = $widgetSettingsForCart->getPriceSelector();
            $widgetSettingsArray['cartLocationSelector'] = $widgetSettingsForCart->getLocationSelector();
            $widgetSettingsArray['widgetOnCartPage'] = $widgetSettingsForCart->getWidgetProduct();
        }

        if ($widgetSettingsForListing) {
            $widgetSettingsArray['listingPriceSelector'] = $widgetSettingsForListing->getPriceSelector();
            $widgetSettingsArray['listingLocationSelector'] = $widgetSettingsForListing->getLocationSelector();
            $widgetSettingsArray['widgetOnListingPage'] = $widgetSettingsForListing->getWidgetProduct();
        }

        return $widgetSettingsArray;
    }
}

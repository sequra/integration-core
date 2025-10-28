<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\DefaultWidgetSettings;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings;

/**
 * Class WidgetSettingsResponse
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
     * @var DefaultWidgetSettings|null
     */
    protected $defaultWidgetSettings;

    /**
     * @param WidgetSettings|null $widgetSettings
     * @param DefaultWidgetSettings|null $defaultWidgetSettings
     */
    public function __construct(?WidgetSettings $widgetSettings, ?DefaultWidgetSettings $defaultWidgetSettings = null)
    {
        $this->widgetSettings = $widgetSettings;
        $this->defaultWidgetSettings = $defaultWidgetSettings;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        if (!$this->widgetSettings && $this->defaultWidgetSettings) {
            return $this->defaultWidgetSettings->toArray();
        }

        $widgetSettingsArray = [
            'displayWidgetOnProductPage' => $this->widgetSettings->isDisplayOnProductPage(),
            'showInstallmentAmountInProductListing' => $this->widgetSettings->isShowInstallmentsInProductListing(),
            'showInstallmentAmountInCartPage' => $this->widgetSettings->isShowInstallmentsInCartPage(),
            'widgetStyles' => $this->widgetSettings->getWidgetConfig()
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

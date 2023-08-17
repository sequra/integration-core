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
    private $widgetSettings;

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

        return [
            'useWidgets' => $this->widgetSettings->isEnabled(),
            'displayWidgetOnProductPage' => $this->widgetSettings->isDisplayOnProductPage(),
            'showInstallmentAmountInProductListing' => $this->widgetSettings->isShowInstallmentsInProductListing(),
            'showInstallmentAmountInCartPage' => $this->widgetSettings->isShowInstallmentsInCartPage(),
            'assetsKey' => $this->widgetSettings->getAssetsKey(),
            'displayMiniWidgetOnProductListingPage' => $this->widgetSettings->isDisplayMiniWidgetOnProductListingPage(),
            'miniWidgetSelector' => $this->widgetSettings->getMiniWidgetSelector(),
            'widgetConfiguration' => $this->widgetSettings->getWidgetConfig() ? [
                'type' => $this->widgetSettings->getWidgetConfig()->getType(),
                'size' => $this->widgetSettings->getWidgetConfig()->getSize(),
                'fontColor' => $this->widgetSettings->getWidgetConfig()->getFontColor(),
                'backgroundColor' => $this->widgetSettings->getWidgetConfig()->getBackgroundColor(),
                'alignment' => $this->widgetSettings->getWidgetConfig()->getAlignment(),
                'branding' => $this->widgetSettings->getWidgetConfig()->getBranding(),
                'startingText' => $this->widgetSettings->getWidgetConfig()->getStartingText(),
                'amountFontSize' => $this->widgetSettings->getWidgetConfig()->getAmountFontSize(),
                'amountFontColor' => $this->widgetSettings->getWidgetConfig()->getAmountFontColor(),
                'amountFontBold' => $this->widgetSettings->getWidgetConfig()->getAmountFontBold(),
                'linkFontColor' => $this->widgetSettings->getWidgetConfig()->getLinkFontColor(),
                'linkUnderline' => $this->widgetSettings->getWidgetConfig()->getLinkUnderline(),
                'borderColor' => $this->widgetSettings->getWidgetConfig()->getBorderColor(),
                'borderRadius' => $this->widgetSettings->getWidgetConfig()->getBorderRadius(),
                'noCostsClaim' => $this->widgetSettings->getWidgetConfig()->getNoCostsClaim(),
            ] : [],
            'widgetLabels' => $this->widgetSettings->getWidgetLabels() ? [
                'messages' => $this->widgetSettings->getWidgetLabels()->getMessages(),
                'messagesBelowLimit' => $this->widgetSettings->getWidgetLabels()->getMessagesBelowLimit(),
            ] : [],
        ];
    }
}

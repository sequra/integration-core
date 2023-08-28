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
            'miniWidgetSelector' => $this->widgetSettings->getMiniWidgetSelector(),
            'widgetConfiguration' => $this->widgetSettings->getWidgetConfig() ? [
                'type' => $this->widgetSettings->getWidgetConfig()->getType(),
                'size' => $this->widgetSettings->getWidgetConfig()->getSize(),
                'font-color' => $this->widgetSettings->getWidgetConfig()->getFontColor(),
                'background-color' => $this->widgetSettings->getWidgetConfig()->getBackgroundColor(),
                'alignment' => $this->widgetSettings->getWidgetConfig()->getAlignment(),
                'branding' => $this->widgetSettings->getWidgetConfig()->getBranding(),
                'starting-text' => $this->widgetSettings->getWidgetConfig()->getStartingText(),
                'amount-font-size' => $this->widgetSettings->getWidgetConfig()->getAmountFontSize(),
                'amount-font-color' => $this->widgetSettings->getWidgetConfig()->getAmountFontColor(),
                'amount-font-bold' => $this->widgetSettings->getWidgetConfig()->getAmountFontBold(),
                'link-font-color' => $this->widgetSettings->getWidgetConfig()->getLinkFontColor(),
                'link-underline' => $this->widgetSettings->getWidgetConfig()->getLinkUnderline(),
                'border-color' => $this->widgetSettings->getWidgetConfig()->getBorderColor(),
                'border-radius' => $this->widgetSettings->getWidgetConfig()->getBorderRadius(),
                'no-costs-claim' => $this->widgetSettings->getWidgetConfig()->getNoCostsClaim(),
            ] : [],
            'widgetLabels' => $this->widgetSettings->getWidgetLabels() ? [
                'messages' => $this->widgetSettings->getWidgetLabels()->getMessages(),
                'messagesBelowLimit' => $this->widgetSettings->getWidgetLabels()->getMessagesBelowLimit(),
            ] : [],
        ];
    }
}

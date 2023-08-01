<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetConfiguration;

/**
 * Class WidgetConfigResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Responses
 */
class WidgetConfigResponse extends Response
{
    /**
     * @var WidgetConfiguration
     */
    private $widgetConfig;

    /**
     * @param WidgetConfiguration|null $widgetConfig
     */
    public function __construct(?WidgetConfiguration $widgetConfig)
    {
        $this->widgetConfig = $widgetConfig;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        if (!$this->widgetConfig) {
            return [];
        }

        return [
            'type' => $this->widgetConfig->getType(),
            'size' => $this->widgetConfig->getSize(),
            'font-color' => $this->widgetConfig->getFontColor(),
            'background-color' => $this->widgetConfig->getBackgroundColor(),
            'alignment' => $this->widgetConfig->getAlignment(),
            'branding' => $this->widgetConfig->getBranding(),
            'starting-text' => $this->widgetConfig->getStartingText(),
            'amount-font-size' => $this->widgetConfig->getAmountFontSize(),
            'amount-font-color' => $this->widgetConfig->getAmountFontColor(),
            'amount-font-bold' => $this->widgetConfig->getAmountFontBold(),
            'link-font-color' => $this->widgetConfig->getLinkFontColor(),
            'link-underline' => $this->widgetConfig->getLinkUnderline(),
            'border-color' => $this->widgetConfig->getBorderColor(),
            'border-radius' => $this->widgetConfig->getBorderRadius(),
            'no-costs-claim' => $this->widgetConfig->getNoCostsClaim(),
        ];
    }
}
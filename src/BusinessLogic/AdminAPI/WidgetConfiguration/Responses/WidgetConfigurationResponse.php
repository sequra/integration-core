<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\WidgetConfiguration\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\WidgetConfiguration\Models\WidgetConfiguration;

/**
 * Class WidgetConfigurationResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\WidgetConfiguration\Responses
 */
class WidgetConfigurationResponse extends Response
{
    /**
     * @var WidgetConfiguration
     */
    private $widgetConfiguration;

    /**
     * @param WidgetConfiguration|null $widgetConfiguration
     */
    public function __construct(?WidgetConfiguration $widgetConfiguration)
    {
        $this->widgetConfiguration = $widgetConfiguration;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        if (!$this->widgetConfiguration) {
            return [];
        }

        return [
            'useWidgets' => $this->widgetConfiguration->isUseWidgets(),
            'disableWidgetOnProductPage' => $this->widgetConfiguration->isDisableWidgetOnProductPage(),
            'showInstallmentAmountInProductListing' => $this->widgetConfiguration->isShowInstallmentAmountInProductListing(),
            'showInstallmentAmountInCartPage' => $this->widgetConfiguration->isShowInstallmentAmountInCartPage(),
            'assetsKey' => $this->widgetConfiguration->getAssetsKey(),
            'widgetStyles' => $this->widgetConfiguration->getWidgetStyles(),
            'widgetLabels' => $this->widgetConfiguration->getWidgetLabels()
        ];
    }
}

<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models;

/**
 * Class WidgetSelectorSettings
 *
 * @package SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models
 */
class WidgetSelectorSettings
{
    /**
     * @var string
     */
    protected $priceSelector;
    /**
     * @var string
     */
    protected $locationSelector;
    /**
     * @var string
     */
    protected $altPriceSelector;
    /**
     * @var string
     */
    protected $altPriceTriggerSelector;
    /**
     * @var string
     */
    protected $widgetProduct;
    /**
     * @var CustomWidgetsSettings[]
     */
    protected $customWidgetsSettings;

    /**
     * @param string $priceSelector
     * @param string $locationSelector
     * @param string $widgetProduct
     * @param string $altPriceSelector
     * @param string $altPriceTriggerSelector
     * @param CustomWidgetsSettings[] $customWidgetsSettings
     */
    public function __construct(
        string $priceSelector,
        string $locationSelector,
        string $widgetProduct = '',
        string $altPriceSelector = '',
        string $altPriceTriggerSelector = '',
        array $customWidgetsSettings = []
    ) {
        $this->priceSelector = $priceSelector;
        $this->locationSelector = $locationSelector;
        $this->widgetProduct = $widgetProduct;
        $this->altPriceSelector = $altPriceSelector;
        $this->altPriceTriggerSelector = $altPriceTriggerSelector;
        $this->customWidgetsSettings = $customWidgetsSettings;
    }

    /**
     * @return string
     */
    public function getPriceSelector(): string
    {
        return $this->priceSelector;
    }

    /**
     * @param string $priceSelector
     */
    public function setPriceSelector(string $priceSelector): void
    {
        $this->priceSelector = $priceSelector;
    }

    /**
     * @return string
     */
    public function getLocationSelector(): string
    {
        return $this->locationSelector;
    }

    /**
     * @param string $locationSelector
     */
    public function setLocationSelector(string $locationSelector): void
    {
        $this->locationSelector = $locationSelector;
    }

    /**
     * @return string
     */
    public function getAltPriceSelector(): string
    {
        return $this->altPriceSelector;
    }

    /**
     * @param string $altPriceSelector
     */
    public function setAltPriceSelector(string $altPriceSelector): void
    {
        $this->altPriceSelector = $altPriceSelector;
    }

    /**
     * @return string
     */
    public function getAltPriceTriggerSelector(): string
    {
        return $this->altPriceTriggerSelector;
    }

    /**
     * @param string $altPriceTriggerSelector
     */
    public function setAltPriceTriggerSelector(string $altPriceTriggerSelector): void
    {
        $this->altPriceTriggerSelector = $altPriceTriggerSelector;
    }

    /**
     * @return string
     */
    public function getWidgetProduct(): string
    {
        return $this->widgetProduct;
    }

    /**
     * @param string $widgetProduct
     */
    public function setWidgetProduct(string $widgetProduct): void
    {
        $this->widgetProduct = $widgetProduct;
    }

    /**
     * @return CustomWidgetsSettings[]
     */
    public function getCustomWidgetsSettings(): array
    {
        return $this->customWidgetsSettings;
    }

    /**
     * @param CustomWidgetsSettings[] $customWidgetsSettings
     */
    public function setCustomWidgetsSettings(array $customWidgetsSettings): void
    {
        $this->customWidgetsSettings = $customWidgetsSettings;
    }
}

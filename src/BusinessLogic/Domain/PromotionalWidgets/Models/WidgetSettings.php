<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models;

/**
 * Class WidgetSettings
 *
 * @package SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models
 */
class WidgetSettings
{
    /**
     * @var bool
     */
    protected $enabled;
    /**
     * @var bool
     */
    protected $displayOnProductPage;
    /**
     * @var bool
     */
    protected $showInstallmentsInProductListing;
    /**
     * @var bool
     */
    protected $showInstallmentsInCartPage;
    /**
     * @var string|null
     */
    protected $widgetConfig;
    /**
     * @var WidgetSelectorSettings|null
     */
    protected $widgetSettingsForProduct;
    /**
     * @var WidgetSelectorSettings|null
     */
    protected $widgetSettingsForCart;
    /**
     * @var WidgetSelectorSettings|null
     */
    protected $widgetSettingsForListing;

    /**
     * @param bool $enabled
     * @param bool $displayOnProductPage
     * @param bool $showInstallmentsInProductListing
     * @param bool $showInstallmentsInCartPage
     * @param string|null $widgetConfig
     * @param WidgetSelectorSettings|null $widgetSettingsForProduct
     * @param WidgetSelectorSettings|null $widgetSettingsForCart
     * @param WidgetSelectorSettings|null $widgetSettingsForListing
     */
    public function __construct(
        bool $enabled,
        bool $displayOnProductPage = false,
        bool $showInstallmentsInProductListing = false,
        bool $showInstallmentsInCartPage = false,
        ?string $widgetConfig = null,
        WidgetSelectorSettings $widgetSettingsForProduct = null,
        WidgetSelectorSettings $widgetSettingsForCart = null,
        WidgetSelectorSettings $widgetSettingsForListing = null
    ) {
        $this->enabled = $enabled;
        $this->displayOnProductPage = $displayOnProductPage;
        $this->showInstallmentsInProductListing = $showInstallmentsInProductListing;
        $this->showInstallmentsInCartPage = $showInstallmentsInCartPage;
        $this->widgetConfig = $widgetConfig;
        $this->widgetSettingsForProduct = $widgetSettingsForProduct;
        $this->widgetSettingsForCart = $widgetSettingsForCart;
        $this->widgetSettingsForListing = $widgetSettingsForListing;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @return bool
     */
    public function isDisplayOnProductPage(): bool
    {
        return $this->displayOnProductPage;
    }

    /**
     * @return bool
     */
    public function isShowInstallmentsInProductListing(): bool
    {
        return $this->showInstallmentsInProductListing;
    }

    /**
     * @return bool
     */
    public function isShowInstallmentsInCartPage(): bool
    {
        return $this->showInstallmentsInCartPage;
    }

    /**
     * @return ?string
     */
    public function getWidgetConfig(): ?string
    {
        return $this->widgetConfig;
    }

    /**
     * @return WidgetSelectorSettings|null
     */
    public function getWidgetSettingsForProduct(): ?WidgetSelectorSettings
    {
        return $this->widgetSettingsForProduct;
    }

    /**
     * @return WidgetSelectorSettings|null
     */
    public function getWidgetSettingsForCart(): ?WidgetSelectorSettings
    {
        return $this->widgetSettingsForCart;
    }

    /**
     * @return WidgetSelectorSettings|null
     */
    public function getWidgetSettingsForListing(): ?WidgetSelectorSettings
    {
        return $this->widgetSettingsForListing;
    }
}

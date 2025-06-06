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
     * @param bool $enabled
     *
     * @return void
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return bool
     */
    public function isDisplayOnProductPage(): bool
    {
        return $this->displayOnProductPage;
    }

    /**
     * @param bool $displayOnProductPage
     *
     * @return void
     */
    public function setDisplayOnProductPage(bool $displayOnProductPage): void
    {
        $this->displayOnProductPage = $displayOnProductPage;
    }

    /**
     * @return bool
     */
    public function isShowInstallmentsInProductListing(): bool
    {
        return $this->showInstallmentsInProductListing;
    }

    /**
     * @param bool $showInstallmentsInProductListing
     *
     * @return void
     */
    public function setShowInstallmentsInProductListing(bool $showInstallmentsInProductListing): void
    {
        $this->showInstallmentsInProductListing = $showInstallmentsInProductListing;
    }

    /**
     * @return bool
     */
    public function isShowInstallmentsInCartPage(): bool
    {
        return $this->showInstallmentsInCartPage;
    }

    /**
     * @param bool $showInstallmentsInCartPage
     *
     * @return void
     */
    public function setShowInstallmentsInCartPage(bool $showInstallmentsInCartPage): void
    {
        $this->showInstallmentsInCartPage = $showInstallmentsInCartPage;
    }

    /**
     * @return string|null
     */
    public function getWidgetConfig(): ?string
    {
        return $this->widgetConfig;
    }

    /**
     * @param string|null $widgetConfig
     *
     * @return void
     */
    public function setWidgetConfig(?string $widgetConfig): void
    {
        $this->widgetConfig = $widgetConfig;
    }

    /**
     * @return WidgetSelectorSettings|null
     */
    public function getWidgetSettingsForProduct(): ?WidgetSelectorSettings
    {
        return $this->widgetSettingsForProduct;
    }

    /**
     * @param WidgetSelectorSettings|null $widgetSettingsForProduct
     */
    public function setWidgetSettingsForProduct(?WidgetSelectorSettings $widgetSettingsForProduct): void
    {
        $this->widgetSettingsForProduct = $widgetSettingsForProduct;
    }

    /**
     * @return WidgetSelectorSettings|null
     */
    public function getWidgetSettingsForCart(): ?WidgetSelectorSettings
    {
        return $this->widgetSettingsForCart;
    }

    /**
     * @param WidgetSelectorSettings|null $widgetSettingsForCart
     */
    public function setWidgetSettingsForCart(?WidgetSelectorSettings $widgetSettingsForCart): void
    {
        $this->widgetSettingsForCart = $widgetSettingsForCart;
    }

    /**
     * @return WidgetSelectorSettings|null
     */
    public function getWidgetSettingsForListing(): ?WidgetSelectorSettings
    {
        return $this->widgetSettingsForListing;
    }

    /**
     * @param WidgetSelectorSettings|null $widgetSettingsForListing
     */
    public function setWidgetSettingsForListing(?WidgetSelectorSettings $widgetSettingsForListing): void
    {
        $this->widgetSettingsForListing = $widgetSettingsForListing;
    }
}

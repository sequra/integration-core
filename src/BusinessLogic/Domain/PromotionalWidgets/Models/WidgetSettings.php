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
     * @var string
     */
    protected $assetsKey;
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
     * @param string $assetsKey
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
        string $assetsKey = '',
        bool $displayOnProductPage = false,
        bool $showInstallmentsInProductListing = false,
        bool $showInstallmentsInCartPage = false,
        ?string $widgetConfig = null,
        WidgetSelectorSettings $widgetSettingsForProduct = null,
        WidgetSelectorSettings $widgetSettingsForCart = null,
        WidgetSelectorSettings $widgetSettingsForListing = null
    ) {
        $this->enabled = $enabled;
        $this->assetsKey = $assetsKey;
        $this->displayOnProductPage = $displayOnProductPage;
        $this->showInstallmentsInProductListing = $showInstallmentsInProductListing;
        $this->showInstallmentsInCartPage = $showInstallmentsInCartPage;
        $this->widgetConfig = $widgetConfig;
        $this->widgetSettingsForProduct = $widgetSettingsForProduct;
        $this->widgetSettingsForCart = $widgetSettingsForCart;
        $this->widgetSettingsForListing = $widgetSettingsForListing;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    public function getAssetsKey(): string
    {
        return $this->assetsKey;
    }

    public function setAssetsKey(string $assetsKey): void
    {
        $this->assetsKey = $assetsKey;
    }

    public function isDisplayOnProductPage(): bool
    {
        return $this->displayOnProductPage;
    }

    public function setDisplayOnProductPage(bool $displayOnProductPage): void
    {
        $this->displayOnProductPage = $displayOnProductPage;
    }

    public function isShowInstallmentsInProductListing(): bool
    {
        return $this->showInstallmentsInProductListing;
    }

    public function setShowInstallmentsInProductListing(bool $showInstallmentsInProductListing): void
    {
        $this->showInstallmentsInProductListing = $showInstallmentsInProductListing;
    }

    public function isShowInstallmentsInCartPage(): bool
    {
        return $this->showInstallmentsInCartPage;
    }

    public function setShowInstallmentsInCartPage(bool $showInstallmentsInCartPage): void
    {
        $this->showInstallmentsInCartPage = $showInstallmentsInCartPage;
    }

    public function getWidgetConfig(): ?string
    {
        return $this->widgetConfig;
    }

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

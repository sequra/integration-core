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
     * @var string
     */
    protected $miniWidgetSelector;
    /**
     * @var string
     */
    protected $widgetConfig;
    /**
     * @var WidgetLabels
     */
    protected $widgetLabels;

    /**
     * @param bool $enabled
     * @param string $assetsKey
     * @param bool $displayOnProductPage
     * @param bool $showInstallmentsInProductListing
     * @param bool $showInstallmentsInCartPage
     * @param string $miniWidgetSelector
     * @param string|null $widgetConfig
     * @param WidgetLabels|null $widgetLabels
     */
    public function __construct(
        bool $enabled,
        string $assetsKey = '',
        bool $displayOnProductPage = false,
        bool $showInstallmentsInProductListing = false,
        bool $showInstallmentsInCartPage = false,
        string $miniWidgetSelector = '',
        string $widgetConfig = null,
        WidgetLabels $widgetLabels = null
    ) {
        $this->enabled = $enabled;
        $this->assetsKey = $assetsKey;
        $this->displayOnProductPage = $displayOnProductPage;
        $this->showInstallmentsInProductListing = $showInstallmentsInProductListing;
        $this->showInstallmentsInCartPage = $showInstallmentsInCartPage;
        $this->miniWidgetSelector = $miniWidgetSelector;
        $this->widgetConfig = $widgetConfig;
        $this->widgetLabels = $widgetLabels;
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

    public function getWidgetLabels(): ?WidgetLabels
    {
        return $this->widgetLabels;
    }

    public function setWidgetLabels(?WidgetLabels $widgetLabels): void
    {
        $this->widgetLabels = $widgetLabels;
    }

    public function getMiniWidgetSelector(): string
    {
        return $this->miniWidgetSelector;
    }

    public function setMiniWidgetSelector(string $miniWidgetSelector): void
    {
        $this->miniWidgetSelector = $miniWidgetSelector;
    }
}

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
    private $enabled;
    /**
     * @var string
     */
    private $assetsKey;
    /**
     * @var bool
     */
    private $displayOnProductPage;
    /**
     * @var bool
     */
    private $showInstallmentsInProductListing;
    /**
     * @var bool
     */
    private $showInstallmentsInCartPage;
    /**
     * @var bool
     */
    private $displayMiniWidgetOnProductListingPage;
    /**
     * @var string
     */
    private $miniWidgetSelector;
    /**
     * @var WidgetConfiguration
     */
    private $widgetConfig;
    /**
     * @var WidgetLabels
     */
    private $widgetLabels;

    /**
     * @param bool $enabled
     * @param string $assetsKey
     * @param bool $displayOnProductPage
     * @param bool $showInstallmentsInProductListing
     * @param bool $showInstallmentsInCartPage
     * @param bool $displayMiniWidgetOnProductListingPage
     * @param string $miniWidgetSelector
     * @param WidgetConfiguration|null $widgetConfig
     * @param WidgetLabels|null $widgetLabels
     */
    public function __construct(
        bool                $enabled,
        string              $assetsKey = '',
        bool                $displayOnProductPage = false,
        bool                $showInstallmentsInProductListing = false,
        bool                $showInstallmentsInCartPage = false,
        bool                $displayMiniWidgetOnProductListingPage = false,
        string              $miniWidgetSelector = '',
        WidgetConfiguration $widgetConfig = null,
        WidgetLabels        $widgetLabels = null
    )
    {
        $this->enabled = $enabled;
        $this->assetsKey = $assetsKey;
        $this->displayOnProductPage = $displayOnProductPage;
        $this->showInstallmentsInProductListing = $showInstallmentsInProductListing;
        $this->showInstallmentsInCartPage = $showInstallmentsInCartPage;
        $this->displayMiniWidgetOnProductListingPage =$displayMiniWidgetOnProductListingPage;
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

    public function getWidgetConfig(): ?WidgetConfiguration
    {
        return $this->widgetConfig;
    }

    public function setWidgetConfig(?WidgetConfiguration $widgetConfig): void
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

    public function isDisplayMiniWidgetOnProductListingPage(): bool
    {
        return $this->displayMiniWidgetOnProductListingPage;
    }

    public function setDisplayMiniWidgetOnProductListingPage(bool $displayMiniWidgetOnProductListingPage): void
    {
        $this->displayMiniWidgetOnProductListingPage = $displayMiniWidgetOnProductListingPage;
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

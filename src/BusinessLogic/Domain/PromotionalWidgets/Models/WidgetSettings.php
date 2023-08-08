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
    private $showInProductListing;
    /**
     * @var bool
     */
    private $showInCartPage;
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
     * @param bool $showInProductListing
     * @param bool $showInCartPage
     * @param WidgetConfiguration|null $widgetConfig
     * @param WidgetLabels|null $widgetLabels
     */
    public function __construct(
        bool $enabled,
        string $assetsKey = '',
        bool $displayOnProductPage = false,
        bool $showInProductListing = false,
        bool $showInCartPage = false,
        WidgetConfiguration $widgetConfig = null,
        WidgetLabels $widgetLabels = null
    )
    {
        $this->enabled = $enabled;
        $this->assetsKey = $assetsKey;
        $this->displayOnProductPage = $displayOnProductPage;
        $this->showInProductListing = $showInProductListing;
        $this->showInCartPage = $showInCartPage;
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

    public function isShowInProductListing(): bool
    {
        return $this->showInProductListing;
    }

    public function setShowInProductListing(bool $showInProductListing): void
    {
        $this->showInProductListing = $showInProductListing;
    }

    public function isShowInCartPage(): bool
    {
        return $this->showInCartPage;
    }

    public function setShowInCartPage(bool $showInCartPage): void
    {
        $this->showInCartPage = $showInCartPage;
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
}
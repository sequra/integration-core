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
     * @param bool $displayOnProductPage
     * @param bool $showInstallmentsInProductListing
     * @param bool $showInstallmentsInCartPage
     * @param string $miniWidgetSelector
     * @param string|null $widgetConfig
     * @param WidgetLabels|null $widgetLabels
     */
    public function __construct(
        bool $enabled,
        bool $displayOnProductPage = false,
        bool $showInstallmentsInProductListing = false,
        bool $showInstallmentsInCartPage = false,
        string $miniWidgetSelector = '',
        string $widgetConfig = null,
        WidgetLabels $widgetLabels = null
    ) {
        $this->enabled = $enabled;
        $this->displayOnProductPage = $displayOnProductPage;
        $this->showInstallmentsInProductListing = $showInstallmentsInProductListing;
        $this->showInstallmentsInCartPage = $showInstallmentsInCartPage;
        $this->miniWidgetSelector = $miniWidgetSelector;
        $this->widgetConfig = $widgetConfig;
        $this->widgetLabels = $widgetLabels;
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
     * @return ?WidgetLabels
     */
    public function getWidgetLabels(): ?WidgetLabels
    {
        return $this->widgetLabels;
    }

    /**
     * @return string
     */
    public function getMiniWidgetSelector(): string
    {
        return $this->miniWidgetSelector;
    }
}

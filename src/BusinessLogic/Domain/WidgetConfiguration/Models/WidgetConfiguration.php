<?php

namespace SeQura\Core\BusinessLogic\Domain\WidgetConfiguration\Models;

/**
 * Class WidgetConfiguration
 *
 * @package SeQura\Core\BusinessLogic\Domain\WidgetConfiguration\Models
 */
class WidgetConfiguration
{
    /**
     * @var bool
     */
    private $useWidgets;

    /**
     * @var string|null
     */
    private $assetsKey;

    /**
     * @var bool
     */
    private $displayWidgetOnProductPage;

    /**
     * @var bool
     */
    private $showInstallmentAmountInProductListing;

    /**
     * @var bool
     */
    private $showInstallmentAmountInCartPage;

    /**
     * @var string[]|null
     */
    private $widgetStyles;

    /**
     * @var string[]|null
     */
    private $widgetLabels;

    /**
     * @param bool $useWidgets
     * @param bool $displayWidgetOnProductPage
     * @param bool $showInstallmentAmountInProductListing
     * @param bool $showInstallmentAmountInCartPage
     * @param string|null $assetsKey
     * @param string[]|null $widgetStyles
     * @param string[]|null $widgetLabels
     */
    public function __construct(
        bool $useWidgets,
        bool $displayWidgetOnProductPage,
        bool $showInstallmentAmountInProductListing,
        bool $showInstallmentAmountInCartPage,
        ?string $assetsKey,
        ?array $widgetStyles,
        ?array $widgetLabels
    )
    {
        $this->useWidgets = $useWidgets;
        $this->assetsKey = $assetsKey;
        $this->displayWidgetOnProductPage = $displayWidgetOnProductPage;
        $this->showInstallmentAmountInProductListing = $showInstallmentAmountInProductListing;
        $this->showInstallmentAmountInCartPage = $showInstallmentAmountInCartPage;
        $this->widgetStyles = $widgetStyles;
        $this->widgetLabels = $widgetLabels;
    }

    /**
     * @return bool
     */
    public function isUseWidgets(): bool
    {
        return $this->useWidgets;
    }

    /**
     * @param bool $useWidgets
     */
    public function setUseWidgets(bool $useWidgets): void
    {
        $this->useWidgets = $useWidgets;
    }

    /**
     * @return string|null
     */
    public function getAssetsKey(): ?string
    {
        return $this->assetsKey;
    }

    /**
     * @param string|null $assetsKey
     */
    public function setAssetsKey(?string $assetsKey): void
    {
        $this->assetsKey = $assetsKey;
    }

    /**
     * @return bool
     */
    public function isDisplayWidgetOnProductPage(): bool
    {
        return $this->displayWidgetOnProductPage;
    }

    /**
     * @param bool $displayWidgetOnProductPage
     */
    public function setDisplayWidgetOnProductPage(bool $displayWidgetOnProductPage): void
    {
        $this->displayWidgetOnProductPage = $displayWidgetOnProductPage;
    }

    /**
     * @return bool
     */
    public function isShowInstallmentAmountInProductListing(): bool
    {
        return $this->showInstallmentAmountInProductListing;
    }

    /**
     * @param bool $showInstallmentAmountInProductListing
     */
    public function setShowInstallmentAmountInProductListing(bool $showInstallmentAmountInProductListing): void
    {
        $this->showInstallmentAmountInProductListing = $showInstallmentAmountInProductListing;
    }

    /**
     * @return bool
     */
    public function isShowInstallmentAmountInCartPage(): bool
    {
        return $this->showInstallmentAmountInCartPage;
    }

    /**
     * @param bool $showInstallmentAmountInCartPage
     */
    public function setShowInstallmentAmountInCartPage(bool $showInstallmentAmountInCartPage): void
    {
        $this->showInstallmentAmountInCartPage = $showInstallmentAmountInCartPage;
    }

    /**
     * @return string[]|null
     */
    public function getWidgetStyles(): ?array
    {
        return $this->widgetStyles;
    }

    /**
     * @param string[]|null $widgetStyles
     */
    public function setWidgetStyles(?array $widgetStyles): void
    {
        $this->widgetStyles = $widgetStyles;
    }

    /**
     * @return string[]|null
     */
    public function getWidgetLabels(): ?array
    {
        return $this->widgetLabels;
    }

    /**
     * @param string[]|null $widgetLabels
     */
    public function setWidgetLabels(?array $widgetLabels): void
    {
        $this->widgetLabels = $widgetLabels;
    }
}

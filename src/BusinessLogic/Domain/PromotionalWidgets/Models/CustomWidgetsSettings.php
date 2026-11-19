<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models;

/**
 * Class CustomWidgetsSettings
 *
 * @package SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models
 */
class CustomWidgetsSettings
{
    /**
     * @var string
     */
    protected $product;
    /**
     * @var string
     */
    protected $customLocationSelector;
    /**
     * @var bool
     */
    protected $displayWidget;
    /**
     * @var string
     */
    protected $customWidgetStyle;

    /**
     * @param string $customLocationSelector
     * @param string $product
     * @param bool $displayWidget
     * @param string $customWidgetStyle
     */
    public function __construct(
        string $customLocationSelector,
        string $product,
        bool $displayWidget,
        string $customWidgetStyle
    ) {
        $this->customLocationSelector = $customLocationSelector;
        $this->product = $product;
        $this->displayWidget = $displayWidget;
        $this->customWidgetStyle = $customWidgetStyle;
    }

    /**
     * @return string
     */
    public function getCustomLocationSelector(): string
    {
        return $this->customLocationSelector;
    }

    /**
     * @param string $customLocationSelector
     */
    public function setCustomLocationSelector(string $customLocationSelector): void
    {
        $this->customLocationSelector = $customLocationSelector;
    }

    /**
     * @return string
     */
    public function getProduct(): string
    {
        return $this->product;
    }

    /**
     * @param string $product
     */
    public function setProduct(string $product): void
    {
        $this->product = $product;
    }

    /**
     * @return bool
     */
    public function isDisplayWidget(): bool
    {
        return $this->displayWidget;
    }

    /**
     * @param bool $displayWidget
     */
    public function setDisplayWidget(bool $displayWidget): void
    {
        $this->displayWidget = $displayWidget;
    }

    /**
     * @return string
     */
    public function getCustomWidgetStyle(): string
    {
        return $this->customWidgetStyle;
    }

    /**
     * @param string $customWidgetStyle
     */
    public function setCustomWidgetStyle(string $customWidgetStyle): void
    {
        $this->customWidgetStyle = $customWidgetStyle;
    }
}

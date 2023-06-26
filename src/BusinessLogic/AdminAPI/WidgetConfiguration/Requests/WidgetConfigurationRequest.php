<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\WidgetConfiguration\Requests;

use SeQura\Core\BusinessLogic\AdminAPI\Request\Request;
use SeQura\Core\BusinessLogic\Domain\WidgetConfiguration\Models\WidgetConfiguration;

/**
 * Class WidgetConfigurationRequest
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\WidgetConfiguration\Requests
 */
class WidgetConfigurationRequest extends Request
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
    private $disableWidgetOnProductPage;

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
     * @param bool $disableWidgetOnProductPage
     * @param bool $showInstallmentAmountInProductListing
     * @param bool $showInstallmentAmountInCartPage
     * @param string|null $assetsKey
     * @param string[]|null $widgetStyles
     * @param string[]|null $widgetLabels
     */
    public function __construct(
        bool $useWidgets,
        bool $disableWidgetOnProductPage,
        bool $showInstallmentAmountInProductListing,
        bool $showInstallmentAmountInCartPage,
        ?string $assetsKey,
        ?array $widgetStyles,
        ?array $widgetLabels
    )
    {
        $this->useWidgets = $useWidgets;
        $this->assetsKey = $assetsKey;
        $this->disableWidgetOnProductPage = $disableWidgetOnProductPage;
        $this->showInstallmentAmountInProductListing = $showInstallmentAmountInProductListing;
        $this->showInstallmentAmountInCartPage = $showInstallmentAmountInCartPage;
        $this->widgetStyles = $widgetStyles;
        $this->widgetLabels = $widgetLabels;
    }

    /**
     * Transforms the request to a WidgetConfiguration object.
     *
     * @return WidgetConfiguration
     */
    public function transformToDomainModel(): object
    {
        return new WidgetConfiguration(
            $this->useWidgets,
            $this->disableWidgetOnProductPage,
            $this->showInstallmentAmountInProductListing,
            $this->showInstallmentAmountInCartPage,
            $this->assetsKey,
            $this->widgetStyles,
            $this->widgetLabels
        );
    }
}

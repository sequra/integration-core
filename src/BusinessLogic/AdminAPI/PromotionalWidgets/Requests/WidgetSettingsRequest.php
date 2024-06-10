<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Requests;

use SeQura\Core\BusinessLogic\AdminAPI\Request\Request;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetLabels;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetLocationConfig;

/**
 * Class WidgetSettingsRequest
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Requests
 */
class WidgetSettingsRequest extends Request
{
    /**
     * @var bool
     */
    protected $enabled;
    /**
     * @var string|null
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
    protected $widgetConfiguration;
    /**
     * @var string[]
     */
    protected $messages;
    /**
     * @var string[]
     */
    protected $messagesBelowLimit;

    /**
     * @var string|null
     */
    protected $selForPrice;

    /**
     * @var string|null
     */
    protected $selForAltPrice;

    /**
     * @var string|null
     */
    protected $selForAltPriceTrigger;

    /**
     * @var string|null
     */
    protected $defaultLocationSel;

    /**
     * @var array
     */
    protected $locations;

    /**
     * @param bool $enabled
     * @param string|null $assetsKey
     * @param bool $displayOnProductPage
     * @param bool $showInstallmentsInProductListing
     * @param bool $showInstallmentsInCartPage
     * @param string $miniWidgetSelector
     * @param string $widgetConfiguration
     * @param array $messages
     * @param array $messagesBelowLimit
     * @param string|null $selForPrice
     * @param string|null $selForAltPrice
     * @param string|null $selForAltPriceTrigger
     * @param string|null $defaultLocationSel CSS selector for the default location.
     * @param array $locations Must be an array with the same structure defined at WidgetLocation::toArray().
     */
    public function __construct(
        bool $enabled,
        ?string $assetsKey,
        bool $displayOnProductPage,
        bool $showInstallmentsInProductListing,
        bool $showInstallmentsInCartPage,
        string $miniWidgetSelector,
        string $widgetConfiguration,
        array $messages = [],
        array $messagesBelowLimit = [],
        ?string $selForPrice = null,
        ?string $selForAltPrice = null,
        ?string $selForAltPriceTrigger = null,
        ?string $defaultLocationSel = null,
        array $locations = []
    ) {
        $this->enabled = $enabled;
        $this->assetsKey = $assetsKey;
        $this->displayOnProductPage = $displayOnProductPage;
        $this->showInstallmentsInProductListing = $showInstallmentsInProductListing;
        $this->showInstallmentsInCartPage = $showInstallmentsInCartPage;
        $this->miniWidgetSelector = $miniWidgetSelector;
        $this->widgetConfiguration = $widgetConfiguration;
        $this->messages = $messages;
        $this->messagesBelowLimit = $messagesBelowLimit;
        $this->selForPrice = $selForPrice;
        $this->selForAltPrice = $selForAltPrice;
        $this->selForAltPriceTrigger = $selForAltPriceTrigger;
        $this->defaultLocationSel = $defaultLocationSel;
        $this->locations = $locations;
    }

    /**
     * Transforms the request to a WidgetConfiguration object.
     *
     * @return WidgetSettings
     */
    public function transformToDomainModel(): object
    {

        return new WidgetSettings(
            $this->enabled,
            $this->assetsKey,
            $this->displayOnProductPage,
            $this->showInstallmentsInProductListing,
            $this->showInstallmentsInCartPage,
            $this->miniWidgetSelector,
            $this->widgetConfiguration,
            new WidgetLabels(
                $this->messages,
                $this->messagesBelowLimit
            ),
            WidgetLocationConfig::fromArray([
                'selForPrice' => $this->selForPrice,
                'selForAltPrice' => $this->selForAltPrice,
                'selForAltPriceTrigger' => $this->selForAltPriceTrigger,
                'locations' => array_merge(
                    [
                        [
                            'selForTarget' => $this->defaultLocationSel,
                            'product' => null,
                            'country' => null
                        ]
                    ],
                    $this->locations
                )
            ])
        );
    }
}

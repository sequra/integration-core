<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Requests;

use SeQura\Core\BusinessLogic\AdminAPI\Request\Request;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetLabels;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSelectorSettings;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings;

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
     * @var string
     */
    protected $productPriceSelector;
    /**
     * @var string
     */
    protected $altProductPriceSelector;
    /**
     * @var string
     */
    protected $altProductPriceTriggerSelector;
    /**
     * @var string
     */
    protected $defaultProductLocationSelector;
    /**
     * @var string
     */
    protected $cartPriceSelector;
    /**
     * @var string
     */
    protected $cartLocationSelector;
    /**
     * @var string
     */
    protected $widgetOnCartPage;
    /**
     * @var string
     */
    protected $listingPriceSelector;
    /**
     * @var string
     */
    protected $listingLocationSelector;
    /**
     * @var string
     */
    protected $widgetOnListingPage;

    /**
     * @param bool $enabled
     * @param string|null $assetsKey
     * @param bool $displayOnProductPage
     * @param bool $showInstallmentsInProductListing
     * @param bool $showInstallmentsInCartPage
     * @param string $miniWidgetSelector
     * @param string $widgetConfiguration
     * @param string $productPriceSelector
     * @param string $defaultProductLocationSelector
     * @param string $cartPriceSelector
     * @param string $cartLocationSelector
     * @param string $widgetOnCartPage
     * @param string $listingPriceSelector
     * @param string $listingLocationSelector
     * @param string $widgetOnListingPage
     * @param string $altProductPriceSelector
     * @param string $altProductPriceTriggerSelector
     * @param string[]  $messages
     * @param string[]  $messagesBelowLimit
     */
    public function __construct(
        bool $enabled,
        ?string $assetsKey,
        bool $displayOnProductPage,
        bool $showInstallmentsInProductListing,
        bool $showInstallmentsInCartPage,
        string $miniWidgetSelector,
        string $widgetConfiguration,
        string $productPriceSelector,
        string $defaultProductLocationSelector,
        string $cartPriceSelector,
        string $cartLocationSelector,
        string $widgetOnCartPage,
        string $listingPriceSelector,
        string $listingLocationSelector,
        string $widgetOnListingPage,
        string $altProductPriceSelector = '',
        string $altProductPriceTriggerSelector = '',
        array $messages = [],
        array $messagesBelowLimit = []
    ) {
        $this->enabled = $enabled;
        $this->assetsKey = $assetsKey;
        $this->displayOnProductPage = $displayOnProductPage;
        $this->showInstallmentsInProductListing = $showInstallmentsInProductListing;
        $this->showInstallmentsInCartPage = $showInstallmentsInCartPage;
        $this->miniWidgetSelector = $miniWidgetSelector;
        $this->widgetConfiguration = $widgetConfiguration;
        $this->productPriceSelector = $productPriceSelector;
        $this->defaultProductLocationSelector = $defaultProductLocationSelector;
        $this->cartPriceSelector = $cartPriceSelector;
        $this->cartLocationSelector = $cartLocationSelector;
        $this->widgetOnCartPage = $widgetOnCartPage;
        $this->listingPriceSelector = $listingPriceSelector;
        $this->listingLocationSelector = $listingLocationSelector;
        $this->widgetOnListingPage = $widgetOnListingPage;
        $this->altProductPriceSelector = $altProductPriceSelector;
        $this->altProductPriceTriggerSelector = $altProductPriceTriggerSelector;
        $this->messages = $messages;
        $this->messagesBelowLimit = $messagesBelowLimit;
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
            new WidgetSelectorSettings(
                $this->productPriceSelector,
                $this->defaultProductLocationSelector,
                '',
                $this->altProductPriceSelector,
                $this->altProductPriceTriggerSelector
            ),
            new WidgetSelectorSettings(
                $this->cartPriceSelector,
                $this->cartLocationSelector,
                $this->widgetOnCartPage
            ),
            new WidgetSelectorSettings(
                $this->listingPriceSelector,
                $this->listingLocationSelector,
                $this->widgetOnListingPage
            )
        );
    }
}

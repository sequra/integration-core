<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Requests;

use SeQura\Core\BusinessLogic\AdminAPI\Request\Request;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetLabels;
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
     * @param bool $enabled
     * @param bool $displayOnProductPage
     * @param bool $showInstallmentsInProductListing
     * @param bool $showInstallmentsInCartPage
     * @param string $miniWidgetSelector
     * @param string $widgetConfiguration
     * @param string[] $messages
     * @param string[] $messagesBelowLimit
     */
    public function __construct(
        bool $enabled,
        bool $displayOnProductPage,
        bool $showInstallmentsInProductListing,
        bool $showInstallmentsInCartPage,
        string $miniWidgetSelector,
        string $widgetConfiguration,
        array $messages = [],
        array $messagesBelowLimit = []
    ) {
        $this->enabled = $enabled;
        $this->displayOnProductPage = $displayOnProductPage;
        $this->showInstallmentsInProductListing = $showInstallmentsInProductListing;
        $this->showInstallmentsInCartPage = $showInstallmentsInCartPage;
        $this->miniWidgetSelector = $miniWidgetSelector;
        $this->widgetConfiguration = $widgetConfiguration;
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
            $this->displayOnProductPage,
            $this->showInstallmentsInProductListing,
            $this->showInstallmentsInCartPage,
            $this->miniWidgetSelector,
            $this->widgetConfiguration,
            new WidgetLabels(
                $this->messages,
                $this->messagesBelowLimit
            )
        );
    }
}

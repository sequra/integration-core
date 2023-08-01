<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Requests;

use SeQura\Core\BusinessLogic\AdminAPI\Request\Request;
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
    private $enabled;
    /**
     * @var string|null
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
     * @param bool $enabled
     * @param bool $displayOnProductPage
     * @param bool $showInProductListing
     * @param bool $showInCartPage
     * @param string|null $assetsKey
     */
    public function __construct(
        bool    $enabled,
        bool    $displayOnProductPage,
        bool    $showInProductListing,
        bool    $showInCartPage,
        ?string $assetsKey
    )
    {
        $this->enabled = $enabled;
        $this->assetsKey = $assetsKey;
        $this->displayOnProductPage = $displayOnProductPage;
        $this->showInProductListing = $showInProductListing;
        $this->showInCartPage = $showInCartPage;
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
            $this->showInProductListing,
            $this->showInCartPage,
            $this->assetsKey
        );
    }
}

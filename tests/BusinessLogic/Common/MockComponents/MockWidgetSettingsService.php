<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetInitializer;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetSettingsService;

/**
 * Class MockWidgetSettingsService.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockWidgetSettingsService extends WidgetSettingsService
{
    /**
     * @var WidgetInitializer|null
     */
    private $widgetInitializeData = null;

    /**
     * @param string $shippingCountry
     * @param string $currentCountry
     *
     * @return WidgetInitializer
     */
    public function getWidgetInitializeData(string $shippingCountry, string $currentCountry): WidgetInitializer
    {
        return $this->widgetInitializeData;
    }

    /**
     * @param WidgetInitializer $widgetInitializer
     *
     * @return void
     */
    public function setMockWidgetInitializeData(WidgetInitializer $widgetInitializer): void
    {
        $this->widgetInitializeData = $widgetInitializer;
    }
}

<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use Exception;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\Widget;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetInitializer;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings;
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
     * @var Widget|null
     */
    private $widget = null;

    /**
     * @var Widget[]
     */
    private $widgets = [];

    /**
     * @var ?WidgetSettings
     */
    private $widgetSettings;

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
     * @param string $shippingCountry
     * @param string $currentCountry
     *
     * @return ?Widget
     */
    public function getAvailableWidgetForCartPage(string $shippingCountry, string $currentCountry): ?Widget
    {
        return $this->widget;
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

    /**
     * @param Widget $widget
     *
     * @return void
     */
    public function setMockWidget(Widget $widget): void
    {
        $this->widget = $widget;
    }

    /**
     * @param string $shippingCountry
     * @param string $currentCountry
     *
     * @return ?Widget
     */
    public function getAvailableMiniWidget(string $shippingCountry, string $currentCountry): ?Widget
    {
        return $this->widget;
    }

    /**
     * @param string $shippingCountry
     * @param string $currentCountry
     *
     * @return Widget[]
     */
    public function getAvailableWidgetsForProductPage(string $shippingCountry, string $currentCountry): array
    {
        return $this->widgets;
    }

    /**
     * @param Widget[] $widgets
     *
     * @return void
     */
    public function setMockWidgets(array $widgets): void
    {
        $this->widgets = $widgets;
    }

    /**
     * @return WidgetSettings
     *
     * @throws Exception
     */
    public function getWidgetSettings(): WidgetSettings
    {
        if (!$this->widgetSettings) {
            return WidgetSettings::createDefault(
                '.product.price',
                '',
                '.cart.price',
                '',
                '',
                '.listing.selector'
            );
        }

        return $this->widgetSettings;
    }

    /**
     * @param WidgetSettings $settings
     *
     * @return void
     */
    public function setWidgetSettings(WidgetSettings $settings): void
    {
        $this->widgetSettings = $settings;
    }
}

<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets;

use Exception;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Requests\WidgetSettingsRequest;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Responses\SuccessfulWidgetResponse;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Responses\WidgetSettingsResponse;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Responses\WidgetConfiguratorResponse;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetSettingsService;

/**
 * Class PromotionalWidgetsController
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets
 */
class PromotionalWidgetsController
{
    /**
     * @var WidgetSettingsService
     */
    protected $widgetSettingsService;

    /**
     * @param WidgetSettingsService $widgetSettingsService
     */
    public function __construct(
        WidgetSettingsService $widgetSettingsService
    ) {
        $this->widgetSettingsService = $widgetSettingsService;
    }

    /**
     * Gets the widget configurator.
     *
     * @return WidgetConfiguratorResponse
     */
    public function getWidgetConfigurator(): WidgetConfiguratorResponse
    {
        return new WidgetConfiguratorResponse();
    }

    /**
     * Gets active widget settings.
     *
     * @return WidgetSettingsResponse
     *
     * @throws Exception
     */
    public function getWidgetSettings(): WidgetSettingsResponse
    {
        return new WidgetSettingsResponse($this->widgetSettingsService->getWidgetSettings());
    }

    /**
     * Sets widget settings.
     *
     * @param WidgetSettingsRequest $settingsRequest
     *
     * @return SuccessfulWidgetResponse
     *
     * @throws Exception
     */
    public function setWidgetSettings(WidgetSettingsRequest $settingsRequest): SuccessfulWidgetResponse
    {
        $this->widgetSettingsService->setWidgetSettings($settingsRequest->transformToDomainModel());

        return new SuccessfulWidgetResponse();
    }
}

<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets;

use Exception;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Requests\WidgetConfigRequest;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Requests\WidgetLabelsRequest;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Requests\WidgetSettingsRequest;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Responses\SuccessfulWidgetResponse;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Responses\WidgetConfigResponse;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Responses\WidgetLabelsResponse;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Responses\WidgetSettingsResponse;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Responses\WidgetConfiguratorResponse;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetConfigService;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetLabelsService;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetSettingsService;

/**
 * Class PromotionalWidgetsController
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets
 */
class PromotionalWidgetsController
{
    /**
     * @var WidgetConfigService
     */
    private $widgetConfigService;
    /**
     * @var WidgetSettingsService
     */
    private $widgetSettingsService;
    /**
     * @var WidgetLabelsService
     */
    private $widgetLabelsService;

    /**
     * @param WidgetConfigService $widgetConfigService
     * @param WidgetSettingsService $widgetSettingsService
     * @param WidgetLabelsService $widgetLabelsService
     */
    public function __construct(
        WidgetConfigService   $widgetConfigService,
        WidgetSettingsService $widgetSettingsService,
        WidgetLabelsService   $widgetLabelsService
    )
    {
        $this->widgetConfigService = $widgetConfigService;
        $this->widgetSettingsService = $widgetSettingsService;
        $this->widgetLabelsService = $widgetLabelsService;
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
     * Gets widget config.
     *
     * @return WidgetConfigResponse
     *
     * @throws Exception
     */
    public function getWidgetConfig(): WidgetConfigResponse
    {
        return new WidgetConfigResponse($this->widgetConfigService->getWidgetConfig());
    }

    /**
     * Gets widget labels.
     *
     * @return WidgetLabelsResponse
     *
     * @throws Exception
     */
    public function getWidgetLabels(): WidgetLabelsResponse
    {
        return new WidgetLabelsResponse($this->widgetLabelsService->getWidgetLabels());
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

    /**
     * Sets widget config.
     *
     * @param WidgetConfigRequest $configRequest
     *
     * @return SuccessfulWidgetResponse
     *
     * @throws Exception
     */
    public function setWidgetConfig(WidgetConfigRequest $configRequest): SuccessfulWidgetResponse
    {
        $this->widgetConfigService->setWidgetConfig($configRequest->transformToDomainModel());

        return new SuccessfulWidgetResponse();
    }

    /**
     * Sets widget labels.
     *
     * @param WidgetLabelsRequest $labelsRequest
     *
     * @return SuccessfulWidgetResponse
     *
     * @throws Exception
     */
    public function setWidgetLabels(WidgetLabelsRequest $labelsRequest): SuccessfulWidgetResponse
    {
        $this->widgetLabelsService->setWidgetLabels($labelsRequest->transformToDomainModel());

        return new SuccessfulWidgetResponse();
    }
}
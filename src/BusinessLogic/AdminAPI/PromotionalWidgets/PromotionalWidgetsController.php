<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets;

use Exception;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Requests\WidgetSettingsRequest;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Responses\SuccessfulWidgetResponse;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Responses\UnsuccessfulJsonResponse;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Responses\WidgetSettingsResponse;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Responses\WidgetConfiguratorResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings;
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
     * @throws Exception
     */
    public function getWidgetSettings(?WidgetSettings $default = null): WidgetSettingsResponse
    {
        return new WidgetSettingsResponse($this->widgetSettingsService->getWidgetSettings() ?? $default);
    }

    /**
     * Sets widget settings.
     *
     * @param WidgetSettingsRequest $settingsRequest
     *
     * @return Response
     *
     * @throws Exception
     */
    public function setWidgetSettings(WidgetSettingsRequest $settingsRequest): Response
    {
        $widgetSettingsModel = $settingsRequest->transformToDomainModel();
        if (
            !$this->isValidJson($widgetSettingsModel->getWidgetConfig())
        ) {
            return new UnsuccessfulJsonResponse();
        }

        $widgetSettingsForProduct = $widgetSettingsModel->getWidgetSettingsForProduct();
        $productsCustomWidgetSettings = $widgetSettingsForProduct ?
            $widgetSettingsForProduct->getCustomWidgetsSettings() : [];
        foreach ($productsCustomWidgetSettings as $productCustomWidgetSetting) {
            if (
                !empty($productCustomWidgetSetting->getCustomWidgetStyle()) &&
                !$this->isValidJson($productCustomWidgetSetting->getCustomWidgetStyle())
            ) {
                return new UnsuccessfulJsonResponse();
            }
        }

        $this->widgetSettingsService->setWidgetSettings($widgetSettingsModel);

        return new SuccessfulWidgetResponse();
    }

    /**
     * Verifies if string is valid JSON
     *
     * @param string $json
     *
     * @return bool
     */
    private function isValidJson(string $json): bool
    {
        if (empty($json)) {
            return false;
        }

        json_decode($json, true);

        return json_last_error() === JSON_ERROR_NONE;
    }
}

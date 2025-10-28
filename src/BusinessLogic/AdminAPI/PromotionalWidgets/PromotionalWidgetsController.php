<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets;

use Exception;
use JsonException;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Requests\WidgetSettingsRequest;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Responses\SuccessfulWidgetResponse;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Responses\UnsuccessfulJsonResponse;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Responses\WidgetSettingsResponse;
use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Responses\WidgetConfiguratorResponse;
use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services\WidgetSettingsService;
use SeQura\Core\BusinessLogic\Domain\Integration\PromotionalWidgets\WidgetDefaultSettingsInterface;
use Throwable;

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
     * @var WidgetDefaultSettingsInterface
     */
    protected $widgetDefaultSettingService;

    /**
     * @param WidgetSettingsService $widgetSettingsService
     * @param WidgetDefaultSettingsInterface $widgetDefaultSettingService
     */
    public function __construct(
        WidgetSettingsService $widgetSettingsService,
        WidgetDefaultSettingsInterface $widgetDefaultSettingService
    ) {
        $this->widgetSettingsService = $widgetSettingsService;
        $this->widgetDefaultSettingService = $widgetDefaultSettingService;
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
        $widgetSettings = $this->widgetSettingsService->getWidgetSettings();

        return new WidgetSettingsResponse(
            $widgetSettings,
            !$widgetSettings ? $this->widgetDefaultSettingService->initializeDefaultWidgetSettings() : null
        );
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
            $widgetSettingsModel->getWidgetConfig() === '' ||
            !$this->isValidJson($widgetSettingsModel->getWidgetConfig())
        ) {
            return new UnsuccessfulJsonResponse();
        }

        $widgetSettingsForProduct = $widgetSettingsModel->getWidgetSettingsForProduct();
        $productsCustomWidgetSettings = $widgetSettingsForProduct ?
            $widgetSettingsForProduct->getCustomWidgetsSettings() : [];
        foreach ($productsCustomWidgetSettings as $productCustomWidgetSetting) {
            if (
                $productCustomWidgetSetting->getCustomWidgetStyle() !== '' &&
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
        json_decode($json, true);

        return json_last_error() === JSON_ERROR_NONE;
    }
}

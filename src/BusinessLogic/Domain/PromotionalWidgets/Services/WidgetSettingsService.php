<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services;

use Exception;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetSettingsRepositoryInterface;

/**
 * Class WidgetSettingsService
 *
 * @package SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services
 */
class WidgetSettingsService
{
    /**
     * @var WidgetSettingsRepositoryInterface
     */
    private $widgetSettingsRepository;

    /**
     * @param WidgetSettingsRepositoryInterface $widgetSettingsRepository
     */
    public function __construct(WidgetSettingsRepositoryInterface $widgetSettingsRepository)
    {
        $this->widgetSettingsRepository = $widgetSettingsRepository;
    }

    /**
     * Retrieves widget settings.
     *
     * @return WidgetSettings|null
     *
     * @throws Exception
     */
    public function getWidgetSettings(): ?WidgetSettings
    {
        return $this->widgetSettingsRepository->getWidgetSettings();
    }

    /**
     * Sets widget settings.
     *
     * @param WidgetSettings $settings
     *
     * @return void
     *
     * @throws Exception
     */
    public function setWidgetSettings(WidgetSettings $settings): void
    {
        $this->widgetSettingsRepository->setWidgetSettings($settings);
    }
}
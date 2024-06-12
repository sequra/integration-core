<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts;

use Exception;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings;

/**
 * Interface WidgetSettingsRepositoryInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts
 */
interface WidgetSettingsRepositoryInterface
{
    /**
     * Sets widget settings.
     *
     * @param WidgetSettings $settings
     *
     * @return void
     *
     * @throws Exception
     */
    public function setWidgetSettings(WidgetSettings $settings): void;

    /**
     * Retrieves widget settings.
     *
     * @return WidgetSettings|null
     *
     * @throws Exception
     */
    public function getWidgetSettings(): ?WidgetSettings;
}

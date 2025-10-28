<?php

namespace SeQura\Core\BusinessLogic\Domain\Integration\PromotionalWidgets;

use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\DefaultWidgetSettings;

/**
 * Interface WidgetDefaultSettingsInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\Integration\PromotionalWidgets
 */
interface WidgetDefaultSettingsInterface
{
    /**
     * Returns instance of DefaultWidgetSettings
     *
     * @return DefaultWidgetSettings|null
     */
    public function initializeDefaultWidgetSettings(): ?DefaultWidgetSettings;
}

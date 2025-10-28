<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Integration\PromotionalWidgets\WidgetDefaultSettingsInterface;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\DefaultWidgetSettings;

/**
 * Class MockWidgetDefaultSettings.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockWidgetDefaultSettings implements WidgetDefaultSettingsInterface
{
    public function initializeDefaultWidgetSettings(): ?DefaultWidgetSettings
    {
        return new DefaultWidgetSettings(
            '.product.price',
            '',
            '',
            '',
            '.cart.price',
            '',
            '',
            '.listing.selector'
        );
    }
}

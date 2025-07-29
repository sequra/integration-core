<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetSettings;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetSettingsRepositoryInterface;

/**
 * Class MockWidgetSettingsRepository.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockWidgetSettingsRepository implements WidgetSettingsRepositoryInterface
{
    /**
     * @var ?WidgetSettings
     */
    private $widgetSettings = null;

    /**
     * @inheritDoc
     */
    public function setWidgetSettings(WidgetSettings $settings): void
    {
        $this->widgetSettings = $settings;
    }

    /**
     * @inheritDoc
     */
    public function getWidgetSettings(): ?WidgetSettings
    {
        return $this->widgetSettings;
    }

    /**
     * @return void
     */
    public function deleteWidgetSettings(): void
    {
        $this->widgetSettings = null;
    }
}

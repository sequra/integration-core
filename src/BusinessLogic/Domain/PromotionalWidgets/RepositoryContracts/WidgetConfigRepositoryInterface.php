<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts;

use Exception;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetConfiguration;

/**
 * Interface WidgetConfigRepositoryInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts
 */
interface WidgetConfigRepositoryInterface
{
    /**
     * Sets widget configuration.
     *
     * @param WidgetConfiguration $configuration
     *
     * @return void
     *
     * @throws Exception
     */
    public function setWidgetConfig(WidgetConfiguration $configuration): void;

    /**
     * Retrieves widget configuration.
     *
     * @return WidgetConfiguration|null
     *
     * @throws Exception
     */
    public function getWidgetConfig(): ?WidgetConfiguration;
}

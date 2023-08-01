<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts;

use Exception;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetLabels;

/**
 * Interface WidgetLabelsRepositoryInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts
 */
interface WidgetLabelsRepositoryInterface
{
    /**
     * Sets widget labels.
     *
     * @param WidgetLabels $labels
     *
     * @return void
     *
     * @throws Exception
     */
    public function setWidgetLabels(WidgetLabels $labels): void;

    /**
     * Retrieves widget labels.
     *
     * @return WidgetLabels|null
     *
     * @throws Exception
     */
    public function getWidgetLabels(): ?WidgetLabels;
}

<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services;

use Exception;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetLabels;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetLabelsRepositoryInterface;

/**
 * Class WidgetLabelsService
 *
 * @package SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services
 */
class WidgetLabelsService
{
    /**
     * @var WidgetLabelsRepositoryInterface
     */
    private $widgetLabelsRepository;

    /**
     * @param WidgetLabelsRepositoryInterface $widgetLabelsRepository
     */
    public function __construct(WidgetLabelsRepositoryInterface $widgetLabelsRepository)
    {
        $this->widgetLabelsRepository = $widgetLabelsRepository;
    }

    /**
     * Retrieves widget labels.
     *
     * @return WidgetLabels|null
     *
     * @throws Exception
     */
    public function getWidgetLabels(): ?WidgetLabels
    {
        return $this->widgetLabelsRepository->getWidgetLabels();
    }

    /**
     * Sets widget labels.
     *
     * @param WidgetLabels $labels
     *
     * @return void
     *
     * @throws Exception
     */
    public function setWidgetLabels(WidgetLabels $labels): void
    {
        $this->widgetLabelsRepository->setWidgetLabels($labels);
    }
}

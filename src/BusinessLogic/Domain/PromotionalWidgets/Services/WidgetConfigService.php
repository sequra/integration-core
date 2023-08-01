<?php

namespace SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services;

use Exception;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Models\WidgetConfiguration;
use SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\RepositoryContracts\WidgetConfigRepositoryInterface;

/**
 * Class WidgetConfigService
 *
 * @package SeQura\Core\BusinessLogic\Domain\PromotionalWidgets\Services
 */
class WidgetConfigService
{
    /**
     * @var WidgetConfigRepositoryInterface
     */
    private $widgetConfigRepository;

    /**
     * @param WidgetConfigRepositoryInterface $widgetConfigRepository
     */
    public function __construct(WidgetConfigRepositoryInterface $widgetConfigRepository)
    {
        $this->widgetConfigRepository = $widgetConfigRepository;
    }

    /**
     * Retrieves widget configuration.
     *
     * @return WidgetConfiguration|null
     *
     * @throws Exception
     */
    public function getWidgetConfig(): ?WidgetConfiguration
    {
        return $this->widgetConfigRepository->getWidgetConfig();
    }

    /**
     * Sets widget configuration.
     *
     * @param WidgetConfiguration $configuration
     *
     * @return void
     *
     * @throws Exception
     */
    public function setWidgetConfig(WidgetConfiguration $configuration): void
    {
        $this->widgetConfigRepository->setWidgetConfig($configuration);
    }
}

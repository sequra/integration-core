<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\WidgetSettings;

use SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\PromotionalWidgetsController;
use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerInterface;

/**
 * Class GetWidgetSettingsHandler
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\WidgetSettings
 */
class GetWidgetSettingsHandler implements TopicHandlerInterface
{
    /**
     * @var PromotionalWidgetsController
     */
    protected $promotionalWidgetsController;

    /**
     * @param PromotionalWidgetsController $promotionalWidgetsController
     */
    public function __construct(PromotionalWidgetsController $promotionalWidgetsController)
    {
        $this->promotionalWidgetsController = $promotionalWidgetsController;
    }

    /**
     * @inheritDoc
     */
    public function handle(array $payload): Response
    {
        return $this->promotionalWidgetsController->getWidgetSettings();
    }
}

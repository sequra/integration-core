<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\PromotionalWidgets\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class WidgetConfiguratorResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\WidgetConfiguration\Responses
 */
class WidgetConfiguratorResponse extends Response
{
    protected $widgetConfigurator;

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [];
    }
}

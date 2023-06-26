<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\WidgetConfiguration\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class SuccessfulWidgetConfigurationResponse
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\WidgetConfiguration\Responses
 */
class SuccessfulWidgetConfigurationResponse extends Response
{
    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [];
    }
}

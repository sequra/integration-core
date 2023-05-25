<?php

namespace SeQura\Core\BusinessLogic\WebhookAPI\Response;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class WebhookSuccessResponse
 *
 * @package SeQura\Core\BusinessLogic\WebhookAPI\Response
 */
class WebhookSuccessResponse extends Response
{
    /**
     * @return string[]
     */
    public function toArray(): array
    {
        return [];
    }
}

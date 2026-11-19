<?php

namespace SeQura\Core\BusinessLogic\AdminAPI\Connection\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class ReRegisterWebhookResponse.
 *
 * @package SeQura\Core\BusinessLogic\AdminAPI\Connection\Responses
 */
class ReRegisterWebhookResponse extends Response
{
    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return ['success' => true];
    }
}

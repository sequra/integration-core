<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class SuccessResponse
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses
 */
class SuccessResponse extends Response
{
    /**
     * @var bool
     */
    protected $successful = true;

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [];
    }
}

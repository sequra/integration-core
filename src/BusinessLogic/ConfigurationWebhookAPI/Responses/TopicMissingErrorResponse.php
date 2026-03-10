<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class TopicMissingErrorResponse
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses
 */
class TopicMissingErrorResponse extends Response
{
    /**
     * @var bool
     */
    protected $successful = false;

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'success' => false,
            'error' => 'Topic field is required in the webhook payload.',
            'errorCode' => 'TOPIC_MISSING'
        ];
    }
}

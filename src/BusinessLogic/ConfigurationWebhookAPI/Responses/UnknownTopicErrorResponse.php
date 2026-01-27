<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Class UnknownTopicErrorResponse
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses
 */
class UnknownTopicErrorResponse extends Response
{
    /**
     * @var bool
     */
    protected $successful = false;

    /**
     * @var string
     */
    protected $topic;

    /**
     * @param string $topic
     */
    public function __construct(string $topic)
    {
        $this->topic = $topic;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'success' => false,
            'error' => "Unknown or unsupported topic: {$this->topic}",
            'errorCode' => 'UNKNOWN_TOPIC'
        ];
    }
}

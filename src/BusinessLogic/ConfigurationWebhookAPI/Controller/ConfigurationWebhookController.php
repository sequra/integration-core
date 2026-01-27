<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Controller;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerRegistry;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\TopicMissingErrorResponse;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\UnknownTopicErrorResponse;

/**
 * Class ConfigurationWebhookController
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Controller
 */
class ConfigurationWebhookController
{
    /**
     * @var TopicHandlerRegistry
     */
    protected $topicHandlerRegistry;

    /**
     * ConfigurationWebhookController constructor.
     *
     * @param TopicHandlerRegistry $topicHandlerRegistry
     */
    public function __construct(TopicHandlerRegistry $topicHandlerRegistry)
    {
        $this->topicHandlerRegistry = $topicHandlerRegistry;
    }

    /**
     * Handles a configuration webhook request.
     *
     * @param mixed[] $payload
     *
     * @return Response
     */
    public function handleRequest(array $payload): Response
    {
        $topic = $payload['topic'] ?? '';

        if (empty($topic)) {
            return new TopicMissingErrorResponse();
        }

        $handler = $this->topicHandlerRegistry->getHandler($topic);

        if ($handler === null) {
            return new UnknownTopicErrorResponse($topic);
        }

        return $handler->handle($payload);
    }
}

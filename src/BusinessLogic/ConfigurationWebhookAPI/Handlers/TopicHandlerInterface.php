<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;

/**
 * Interface TopicHandlerInterface
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers
 */
interface TopicHandlerInterface
{
    /**
     * Handles the webhook request for a specific topic.
     *
     * @param mixed[] $payload
     *
     * @return Response
     */
    public function handle(array $payload): Response;
}

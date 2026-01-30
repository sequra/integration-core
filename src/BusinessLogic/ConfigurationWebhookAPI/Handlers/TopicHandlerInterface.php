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
     * @param string $merchantId
     *
     * @return Response
     */
    public function handle(array $payload, string $merchantId): Response;
}

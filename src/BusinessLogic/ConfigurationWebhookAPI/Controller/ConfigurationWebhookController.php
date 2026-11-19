<?php

namespace SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Controller;

use SeQura\Core\BusinessLogic\AdminAPI\Response\Response;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Handlers\TopicHandlerRegistry;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\TopicMissingErrorResponse;
use SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Responses\UnknownTopicErrorResponse;
use SeQura\Core\BusinessLogic\Domain\Webhook\Exceptions\WebhookSignatureValidationFailed;
use SeQura\Core\BusinessLogic\Domain\Webhook\Models\WebhookValidationRequest;
use SeQura\Core\BusinessLogic\Domain\Webhook\Services\ConfigurationWebhookValidationService;

/**
 * Class ConfigurationWebhookController
 *
 * @package SeQura\Core\BusinessLogic\ConfigurationWebhookAPI\Controller
 */
class ConfigurationWebhookController
{
    /**
     * @var TopicHandlerRegistry $topicHandlerRegistry
     */
    protected $topicHandlerRegistry;

    /**
     * @var ConfigurationWebhookValidationService $configurationWebhookValidationService
     */
    protected $configurationWebhookValidationService;

    /**
     * ConfigurationWebhookController constructor.
     *
     * @param TopicHandlerRegistry $topicHandlerRegistry
     * @param ConfigurationWebhookValidationService $configurationWebhookValidationService
     */
    public function __construct(
        TopicHandlerRegistry $topicHandlerRegistry,
        ConfigurationWebhookValidationService $configurationWebhookValidationService
    ) {
        $this->topicHandlerRegistry = $topicHandlerRegistry;
        $this->configurationWebhookValidationService = $configurationWebhookValidationService;
    }

    /**
     * Handles a configuration webhook request.
     *
     * @param string $signature
     * @param mixed[] $payload
     *
     * @return Response
     *
     * @throws WebhookSignatureValidationFailed
     */
    public function handleRequest(string $signature, array $payload): Response
    {
        $this->configurationWebhookValidationService
            ->validateWebhookSignature(new WebhookValidationRequest($signature));

        $topic = $payload['topic'] ?? '';

        if (empty($topic)) {
            return new TopicMissingErrorResponse();
        }

        $handler = $this->topicHandlerRegistry->getHandlerForTopic($topic);

        if ($handler === null) {
            return new UnknownTopicErrorResponse($topic);
        }

        return $handler->handle($payload);
    }
}

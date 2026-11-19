<?php

namespace SeQura\Core\BusinessLogic\Domain\Webhook\Models;

/**
 * Class WebhookValidationRequest.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Webhook\Models
 */
class WebhookValidationRequest
{
    /**
     * @var string $webhookSignature
     */
    private $webhookSignature;

    /**
     * @param string $webhookSignature
     */
    public function __construct(string $webhookSignature)
    {
        $this->webhookSignature = $webhookSignature;
    }

    /**
     * @return string
     */
    public function getWebhookSignature(): string
    {
        return $this->webhookSignature;
    }
}

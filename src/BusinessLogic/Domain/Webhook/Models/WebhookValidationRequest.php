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
     * @var string $merchantId
     */
    private $merchantId;

    /**
     * @var string $webhookSignature
     */
    private $webhookSignature;

    /**
     * @param string $merchantId
     * @param string $webhookSignature
     */
    public function __construct(string $merchantId, string $webhookSignature)
    {
        $this->merchantId = $merchantId;
        $this->webhookSignature = $webhookSignature;
    }

    /**
     * @return string
     */
    public function getMerchantId(): string
    {
        return $this->merchantId;
    }

    /**
     * @return string
     */
    public function getWebhookSignature(): string
    {
        return $this->webhookSignature;
    }
}

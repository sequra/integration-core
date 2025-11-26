<?php

namespace SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models;

use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\CapabilitiesEmptyException;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\InvalidWebhookUrlException;

/**
 * Class CreateStoreIntegrationRequest.
 *
 * @package SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models
 */
class CreateStoreIntegrationRequest
{
    /**
     * @var string $merchantId
     */
    private $merchantId;

    /**
     * @var string $webhookUrl
     */
    private $webhookUrl;

    /**
     * @var Capability[] $capabilities
     */
    private $capabilities;

    /**
     * @param string $merchantId
     * @param string $webhookUrl
     * @param Capability[] $capabilities
     *
     * @throws CapabilitiesEmptyException
     * @throws InvalidWebhookUrlException
     */
    public function __construct(string $merchantId, string $webhookUrl, array $capabilities)
    {
        $this->validateCapabilities($capabilities);
        $this->validateWebhookUrl($webhookUrl);

        $this->merchantId = $merchantId;
        $this->webhookUrl = $webhookUrl;
        $this->capabilities = $capabilities;
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
    public function getWebhookUrl(): string
    {
        return $this->webhookUrl;
    }

    /**
     * @return Capability[]
     */
    public function getCapabilities(): array
    {
        return $this->capabilities;
    }

    /**
     * @return array[]
     */
    public function toArray(): array
    {
        return [
            'store_integration' => [
                'webhook_url' => $this->webhookUrl,
                'capabilities' => array_map(
                    function ($capability) {
                        return $capability->getCapability();
                    },
                    $this->capabilities
                ),
            ],
        ];
    }

    /**
     * @param Capability[] $capabilities
     *
     * @return void
     *
     * @throws CapabilitiesEmptyException
     */
    private function validateCapabilities(array $capabilities): void
    {
        if (empty($capabilities)) {
            throw new CapabilitiesEmptyException();
        }
    }

    /**
     * @param string $webhookUrl
     *
     * @return void
     *
     * @throws InvalidWebhookUrlException
     */
    private function validateWebhookUrl(string $webhookUrl): void
    {
        if (filter_var($webhookUrl, FILTER_VALIDATE_URL) === false) {
            throw new InvalidWebhookUrlException();
        }
    }
}

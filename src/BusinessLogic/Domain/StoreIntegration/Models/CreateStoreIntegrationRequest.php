<?php

namespace SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models;

use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Exceptions\CapabilitiesEmptyException;
use SeQura\Core\BusinessLogic\Domain\URL\Model\URL;

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
     * @var URL $webhookUrl
     */
    private $webhookUrl;

    /**
     * @var Capability[] $capabilities
     */
    private $capabilities;

    /**
     * @param string $merchantId
     * @param URL $webhookUrl
     * @param Capability[] $capabilities
     *
     * @throws CapabilitiesEmptyException
     */
    public function __construct(string $merchantId, URL $webhookUrl, array $capabilities)
    {
        $this->validateCapabilities($capabilities);

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
     * @return URL
     */
    public function getWebhookUrl(): URL
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
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'store_integration' => [
                'webhook_url' => $this->webhookUrl->buildUrl(),
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
}

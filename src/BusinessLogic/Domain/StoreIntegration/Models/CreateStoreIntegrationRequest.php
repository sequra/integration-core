<?php

namespace SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
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
     * @var ConnectionData $connectionData
     */
    private $connectionData;

    /**
     * @var URL $webhookUrl
     */
    private $webhookUrl;

    /**
     * @var Capability[] $capabilities
     */
    private $capabilities;

    /**
     * @param ConnectionData $connectionData
     * @param URL $webhookUrl
     * @param Capability[] $capabilities
     *
     * @throws CapabilitiesEmptyException
     */
    public function __construct(ConnectionData $connectionData, URL $webhookUrl, array $capabilities)
    {
        $this->validateCapabilities($capabilities);

        $this->connectionData = $connectionData;
        $this->webhookUrl = $webhookUrl;
        $this->capabilities = $capabilities;
    }

    /**
     * @return ConnectionData
     */
    public function getConnectionData(): ConnectionData
    {
        return $this->connectionData;
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

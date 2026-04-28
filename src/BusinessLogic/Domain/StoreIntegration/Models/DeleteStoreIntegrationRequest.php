<?php

namespace SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;

/**
 * Class DeleteStoreIntegrationRequest.
 *
 * @package SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models
 */
class DeleteStoreIntegrationRequest
{
    /**
     * @var ConnectionData
     */
    private $connectionData;

    /**
     * @var string
     */
    private $webhookUrl;

    /**
     * @param ConnectionData $connectionData
     * @param string $webhookUrl
     */
    public function __construct(ConnectionData $connectionData, string $webhookUrl)
    {
        $this->connectionData = $connectionData;
        $this->webhookUrl = $webhookUrl;
    }

    /**
     * @return ConnectionData
     */
    public function getConnectionData(): ConnectionData
    {
        return $this->connectionData;
    }

    /**
     * @return string
     */
    public function getWebhookUrl(): string
    {
        return $this->webhookUrl;
    }
}

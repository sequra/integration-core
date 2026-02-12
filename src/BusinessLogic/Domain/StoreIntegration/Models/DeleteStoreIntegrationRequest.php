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
     * @var ConnectionData $connectionData
     */
    private $connectionData;

    /**
     * @var StoreIntegration
     */
    private $storeIntegration;

    /**
     * @param ConnectionData $connectionData
     * @param StoreIntegration $storeIntegration
     */
    public function __construct(ConnectionData $connectionData, StoreIntegration $storeIntegration)
    {
        $this->connectionData = $connectionData;
        $this->storeIntegration = $storeIntegration;
    }

    /**
     * @return ConnectionData
     */
    public function getConnectionData(): ConnectionData
    {
        return $this->connectionData;
    }

    /**
     * @return StoreIntegration
     */
    public function getStoreIntegration(): StoreIntegration
    {
        return $this->storeIntegration;
    }
}

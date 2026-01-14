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
     * @param ConnectionData $connectionData
     */
    public function __construct(ConnectionData $connectionData)
    {
        $this->connectionData = $connectionData;
    }

    /**
     * @return ConnectionData
     */
    public function getConnectionData(): ConnectionData
    {
        return $this->connectionData;
    }
}

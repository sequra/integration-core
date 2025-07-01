<?php

namespace SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;

/**
 * Class ConnectionDataRepositoryInterface
 *
 * @package SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts
 */
interface ConnectionDataRepositoryInterface
{
    /**
     * Returns ConnectionData instance for current store context.
     *
     * @param string $deployment
     *
     * @return ConnectionData|null
     */
    public function getConnectionDataByDeploymentId(string $deployment): ?ConnectionData;

    /**
     * Insert/update ConnectionData for current store context.
     *
     * @param ConnectionData $connectionData
     *
     * @return void
     */
    public function setConnectionData(ConnectionData $connectionData): void;

    /**
     * Retrieves first ConnectionData storeId.
     *
     * @return string|null
     */
    public function getOldestConnectionSettingsStoreId(): ?string;

    /**
     * Retrieves connection settings store ids for all stores.
     *
     * @return string[]
     */
    public function getAllConnectionSettingsStores(): array;

    /**
     * Retrieves all connection data for current store context.
     *
     * @return ConnectionData[]
     */
    public function getAllConnectionSettings(): array;
}

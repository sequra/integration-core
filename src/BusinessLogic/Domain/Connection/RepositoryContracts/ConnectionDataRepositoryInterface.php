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
     * @return ConnectionData|null
     */
    public function getConnectionData(): ?ConnectionData;

    /**
     * Insert/update ConnectionData for current store context.
     *
     * @param ConnectionData $connectionData
     *
     * @return void
     */
    public function setConnectionData(ConnectionData $connectionData): void;
}

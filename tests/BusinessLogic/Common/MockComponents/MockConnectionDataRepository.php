<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;

/**
 * Class MockConnectionDataRepository.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockConnectionDataRepository implements ConnectionDataRepositoryInterface
{
    /** @var ?ConnectionData $connectionData */
    private $connectionData = null;

    /**
     * @inheritDoc
     */
    public function getConnectionDataByDeploymentId(string $deployment): ?ConnectionData
    {
        return $this->connectionData;
    }

    /**
     * @inheritDoc
     */
    public function setConnectionData(ConnectionData $connectionData): void
    {
        $this->connectionData = $connectionData;
    }

    /**
     * @inheritDoc
     */
    public function getOldestConnectionSettingsStoreId(): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getAllConnectionSettingsStores(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAllConnectionSettings(): array
    {
        return [];
    }
}

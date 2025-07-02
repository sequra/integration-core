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
    /** @var ConnectionData[] $connectionData */
    private $connectionData = [];

    /**
     * @inheritDoc
     */
    public function getConnectionDataByDeploymentId(string $deployment): ?ConnectionData
    {
        foreach ($this->connectionData as $connectionData) {
            if ($connectionData->getDeployment() === $deployment) {
                return $connectionData;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function setConnectionData(ConnectionData $connectionData): void
    {
        $this->connectionData[] = $connectionData;
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
        return $this->connectionData;
    }
}

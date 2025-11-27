<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Services\StoreIntegrationService;

/**
 * Class StoreIntegrationService.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockStoreIntegrationService extends StoreIntegrationService
{
    /**
     * @var string $integrationId
     */
    private $integrationId = '';

    /**
     * @var bool $deleted
     */
    private $deleted = false;

    /**
     * @param ConnectionData $connectionData
     *
     * @return string
     */
    public function createStoreIntegration(ConnectionData $connectionData): string
    {
        return $this->integrationId;
    }

    public function deleteStoreIntegration(ConnectionData $connectionData): void
    {
        $this->deleted = true;
    }

    /**
     * @param string $integrationId
     *
     * @return void
     */
    public function setMockIntegrationId(string $integrationId): void
    {
        $this->integrationId = $integrationId;
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->deleted;
    }
}

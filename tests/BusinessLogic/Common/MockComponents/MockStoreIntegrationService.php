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

    public function createStoreIntegration(ConnectionData $connectionData): string
    {
        return $this->integrationId;
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
}

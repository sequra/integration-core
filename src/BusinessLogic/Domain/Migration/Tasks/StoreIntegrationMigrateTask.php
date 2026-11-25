<?php

namespace SeQura\Core\BusinessLogic\Domain\Migration\Tasks;

use SeQura\Core\BusinessLogic\Domain\Connection\Services\ConnectionService;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Stores\Services\StoreService;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Task;
use Exception;

/**
 * Class StoreIntegrationMigrateTask.
 * Handles migration for creating store integration for all connected stores and ConnectionSettings entity.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Migration\Tasks
 */
class StoreIntegrationMigrateTask extends Task
{
    /**
     * @inheritDoc
     */
    public function __serialize(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function __unserialize(array $data): void
    {
    }

    /**
     * @throws Exception
     */
    public function execute(): void
    {
        $connectedStores = $this->getStoreService()->getConnectedStores();
        foreach ($connectedStores as $storeId) {
            StoreContext::doWithStore($storeId, function () {
                $allConnectionData = $this->getConnectionService()->getAllConnectionData();
                foreach ($allConnectionData as $connectionData) {
                    $this->getConnectionService()->reRegisterWebhooks($connectionData);
                }
            });
        }
    }

    /**
     * Returns an instance of the StoreService.
     *
     * @return StoreService
     */
    protected function getStoreService(): StoreService
    {
        return ServiceRegister::getService(StoreService::class);
    }

    /**
     * @return ConnectionService
     */
    protected function getConnectionService(): ConnectionService
    {
        return ServiceRegister::getService(ConnectionService::class);
    }
}

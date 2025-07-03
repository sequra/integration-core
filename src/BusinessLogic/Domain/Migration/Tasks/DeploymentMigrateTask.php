<?php

namespace SeQura\Core\BusinessLogic\Domain\Migration\Tasks;

use Exception;
use SeQura\Core\BusinessLogic\DataAccess\ConnectionData\Entities\ConnectionData as ConnectionDataEntity;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\CredentialsRequest;
use SeQura\Core\BusinessLogic\Domain\Connection\ProxyContracts\ConnectionProxyInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\CredentialsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Deployments\Services\DeploymentsService;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\Stores\Services\StoreService;
use SeQura\Core\Infrastructure\ORM\Exceptions\RepositoryNotRegisteredException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Core\Infrastructure\ORM\RepositoryRegistry;
use SeQura\Core\Infrastructure\ServiceRegister;
use SeQura\Core\Infrastructure\TaskExecution\Task;

/**
 * Class DeploymentMigrateTask.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Migration\Tasks
 */
class DeploymentMigrateTask extends Task
{
    /**
     * @inheritDoc
     */
    public function __serialize()
    {
    }

    /**
     * @inheritDoc
     */
    public function __unserialize(array $data): void
    {
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function execute(): void
    {
        $connectedStores = $this->getStoreService()->getConnectedStores();
        foreach ($connectedStores as $storeId) {
            StoreContext::doWithStore($storeId, function () use ($storeId) {
                $deployments = $this->getDeploymentsService()->getDeployments();

                $queryFilter = new QueryFilter();
                $queryFilter->where('storeId', Operators::EQUALS, $storeId);
                /**
                 * @var ConnectionDataEntity $connectionEntity
                 */
                $connectionEntity = $this->getConnectionRepository()->selectOne($queryFilter);

                if(!$connectionEntity) {
                    return;
                }
                $existingConnectionData = $connectionEntity->getConnectionData();

                foreach ($deployments as $deployment) {
                    $connectionData = new ConnectionData(
                        $existingConnectionData->getEnvironment(),
                        $existingConnectionData->getMerchantId(),
                        $deployment->getId(),
                        $existingConnectionData->getAuthorizationCredentials()
                    );

                    $credentials = $this->getConnectionProxy()->getCredentials(new CredentialsRequest($connectionData));

                    if(empty($credentials)) {
                        continue;
                    }

                    $this->getCredentialsRepository()->setCredentials($credentials);
                    $this->getConnectionDataRepository()->setConnectionData($connectionData);
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
     * Returns an instance of the DeploymentsService.
     *
     * @return DeploymentsService
     */
    protected function getDeploymentsService(): DeploymentsService
    {
        return ServiceRegister::getService(DeploymentsService::class);
    }

    /**
     * Get SendReport repository
     *
     * @return RepositoryInterface
     *
     * @throws RepositoryNotRegisteredException
     */
    protected function getConnectionRepository(): RepositoryInterface
    {
        return RepositoryRegistry::getRepository(ConnectionDataEntity::getClassName());
    }

    /**
     * @return ConnectionProxyInterface
     */
    protected function getConnectionProxy(): ConnectionProxyInterface
    {
        return ServiceRegister::getService(ConnectionProxyInterface::class);
    }

    /**
     * @return CredentialsRepositoryInterface
     */
    protected function getCredentialsRepository(): CredentialsRepositoryInterface
    {
        return ServiceRegister::getService(CredentialsRepositoryInterface::class);
    }

    /**
     * @return ConnectionDataRepositoryInterface
     */
    protected function getConnectionDataRepository(): ConnectionDataRepositoryInterface
    {
        return ServiceRegister::getService(ConnectionDataRepositoryInterface::class);
    }
}

<?php

namespace SeQura\Core\BusinessLogic\DataAccess\ConnectionData\Repositories;

use SeQura\Core\BusinessLogic\DataAccess\ConnectionData\Entities\ConnectionData as ConnectionDataEntity;
use SeQura\Core\BusinessLogic\Domain\Connection\Models\ConnectionData;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\ConnectionDataRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class ConnectionDataRepository
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\ConnectionData\Repositories
 */
class ConnectionDataRepository implements ConnectionDataRepositoryInterface
{
    /**
     * @var RepositoryInterface Connection data repository.
     */
    protected $repository;

    /**
     * @var StoreContext Store context needed for multistore environment.
     */
    protected $storeContext;

    /**
     * @param RepositoryInterface $repository
     * @param StoreContext $storeContext
     */
    public function __construct(RepositoryInterface $repository, StoreContext $storeContext)
    {
        $this->repository = $repository;
        $this->storeContext = $storeContext;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getConnectionData(): ?ConnectionData
    {
        $entity = $this->getConnectionDataEntity();

        return $entity ? $entity->getConnectionData() : null;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setConnectionData(ConnectionData $connectionData): void
    {
        $existingConnectionData = $this->getConnectionDataEntity();

        if ($existingConnectionData) {
            $existingConnectionData->setConnectionData($connectionData);
            $existingConnectionData->setStoreId($this->storeContext->getStoreId());
            $this->repository->update($existingConnectionData);

            return;
        }

        $entity = new ConnectionDataEntity();
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setConnectionData($connectionData);
        $this->repository->save($entity);
    }

    /**
     * @inheritDoc
     */
    public function getOldestConnectionSettingsStoreId(): ?string
    {
        /**
        * @var ConnectionDataEntity $connectionData
        */
        $connectionData = $this->repository->selectOne(new QueryFilter());

        return $connectionData ? $connectionData->getStoreId() : null;
    }

    /**
     * @inheritDoc
     */
    public function getAllConnectionSettingsStores(): array
    {
        /**
        * @var ConnectionDataEntity[] $entities
        */
        $entities = $this->repository->select();

        return $entities ? array_map(function ($entity) {
            return $entity->getStoreId();
        }, $entities) : [];
    }

    /**
     * Gets the connection data entity from the database.
     *
     * @return ConnectionDataEntity|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getConnectionDataEntity(): ?ConnectionDataEntity
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /**
         * @var ConnectionDataEntity $connectionData
         */
        $connectionData = $this->repository->selectOne($queryFilter);

        return $connectionData;
    }
}

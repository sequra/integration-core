<?php

namespace SeQura\Core\BusinessLogic\DataAccess\StatisticalData\Repositories;

use SeQura\Core\BusinessLogic\DataAccess\StatisticalData\Entities\StatisticalData as StatisticalDataEntity;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\Models\StatisticalData;
use SeQura\Core\BusinessLogic\Domain\StatisticalData\RepositoryContracts\StatisticalDataRepositoryInterface;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class StatisticalDataRepository
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\StatisticalData\Repositories
 */
class StatisticalDataRepository implements StatisticalDataRepositoryInterface
{
    /**
     * @var RepositoryInterface Statistical data repository.
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
    public function getStatisticalData(): ?StatisticalData
    {
        $entity = $this->getStatisticalDataEntity();

        return $entity ? $entity->getStatisticalData() : null;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setStatisticalData(StatisticalData $statisticalData): void
    {
        $existingStatisticalData = $this->getStatisticalDataEntity();

        if ($existingStatisticalData) {
            $existingStatisticalData->setStatisticalData($statisticalData);
            $existingStatisticalData->setStoreId($this->storeContext->getStoreId());
            $this->repository->update($existingStatisticalData);

            return;
        }

        $entity = new StatisticalDataEntity();
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setStatisticalData($statisticalData);
        $this->repository->save($entity);
    }

    /**
     * Gets the statistical data entity from the database.
     *
     * @return StatisticalDataEntity|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getStatisticalDataEntity(): ?StatisticalDataEntity
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /**
        * @var StatisticalDataEntity $statisticalData
        */
        $statisticalData = $this->repository->selectOne($queryFilter);

        return $statisticalData;
    }
}

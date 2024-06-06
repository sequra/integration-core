<?php

namespace SeQura\Core\BusinessLogic\DataAccess\OrderSettings\Repositories;

use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\OrderStatusSettings\RepositoryContracts\OrderStatusSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\DataAccess\OrderSettings\Entities\OrderStatusSettings;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class OrderStatusMappingRepository
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\OrderSettings\Repositories
 */
class OrderStatusMappingRepository implements OrderStatusSettingsRepositoryInterface
{
    /**
     * @var RepositoryInterface OrderStatusMappings repository.
     */
    protected $repository;

    /**
     * @var StoreContext Store context needed for multistore environment.
     */
    protected $storeContext;

    /**
     * OrderStatusMappingRepository constructor.
     *
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
    public function getOrderStatusMapping(): ?array
    {
        $entity = $this->getOrderStatusMappingsEntity();

        return $entity ? $entity->getOrderStatusMappings() : null;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setOrderStatusMapping(array $orderStatusMapping): void
    {
        $existingOrderStatusMapping = $this->getOrderStatusMappingsEntity();

        if ($existingOrderStatusMapping) {
            $existingOrderStatusMapping->setOrderStatusMappings($orderStatusMapping);
            $existingOrderStatusMapping->setStoreId($this->storeContext->getStoreId());
            $this->repository->update($existingOrderStatusMapping);

            return;
        }

        $entity = new OrderStatusSettings();
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setOrderStatusMappings($orderStatusMapping);
        $this->repository->save($entity);
    }

    /**
     * @return OrderStatusSettings|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getOrderStatusMappingsEntity(): ?OrderStatusSettings
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /**
        * @noinspection PhpIncompatibleReturnTypeInspection
        */
        return $this->repository->selectOne($queryFilter);
    }
}

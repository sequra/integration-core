<?php

namespace SeQura\Core\BusinessLogic\DataAccess\OrderSettings\Repositories;

use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Webhook\Repositories\OrderStatusMappingRepository as BaseRepository;
use SeQura\Core\BusinessLogic\DataAccess\OrderSettings\Entities\OrderStatusMapping;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class OrderStatusMappingRepository
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\OrderSettings\Repositories
 */
class OrderStatusMappingRepository implements BaseRepository
{
    /**
     * @var RepositoryInterface
     */
    protected $repository;

    /**
     * @var StoreContext
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
     * @throws QueryFilterInvalidParamException
     */
    public function getOrderStatusMapping(): array
    {
        $entity = $this->getOrderStatusMappingsEntity();

        return $entity ? $entity->getOrderStatusMappingSettings() : [];
    }

    /**
     * @throws QueryFilterInvalidParamException
     */
    public function setOrderStatusMapping(array $orderStatusMapping): void
    {
        $existingOrderStatusMapping = $this->getOrderStatusMappingsEntity();

        if ($existingOrderStatusMapping) {
            $existingOrderStatusMapping->setOrderStatusMappingSettings($orderStatusMapping);
            $existingOrderStatusMapping->setStoreId($this->storeContext->getStoreId());
            $this->repository->update($existingOrderStatusMapping);

            return;
        }

        $entity = new OrderStatusMapping();
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setOrderStatusMappingSettings($orderStatusMapping);
        $this->repository->save($entity);
    }

    /**
     * @return OrderStatusMapping|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getOrderStatusMappingsEntity(): ?OrderStatusMapping
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->repository->selectOne($queryFilter);
    }
}

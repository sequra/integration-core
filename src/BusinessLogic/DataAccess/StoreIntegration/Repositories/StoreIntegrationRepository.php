<?php

namespace SeQura\Core\BusinessLogic\DataAccess\StoreIntegration\Repositories;

use SeQura\Core\BusinessLogic\Domain\StoreIntegration\Models\StoreIntegration as DomainStoreIntegration;
use SeQura\Core\BusinessLogic\DataAccess\StoreIntegration\Entities\StoreIntegration;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\Domain\StoreIntegration\RepositoryContracts\StoreIntegrationRepositoryInterface;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

class StoreIntegrationRepository implements StoreIntegrationRepositoryInterface
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
     * @param RepositoryInterface $repository
     * @param StoreContext $storeContext
     */
    public function __construct(RepositoryInterface $repository, StoreContext $storeContext)
    {
        $this->repository = $repository;
        $this->storeContext = $storeContext;
    }

    /**
     * @param DomainStoreIntegration $storeIntegration
     *
     * @return void
     */
    public function setStoreIntegration(DomainStoreIntegration $storeIntegration): void
    {
        $entity = new StoreIntegration();

        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setStoreIntegration($storeIntegration);
        $this->repository->save($entity);
    }

    /**
     * @return void
     * @throws QueryFilterInvalidParamException
     */
    public function deleteStoreIntegration(): void
    {
        $entity = $this->getStoreIntegrationEntityByStoreId();

        $entity && $this->repository->delete($entity);
    }

    /**
     * @return string
     * @throws QueryFilterInvalidParamException
     */
    public function getWebhookSignature(): string
    {
        $storeIntegration = $this->getStoreIntegrationEntityByStoreId();

        return $storeIntegration->getStoreIntegration()->getSignature();
    }

    /**
     * @return StoreIntegration
     * @throws QueryFilterInvalidParamException
     */
    private function getStoreIntegrationEntityByStoreId(): StoreIntegration
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /**
         * @var StoreIntegration $storeIntegration
         */
        $storeIntegration = $this->repository->selectOne($queryFilter);

        return $storeIntegration;
    }

    /**
     * @throws QueryFilterInvalidParamException
     */
    public function getStoreIntegration(): DomainStoreIntegration
    {
        return $this->getStoreIntegrationEntityByStoreId()->getStoreIntegration();
    }
}

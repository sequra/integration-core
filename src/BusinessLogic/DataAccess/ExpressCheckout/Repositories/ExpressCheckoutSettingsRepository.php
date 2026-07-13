<?php

namespace SeQura\Core\BusinessLogic\DataAccess\ExpressCheckout\Repositories;

use SeQura\Core\BusinessLogic\DataAccess\ExpressCheckout\Entities\ExpressCheckoutSettings as ExpressCheckoutSettingsEntity;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\Models\ExpressCheckoutSettings;
use SeQura\Core\BusinessLogic\Domain\ExpressCheckout\RepositoryContracts\ExpressCheckoutSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class ExpressCheckoutSettingsRepository
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\ExpressCheckout\Repositories
 */
class ExpressCheckoutSettingsRepository implements ExpressCheckoutSettingsRepositoryInterface
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
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getExpressCheckoutSettings(): ?ExpressCheckoutSettings
    {
        $entity = $this->getEntity();

        return $entity ? $entity->getExpressCheckoutSettings() : null;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setExpressCheckoutSettings(ExpressCheckoutSettings $settings): void
    {
        $existing = $this->getEntity();

        if ($existing) {
            $existing->setExpressCheckoutSettings($settings);
            $this->repository->update($existing);

            return;
        }

        $entity = new ExpressCheckoutSettingsEntity();
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setExpressCheckoutSettings($settings);
        $this->repository->save($entity);
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function deleteExpressCheckoutSettings(): void
    {
        $entity = $this->getEntity();

        $entity && $this->repository->delete($entity);
    }

    /**
     * @return ExpressCheckoutSettingsEntity|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getEntity(): ?ExpressCheckoutSettingsEntity
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /**
         * @var ExpressCheckoutSettingsEntity|null $entity
         */
        $entity = $this->repository->selectOne($queryFilter);

        return $entity;
    }
}

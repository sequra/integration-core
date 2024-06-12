<?php

namespace SeQura\Core\BusinessLogic\DataAccess\GeneralSettings\Repositories;

use SeQura\Core\BusinessLogic\Domain\GeneralSettings\Models\GeneralSettings;
use SeQura\Core\BusinessLogic\Domain\GeneralSettings\RepositoryContracts\GeneralSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\DataAccess\GeneralSettings\Entities\GeneralSettings as GeneralSettingsEntity;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class GeneralSettingsRepository
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\GeneralSettings\Repositories
 */
class GeneralSettingsRepository implements GeneralSettingsRepositoryInterface
{
    /**
     * @var RepositoryInterface General settings repository.
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
    public function getGeneralSettings(): ?GeneralSettings
    {
        $entity = $this->getGeneralSettingsEntity();

        return $entity ? $entity->getGeneralSettings() : null;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setGeneralSettings(GeneralSettings $generalSettings): void
    {
        $existingGeneralSettings = $this->getGeneralSettingsEntity();

        if ($existingGeneralSettings) {
            $existingGeneralSettings->setGeneralSettings($generalSettings);
            $existingGeneralSettings->setStoreId($this->storeContext->getStoreId());
            $this->repository->update($existingGeneralSettings);

            return;
        }

        $entity = new GeneralSettingsEntity();
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setGeneralSettings($generalSettings);
        $this->repository->save($entity);
    }

    /**
     * Gets the general settings entity from the database.
     *
     * @return GeneralSettingsEntity|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getGeneralSettingsEntity(): ?GeneralSettingsEntity
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /**
        * @var GeneralSettingsEntity $generalSettings
        */
        $generalSettings = $this->repository->selectOne($queryFilter);

        return $generalSettings;
    }
}

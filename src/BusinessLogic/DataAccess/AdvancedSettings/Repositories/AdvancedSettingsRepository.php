<?php

namespace SeQura\Core\BusinessLogic\DataAccess\AdvancedSettings\Repositories;

use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\Models\AdvancedSettings;
use SeQura\Core\BusinessLogic\Domain\AdvancedSettings\RepositoryContracts\AdvancedSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Core\BusinessLogic\DataAccess\AdvancedSettings\Entities\AdvancedSettings as AdvancedSettingsEntity;

/**
 * Class AdvancedSettingsRepository.
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\AdvancedSettings\Repositories
 */
class AdvancedSettingsRepository implements AdvancedSettingsRepositoryInterface
{
    /**
     * @var RepositoryInterface Advanced settings repository.
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
    public function getAdvancedSettings(): ?AdvancedSettings
    {
        $entity = $this->getAdvancedSettingsEntity();

        return $entity ? $entity->getAdvancedSettings() : null;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setAdvancedSettings(AdvancedSettings $settings): void
    {
        $existingGeneralSettings = $this->getAdvancedSettingsEntity();

        if ($existingGeneralSettings) {
            $existingGeneralSettings->setAdvancedSettings($settings);
            $existingGeneralSettings->setStoreId($this->storeContext->getStoreId());
            $this->repository->update($existingGeneralSettings);

            return;
        }

        $entity = new AdvancedSettingsEntity();
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setAdvancedSettings($settings);
        $this->repository->save($entity);
    }

    /**
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function deleteAdvancedSettings(): void
    {
        $entity = $this->getAdvancedSettingsEntity();

        $entity && $this->repository->delete($entity);
    }

    /**
     * Gets the advanced settings entity from the database.
     *
     * @return ?AdvancedSettingsEntity
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getAdvancedSettingsEntity(): ?AdvancedSettingsEntity
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /**
         * @var AdvancedSettingsEntity $advancedSettings
         */
        $advancedSettings = $this->repository->selectOne($queryFilter);

        return $advancedSettings;
    }
}

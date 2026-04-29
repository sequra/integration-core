<?php

namespace SeQura\Core\BusinessLogic\DataAccess\BannerSettings\Repositories;

use SeQura\Core\BusinessLogic\Domain\BannerSettings\Models\BannerSettings;
use SeQura\Core\BusinessLogic\Domain\BannerSettings\RepositoryContracts\BannerSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\DataAccess\BannerSettings\Entities\BannerSettings as BannerSettingsEntity;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class BannerSettingsRepository
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\BannerSettings\Repositories
 */
class BannerSettingsRepository implements BannerSettingsRepositoryInterface
{
    /**
     * @var RepositoryInterface Banner settings repository.
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
     */
    public function setBannerSettings(BannerSettings $settings): void
    {
        $bannerSettingsEntity = $this->getBannerSettingsEntity();

        if ($bannerSettingsEntity) {
            $bannerSettingsEntity->setBannerSettings($settings);
            $bannerSettingsEntity->setStoreId($this->storeContext->getStoreId());
            $this->repository->update($bannerSettingsEntity);

            return;
        }

        $entity = new BannerSettingsEntity();
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setBannerSettings($settings);
        $this->repository->save($entity);
    }

    /**
     * @inheritDoc
     */
    public function getBannerSettings(): ?BannerSettings
    {
        $entity = $this->getBannerSettingsEntity();

        return $entity ? $entity->getBannerSettings() : null;
    }

    /**
     * @inheritDoc
     */
    public function deleteBannerSettings(): void
    {
        $entity = $this->getBannerSettingsEntity();

        $entity && $this->repository->delete($entity);
    }

    /**
     * Gets the banner settings entity from the database.
     *
     * @return BannerSettingsEntity|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getBannerSettingsEntity(): ?BannerSettingsEntity
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /**
         * @var BannerSettingsEntity $bannerSettings
         */
        $bannerSettings = $this->repository->selectOne($queryFilter);

        return $bannerSettings;
    }
}

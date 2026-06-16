<?php

namespace SeQura\Core\BusinessLogic\DataAccess\Affiliate\Repositories;

use SeQura\Core\BusinessLogic\Domain\Affiliate\Models\AffiliateSettings;
use SeQura\Core\BusinessLogic\Domain\Affiliate\RepositoryContracts\AffiliateSettingsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Core\BusinessLogic\DataAccess\Affiliate\Entities\AffiliateSettings as AffiliateSettingsEntity;

/**
 * Class AffiliateSettingsRepository.
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\Affiliate\Repositories
 */
class AffiliateSettingsRepository implements AffiliateSettingsRepositoryInterface
{
    /**
     * @var RepositoryInterface Affiliate settings repository.
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
    public function getAffiliateSettings(): ?AffiliateSettings
    {
        $entity = $this->getAffiliateSettingsEntity();

        return $entity ? $entity->getAffiliateSettings() : null;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setAffiliateSettings(AffiliateSettings $settings): void
    {
        $existingAffiliateSettings = $this->getAffiliateSettingsEntity();

        if ($existingAffiliateSettings) {
            $existingAffiliateSettings->setAffiliateSettings($settings);
            $existingAffiliateSettings->setStoreId($this->storeContext->getStoreId());
            $this->repository->update($existingAffiliateSettings);

            return;
        }

        $entity = new AffiliateSettingsEntity();
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setAffiliateSettings($settings);
        $this->repository->save($entity);
    }

    /**
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function deleteAffiliateSettings(): void
    {
        $entity = $this->getAffiliateSettingsEntity();

        $entity && $this->repository->delete($entity);
    }

    /**
     * Gets the affiliate settings entity from the database.
     *
     * @return ?AffiliateSettingsEntity
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getAffiliateSettingsEntity(): ?AffiliateSettingsEntity
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /**
         * @var AffiliateSettingsEntity $affiliateSettings
         */
        $affiliateSettings = $this->repository->selectOne($queryFilter);

        return $affiliateSettings;
    }
}

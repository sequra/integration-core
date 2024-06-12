<?php

namespace SeQura\Core\BusinessLogic\DataAccess\CountryConfiguration\Repositories;

use SeQura\Core\BusinessLogic\DataAccess\CountryConfiguration\Entities\CountryConfiguration as CountryConfigurationEntity;
use SeQura\Core\BusinessLogic\Domain\CountryConfiguration\RepositoryContracts\CountryConfigurationRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class CountryConfigurationRepository
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\CountryConfiguration\Repositories
 */
class CountryConfigurationRepository implements CountryConfigurationRepositoryInterface
{
    /**
     * @var RepositoryInterface Country configuration repository.
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
    public function getCountryConfiguration(): ?array
    {
        $entity = $this->getCountryConfigurationEntity();

        return $entity ? $entity->getCountryConfiguration() : null;
    }

    /**
     * @inheritDoc
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setCountryConfiguration(array $countryConfigurations): void
    {
        $existingCountryConfiguration = $this->getCountryConfigurationEntity();

        if ($existingCountryConfiguration) {
            $existingCountryConfiguration->setCountryConfiguration($countryConfigurations);
            $existingCountryConfiguration->setStoreId($this->storeContext->getStoreId());
            $this->repository->update($existingCountryConfiguration);

            return;
        }

        $entity = new CountryConfigurationEntity();
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setCountryConfiguration($countryConfigurations);
        $this->repository->save($entity);
    }

    /**
     * Gets the connection data entity from the database.
     *
     * @return CountryConfigurationEntity|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getCountryConfigurationEntity(): ?CountryConfigurationEntity
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /**
        * @var CountryConfigurationEntity $countryConfiguration
        */
        $countryConfiguration = $this->repository->selectOne($queryFilter);

        return $countryConfiguration;
    }
}

<?php

namespace SeQura\Core\BusinessLogic\DataAccess\Credentials\Repositories;

use SeQura\Core\BusinessLogic\Domain\Connection\Models\Credentials;
use SeQura\Core\BusinessLogic\Domain\Connection\RepositoryContracts\CredentialsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\BusinessLogic\DataAccess\Credentials\Entities\Credentials as CredentialsEntity;
use SeQura\Core\Infrastructure\ORM\Entity;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;

/**
 * Class CredentialsRepository.
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\Credentials\Repositories
 */
class CredentialsRepository implements CredentialsRepositoryInterface
{
    /**
     * @var RepositoryInterface Credentials repository.
     */
    protected $repository;

    /**
     * @var StoreContext Store context needed for multistore environment.
     */
    protected $storeContext;

    /**
     * PaymentMethodRepository constructor.
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
     * @throws     QueryFilterInvalidParamException
     */
    public function setCredentials(array $credentials): void
    {
        foreach ($credentials as $credential) {
            $credentialsEntity = $this->getCredentialsEntityByCountryCode($credential->getCountry());

            if ($credentialsEntity === null) {
                $credentialsEntity = new CredentialsEntity();

                $credentialsEntity->setStoreId($this->storeContext->getStoreId());
                $credentialsEntity->setCountry($credential->getCountry());
                $credentialsEntity->setCredentials($credential);
                $this->repository->save($credentialsEntity);

                continue;
            }

            $credentialsEntity->setCredentials($credential);
            $this->repository->update($credentialsEntity);
        }
    }

    /**
     * @return Credentials[]
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getCredentials(): array
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /**
         * @var CredentialsEntity[] $credentialsEntities
         */
        $credentialsEntities = $this->repository->select($queryFilter);
        return array_map(
            function (CredentialsEntity $credentials) {
                return $credentials->getCredentials();
            },
            $credentialsEntities
        );
    }

    /**
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function deleteCredentials(): void
    {
        $credentials = $this->getCredentials();

        foreach ($credentials as $credential) {
            $this->deleteSingleCredentials($credential);
        }
    }

    /**
     * @param string $countryCode
     *
     * @return ?Credentials
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getCredentialsByCountryCode(string $countryCode): ?Credentials
    {
        $entity = $this->getCredentialsEntityByCountryCode($countryCode);

        return $entity ? $entity->getCredentials() : null;
    }

    /**
     * Returns the credentials entity.
     *
     * @param string $countryCode
     *
     * @return CredentialsEntity
     *
     * @throws QueryFilterInvalidParamException
     */
    private function getCredentialsEntityByCountryCode(string $countryCode): ?Entity
    {
        $filter = new QueryFilter();
        $filter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId())
            ->where('country', Operators::EQUALS, $countryCode);

        /**
         * @var ?CredentialsEntity $entity
         */
        $entity = $this->repository->selectOne($filter);

        return $entity;
    }

    /**
     * @param Credentials $credentials
     *
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    private function deleteSingleCredentials(Credentials $credentials): void
    {
        /**
         * @var CredentialsEntity $credentialsEntity
         */
        $credentialsEntity = $this->getCredentialsEntityByCountryCode($credentials->getCountry());
        $this->repository->delete($credentialsEntity);
    }
}

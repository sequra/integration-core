<?php

namespace SeQura\Core\BusinessLogic\DataAccess\Deployments\Repositories;

use SeQura\Core\BusinessLogic\Domain\Deployments\Models\Deployment;
use SeQura\Core\BusinessLogic\Domain\Deployments\RepositoryContracts\DeploymentsRepositoryInterface;
use SeQura\Core\BusinessLogic\Domain\Multistore\StoreContext;
use SeQura\Core\Infrastructure\ORM\Exceptions\QueryFilterInvalidParamException;
use SeQura\Core\Infrastructure\ORM\Interfaces\RepositoryInterface;
use SeQura\Core\Infrastructure\ORM\QueryFilter\Operators;
use SeQura\Core\Infrastructure\ORM\QueryFilter\QueryFilter;
use SeQura\Core\BusinessLogic\DataAccess\Deployments\Entities\Deployment as DeploymentEntity;

/**
 * Class DeploymentRepository.
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\Deployments\Repositories
 */
class DeploymentRepository implements DeploymentsRepositoryInterface
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
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getDeployments(): array
    {
        return array_map(
            function (DeploymentEntity $deployment) {
                return $deployment->getDeployment();
            },
            $this->getDeploymentsEntities()
        );
    }

    /**
     * @param string $deploymentId
     *
     * @return Deployment|null
     *
     * @throws QueryFilterInvalidParamException
     */
    public function getDeploymentById(string $deploymentId): ?Deployment
    {
        $deploymentEntity = $this->getDeploymentEntityByDeploymentId($deploymentId);

        return $deploymentEntity ? $deploymentEntity->getDeployment() : null;
    }

    /**
     * @param Deployment[] $deployments
     *
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function setDeployments(array $deployments): void
    {
        foreach ($deployments as $deployment) {
            $this->setDeployment($deployment);
        }
    }

    /**
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    public function deleteDeployments(): void
    {
        foreach ($this->getDeploymentsEntities() as $deployment) {
            $this->repository->delete($deployment);
        }
    }

    /**
     * @param Deployment $deployment
     *
     * @return void
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function setDeployment(Deployment $deployment): void
    {
        $existingDeployment = $this->getDeploymentEntityByDeploymentId($deployment->getId());

        if ($existingDeployment) {
            $existingDeployment->setDeploymentId($deployment->getId());
            $existingDeployment->setStoreId($this->storeContext->getStoreId());
            $existingDeployment->setDeployment($deployment);
            $this->repository->update($existingDeployment);

            return;
        }

        $entity = new DeploymentEntity();
        $entity->setStoreId($this->storeContext->getStoreId());
        $entity->setDeploymentId($deployment->getId());
        $entity->setDeployment($deployment);

        $this->repository->save($entity);
    }

    /**
     * Gets the deployment entity from the database.
     *
     * @param string $deploymentId
     *
     * @return DeploymentEntity|null
     *
     * @throws QueryFilterInvalidParamException
     */
    protected function getDeploymentEntityByDeploymentId(string $deploymentId): ?DeploymentEntity
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId())
            ->where('deploymentId', Operators::EQUALS, $deploymentId);

        /**
         * @var DeploymentEntity $deploymentEntity
         */
        $deploymentEntity = $this->repository->selectOne($queryFilter);

        return $deploymentEntity;
    }

    /**
     * @return DeploymentEntity[]
     *
     * @throws QueryFilterInvalidParamException
     */
    private function getDeploymentsEntities(): array
    {
        $queryFilter = new QueryFilter();
        $queryFilter->where('storeId', Operators::EQUALS, $this->storeContext->getStoreId());

        /**
         * @var DeploymentEntity[] $credentialsEntities
         */
        $credentialsEntities = $this->repository->select($queryFilter);

        return $credentialsEntities;
    }
}

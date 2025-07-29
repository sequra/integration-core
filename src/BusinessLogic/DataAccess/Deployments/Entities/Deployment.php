<?php

namespace SeQura\Core\BusinessLogic\DataAccess\Deployments\Entities;

use Exception;
use SeQura\Core\BusinessLogic\Domain\Deployments\Models\Deployment as DeploymentDomainModel;
use SeQura\Core\Infrastructure\ORM\Configuration\EntityConfiguration;
use SeQura\Core\Infrastructure\ORM\Configuration\IndexMap;
use SeQura\Core\Infrastructure\ORM\Entity;

/**
 * Class Deployment.
 *
 * @package SeQura\Core\BusinessLogic\DataAccess\Deployments\Entities
 */
class Deployment extends Entity
{
    /**
     * Fully qualified name of this class.
     */
    public const CLASS_NAME = __CLASS__;
    /**
     * @var string $storeId
     */
    protected $storeId;
    /**
     * @var string $deploymentId
     */
    protected $deploymentId;
    /**
     * @var DeploymentDomainModel $deployment
     */
    protected $deployment;

    /**
     * @inheritDoc
     */
    public function getConfig(): EntityConfiguration
    {
        $indexMap = new IndexMap();

        $indexMap->addStringIndex('storeId');
        $indexMap->addStringIndex('deploymentId');

        return new EntityConfiguration($indexMap, 'Deployment');
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function inflate(array $data): void
    {
        parent::inflate($data);

        $this->storeId = $data['storeId'];
        $this->deploymentId = $data['deploymentId'];
        $this->deployment = DeploymentDomainModel::fromArray($data['deployment']);
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        $data['storeId'] = $this->storeId;
        $data['deploymentId'] = $this->deploymentId;
        $data['deployment'] = $this->deployment->toArray();

        return $data;
    }

    /**
     * @return string
     */
    public function getStoreId(): string
    {
        return $this->storeId;
    }

    /**
     * @param string $storeId
     *
     * @return void
     */
    public function setStoreId(string $storeId): void
    {
        $this->storeId = $storeId;
    }

    /**
     * @return string
     */
    public function getDeploymentId(): string
    {
        return $this->deploymentId;
    }

    /**
     * @param string $deploymentId
     *
     * @return void
     */
    public function setDeploymentId(string $deploymentId): void
    {
        $this->deploymentId = $deploymentId;
    }

    /**
     * @return DeploymentDomainModel
     */
    public function getDeployment(): DeploymentDomainModel
    {
        return $this->deployment;
    }

    /**
     * @param DeploymentDomainModel $deployment
     *
     * @return void
     */
    public function setDeployment(DeploymentDomainModel $deployment): void
    {
        $this->deployment = $deployment;
    }
}

<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Deployments\Models\Deployment;
use SeQura\Core\BusinessLogic\Domain\Deployments\RepositoryContracts\DeploymentsRepositoryInterface;

/**
 * Class MockDeploymentsRepository.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockDeploymentsRepository implements DeploymentsRepositoryInterface
{
    /**
     * @var Deployment[]
     */
    private $deployments = [];

    /**
     * @inheritDoc
     */
    public function getDeployments(): array
    {
        return $this->deployments;
    }

    /**
     * @inheritDoc
     */
    public function getDeploymentById(string $deploymentId): ?Deployment
    {
        foreach ($this->deployments as $deployment) {
            if ($deployment->getId() === $deploymentId) {
                return $deployment;
            }
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function setDeployments(array $deployments): void
    {
        $this->deployments = $deployments;
    }

    /**
     * @return void
     */
    public function deleteDeployments(): void
    {
        $this->deployments = [];
    }
}

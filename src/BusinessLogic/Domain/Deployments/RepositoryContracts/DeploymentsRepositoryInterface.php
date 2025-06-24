<?php

namespace SeQura\Core\BusinessLogic\Domain\Deployments\RepositoryContracts;

use SeQura\Core\BusinessLogic\Domain\Deployments\Models\Deployment;

/**
 * Interface DeploymentsRepositoryInterface.
 *
 * @package SeQura\Core\BusinessLogic\Domain\Deployments\RepositoryContracts
 */
interface DeploymentsRepositoryInterface
{
    /**
     * @return Deployment[]
     */
    public function getDeployments(): array;

    /**
     * @param string $deploymentId
     *
     * @return ?Deployment
     */
    public function getDeploymentById(string $deploymentId): ?Deployment;

    /**
     * @param Deployment[] $deployments
     *
     * @return void
     */
    public function setDeployments(array $deployments): void;
}

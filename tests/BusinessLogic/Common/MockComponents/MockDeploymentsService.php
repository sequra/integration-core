<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Deployments\Models\Deployment;
use SeQura\Core\BusinessLogic\Domain\Deployments\Models\DeploymentURL;
use SeQura\Core\BusinessLogic\Domain\Deployments\Services\DeploymentsService;

/**
 * Class MockDeploymentsService.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockDeploymentsService extends DeploymentsService
{
    /** @var Deployment[] */
    private $deployments = [];

    /** @var ?Deployment */
    private $deployment = null;

    /**
     * @return Deployment[]
     */
    public function getDeployments(): array
    {
        return $this->deployments;
    }

    public function getDeploymentById(string $deploymentId): ?Deployment
    {
        if ($this->deployment) {
            return $this->deployment;
        }

        return new Deployment(
            'sequra',
            'seQura',
            new DeploymentURL('https://live.sequrapi.com/', 'https://live.sequracdn.com/assets/'),
            new DeploymentURL('https://sandbox.sequrapi.com/', 'https://sandbox.sequracdn.com/assets/')
        );
    }

    /**
     * @param Deployment[] $deployments
     */
    public function setMockDeployments(array $deployments): void
    {
        $this->deployments = $deployments;
    }
}

<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Deployments\Models\Deployment;
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

    /**
     * @return Deployment[]
     */
    public function getDeployments(): array
    {
        return $this->deployments;
    }

    /**
     * @param Deployment[] $deployments
     */
    public function setMockDeployments(array $deployments): void
    {
        $this->deployments = $deployments;
    }
}

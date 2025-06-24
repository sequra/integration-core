<?php

namespace SeQura\Core\Tests\BusinessLogic\Common\MockComponents;

use SeQura\Core\BusinessLogic\Domain\Deployments\Models\Deployment;
use SeQura\Core\BusinessLogic\Domain\Deployments\ProxyContracts\DeploymentsProxyInterface;

/**
 * Class MockDeploymentsProxy.
 *
 * @package SeQura\Core\Tests\BusinessLogic\Common\MockComponents
 */
class MockDeploymentsProxy implements DeploymentsProxyInterface
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
     * @param Deployment[] $deployments
     *
     * @return void
     */
    public function setMockDeployments(array $deployments): void
    {
        $this->deployments = $deployments;
    }
}
